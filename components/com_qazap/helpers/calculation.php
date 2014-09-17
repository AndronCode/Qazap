<?php
/**
 * calculation.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;


abstract class QZCalculation
{
	protected static $_rules = array();
	
	public static function getRules($type = 'product', $published = true, $userfilter = true, $idAsKey = true)
	{
		$hash = md5('type:' . $type . '.published:' . $published . '.userfilter:' . $userfilter);
		
		if(!isset(static::$_rules[$hash]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
							->select('id, type_of_arithmatic_operation, math_operation, value, countries, zipcodes')
							->from('#__qazap_taxes');
									
			if($type == 'product')
			{
				$query->where('type_of_arithmatic_operation IN (1,2,3)');
			}
			elseif($type == 'cart')
			{
				$query->where('type_of_arithmatic_operation IN (4,5,6)');
			}									
									
			if($published)
			{
				$query->where('state = 1');
			}
			
			$query->group('id');
			
			$db->setQuery($query);
			$results = $db->loadObjectList();
			
			if($userfilter || $idAsKey)
			{
				$return = array();
				$user = QZUser::get();
				$user_country = (int) $user->get('country', 0);
				$user_zip = $user->get('zip', '');
								
				foreach($results as $rule)
				{
					if($userfilter)
					{
						if(!empty($rule->countries) && is_string($rule->countries))
						{						
							$rule->countries = json_decode($rule->countries, true);
						}
						
						$rule->countries = (array) $rule->countries;
						
						if(is_string($rule->zipcodes) && $rule->zipcodes)
						{
							$rule->zipcodes = array_filter(array_map('trim', explode(',', $rule->zipcodes)));				
						}
						
						$rule->zipcodes = empty($rule->zipcodes) ? null : $rule->zipcodes;										
						
						if(!empty($rule->countries) && !in_array($user_country, $rule->countries) && !in_array(0, $rule->countries))
						{
							continue;
						}
						
						if(!empty($rule->zipcodes) && !in_array(trim($user_zip), $rule->zipcodes))
						{
							continue;	
						}							
					}
					
					if($idAsKey)
					{
						$return[$rule->id] = $rule;
					}					
				}
				
				$results = $return;
			}
			
			static::$_rules[$hash] = $results;			
		}
		
		return static::$_rules[$hash];
	}
	
	public static function getSalesPriceSubQuery($forMaxAndMin = true)
	{
		$config		= QZApp::getConfig();		
		$user			= JFactory::getUser();
		
		$valid = '';
		$validRules = QZCalculation::getRules();
		
		if(!empty($validRules))
		{
			$valid = ' AND %s IN (' . implode(',', array_keys($validRules)) . ')';
		}
		
		$multple_pricing = $config->get('multiple_product_pricing', 0);		
		
		if($multple_pricing == 1)
		{
      $subQuery = '(SELECT cp.product_id';
      
			$case1  = '(CASE cp.multiple_pricing ';
			$case1 .= 'WHEN 0 THEN cp.product_baseprice ';		
			$case1 .= 'WHEN 1 THEN cup.product_baseprice ';
			$case1 .= 'WHEN 2 THEN cqp.product_baseprice ';	
			$case1 .= 'END) AS product_baseprice';
			$subQuery .= ',' . $case1;
			
			$case2  = '(CASE cp.multiple_pricing ';
			$case2 .= 'WHEN 0 THEN cp.product_customprice ';		
			$case2 .= 'WHEN 1 THEN cup.product_customprice ';
			$case2 .= 'WHEN 2 THEN cqp.product_customprice ';	
			$case2 .= 'END) AS product_customprice';
			$subQuery .= ',' . $case2;
      
			$subQuery .= ' FROM #__qazap_products AS cp';			
			// Join Usergroup based pricing			
			if($user->guest)
			{				
				$subQuery .= ' LEFT JOIN #__qazap_product_user_price AS cup ON cup.product_id = cp.product_id AND cup.usergroup_id = 1';
			}
			else
			{
				$subQuery .= ' LEFT JOIN #__qazap_product_user_price AS cup ON cup.product_id = cp.product_id AND cup.usergroup_id IN ('. implode(',', $user->groups) . ')';
			}
			
			// Join quantity based pricing
			$quantity = (int) $config->get('minimum_purchase_quantity', 1);
			$subQuery .= ' LEFT JOIN #__qazap_product_quantity_price AS cqp ON cqp.product_id = cp.product_id AND cqp.max_quantity >= ' . $quantity.' AND cqp.min_quantity <= ' . $quantity;		
		}
		else 
		{
			$subQuery  = ' (SELECT cp.product_id, cp.product_baseprice AS product_baseprice, cp.product_customprice AS product_customprice';
			$subQuery .= ' FROM #__qazap_products  AS cp';
		}
		
		$subQuery .= ' GROUP BY cp.product_id)';
    
    $dbtSubQuery = '(SELECT dbtp.product_id, derived.product_customprice, '.
                   'CASE WHEN dbtp.dbt_rule_id <> 0 AND (dbt.value * 1) = dbt.value THEN '.
                   'CASE WHEN dbt.math_operation = "percent" THEN (derived.product_baseprice - ((derived.product_baseprice * dbt.value)/100)) '.
                   'ELSE (derived.product_baseprice - dbt.value) END ELSE derived.product_baseprice END AS product_baseprice '.    
                   'FROM #__qazap_products  AS dbtp LEFT JOIN #__qazap_taxes AS dbt ON dbt.id = dbtp.dbt_rule_id' . sprintf($valid, 'dbt.id') . ' ' .
                   'LEFT JOIN ' . $subQuery . ' AS derived ON derived.product_id = dbtp.product_id '.                   
                   'GROUP BY dbtp.product_id)';
                   
    $taxSubQuery = '(SELECT taxp.product_id, dbtval.product_customprice, '.
                   'CASE WHEN taxp.tax_rule_id <> 0 AND (tax.value * 1) = tax.value THEN '.
                   'CASE WHEN tax.math_operation = "percent" THEN (dbtval.product_baseprice + ((dbtval.product_baseprice * tax.value)/100)) '.
                   'ELSE (dbtval.product_baseprice + tax.value) END ELSE dbtval.product_baseprice END AS product_baseprice '.   
                   'FROM #__qazap_products  AS taxp LEFT JOIN #__qazap_taxes AS tax ON tax.id = taxp.tax_rule_id' . sprintf($valid, 'tax.id') . ' ' .
                   'LEFT JOIN ' . $dbtSubQuery . ' AS dbtval ON dbtval.product_id = taxp.product_id '.
                   'GROUP BY taxp.product_id)';
    
    $datSubQuery = '(SELECT datp.product_id, taxval.product_customprice, '.
                   'CASE WHEN datp.dat_rule_id <> 0 AND (dat.value * 1) = dat.value THEN '.
                   'CASE WHEN dat.math_operation = "percent" THEN (taxval.product_baseprice - ((taxval.product_baseprice * dat.value)/100)) '.
                   'ELSE (taxval.product_baseprice - dat.value) END ELSE taxval.product_baseprice END AS product_salesprice '.   
                   'FROM #__qazap_products  AS datp LEFT JOIN #__qazap_taxes AS dat ON dat.id = datp.dat_rule_id' . sprintf($valid, 'dat.id') . ' ' .
                   'LEFT JOIN ' . $taxSubQuery . ' AS taxval ON taxval.product_id = datp.product_id '.
                   'GROUP BY datp.product_id)';                   

    $query  = 'SELECT subp.product_id, ';
		$query .= '(CASE ';
		$query .= 'WHEN final.product_customprice IS NULL OR final.product_customprice = "" THEN final.product_salesprice ';		
		$query .= 'ELSE final.product_customprice * 1 ';
		$query .= 'END) AS product_salesprice ';
    $query .= 'FROM  #__qazap_products AS subp ';                   
    $query .= 'LEFT JOIN ' . $datSubQuery . ' AS final ON final.product_id = subp.product_id ';		
    $query .= 'GROUP BY subp.product_id';
    
    return $query;
	}	

}
