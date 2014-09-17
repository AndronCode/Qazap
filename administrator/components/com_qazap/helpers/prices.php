<?php
/**
 * prices.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Qazap records.
 */
class QZPrices
{
	public $product_baseprice = null;
	
	public $product_basepricewithVariants = null;
	
	public $product_customprice = null;
	
	public $dbt_rule_id = null;
	
	public $dat_rule_id = null;
	
	public $tax_rule_id = null;		
	
	public $product_dbt = 0;
	
	public $dbt_name = null;
	
	public $product_basepriceBeforeTax = 0;
	
	public $product_tax = 0;
	
	public $tax_name = null;
	
	public $product_basepriceAfterTax = 0;
	
	public $product_dat = 0;
	
	public $dat_name = null;
	
	public $product_discount = 0;
	
	public $product_salespriceBeforeDiscount = null;
	
	public $product_salesprice = null;
	
	public $product_quantity_prices = null;
	
  /**
	* Selected attributes for cart and order processing
	* 
	* @var		float/null
	* @since	1.0
	*/
  public $product_attributes_price = null;
  
  /**
	* Selected membership price for cart and order processing
	* 
	* @var		float/null
	* @since	1.0
	*/	
	public $product_membership_price = null;	
  /**
	* Selected attributes for cart and order processing
	* 
	* @var		array
	* @since	1.0
	*/
  protected $product_attributes = array();
  
  /**
	* Selected membership for cart and order processing
	* 
	* @var		object
	* @since	1.0
	*/	
	protected $product_membership = null;
	
	protected $user_id = null;	
	

	protected static $_rules = array();
	protected $rules;	
	protected $user;
	
	
	public function __construct($product = null)
	{
		if($product)
		{
			$this->setProperties($product);
			return true;
		}
		
		return false;		
	}
	
	/**
	* @method Set product price properties in the parent class
	* 
	* @param	object/array	$product	QZProductNode object or any other product price details array
	* 
	* @return
	*/
	protected function setProperties($product)
	{	
		foreach($product as $k=>$v)
		{
			if(property_exists($this, $k))
			{
				$this->$k = $v; 
			}
		}
	}

	protected function setObjectProperties($object, $values = array())
	{
		if(count($values))
		{
			foreach($values as $k=>$v)
			{
				$object->$k = $v;
			}
		}
	}
	
	/**
	* Method to get the rule details of internal rules set during product
	* price caculation
	* 
	* @param	boolean	$published	Retrun only if the rule is in plublished state
	* 
	* @return array		Object list of rules where key is their respective id.
	* @since	1.0
	*/	
	protected function _getRules($published = true)
	{
		$rule_ids = array($this->dbt_rule_id, $this->dat_rule_id, $this->tax_rule_id);		
		return self::getRules($rule_ids, $published, $this->user_id);	
	}
	
	/**
	* Method to get rules by their ids
	* 
	* @param	array		$rule_ids		Array of the rule/tax ids
	* @param	boolean	$published	Retrun only if the rule is in plublished state
	* 
	* @return	array		Object list of rules where key is their respective id.
	* @since	1.0
	*/	
	public static function getRules($rule_ids = array(), $published = true, $user_id = null)
	{
		$rule_ids = array_filter(array_map('intval', $rule_ids));
		
		if(!count($rule_ids))
		{
			return false;
		}
		
		if($user_id)
		{
			$user = QZUser::get($user_id);
		}
		else
		{
			$user = QZUser::get();
		}
		
		$cachedKeys = array_keys(self::$_rules);
		$newRules = array_diff($rule_ids, $cachedKeys);

		if(count($newRules))
		{		
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
									->select('id, state, calculation_rule_name AS name, description, type_of_arithmatic_operation AS operation, math_operation AS calculation, value, countries, zipcodes')
									->from('#__qazap_taxes')
									->where('id IN ('. implode(',', $newRules) .')')
									->where('type_of_arithmatic_operation IN (1,2,3)');
									
			if($published)
			{
				$query->where('state = 1');
			}
			
			$db->setQuery($query);
			
			try 
			{
				$results = $db->loadObjectList('id');
			} 
			catch (RuntimeException $e) 
			{
				throw new RuntimeException($e->getMessage());
				return false;
			}	
			
			if(count($results))
			{
				self::$_rules = self::$_rules + $results;	
			}
		}		
		
		$matchedRules = QZHelper::sortArrayByArray(self::$_rules, $rule_ids);
		
		if(count($matchedRules))
		{
			$user_country = (int) $user->get('country', 0);
			$user_zip = $user->get('zip', '');						
			foreach($matchedRules as $key => &$rule)
			{
				if(!empty($rule->countries) && is_string($rule->countries))
				{						
					$rule->countries = json_decode($rule->countries, true);
					$rule->countries = array_map('intval', $rule->countries);
				}
				
				$rule->countries = (array) $rule->countries;
				if(count($rule->countries) && !in_array($user_country, $rule->countries) && !in_array(0, $rule->countries))
				{
					unset($matchedRules[$key]);
				}
							
				if(is_string($rule->zipcodes) && $rule->zipcodes)
				{
					$rule->zipcodes = array_filter(array_map('trim', explode(',', $rule->zipcodes)));				
				}
				
				$rule->zipcodes = empty($rule->zipcodes) ? null : $rule->zipcodes;
				
				if($rule->zipcodes && !in_array(trim($user_zip), $rule->zipcodes))
				{
					unset($matchedRules[$key]);	
				}												
			}
		}

		if(count($matchedRules))
		{			
			return $matchedRules;
		}
		
		return false;
	}
	
	
	
	public function get()
	{	
		$product_price = $this->calculate($this->product_baseprice, $this->product_customprice);
		$this->setProperties($product_price);
		
		if($this->product_quantity_prices && count($this->product_quantity_prices))
		{
			foreach($this->product_quantity_prices as $key => $quantity_price)
			{
				$prices = $this->calculate($quantity_price->product_baseprice, $quantity_price->product_customprice);
				$this->setObjectProperties($this->product_quantity_prices[$key], $prices);
			}
		}
		
		return $this;
	}
	
	
	protected function calculate($baseprice = 0, $customprice = null)
	{
		$rules = $this->_getRules();
		$this->rules = 		$rules;
		$this->user = QZUser::get();
		$dbt_rule = isset($rules[$this->dbt_rule_id]) ? $rules[$this->dbt_rule_id] : null;
		$dat_rule = isset($rules[$this->dat_rule_id]) ? $rules[$this->dat_rule_id] : null;
		$tax_rule = isset($rules[$this->tax_rule_id]) ? $rules[$this->tax_rule_id] : null;
		
		$baseprice								= (float) $baseprice;
		$basepricewithVariants		= $baseprice;
		$dbt											= 0;
		$dbt_name									= 'COM_QAZAP_DISCOUNT_BEFORE_TAX';	
		$basepriceBeforeTax				= 0;
		$tax											= 0;
		$tax_name									= 'COM_QAZAP_TAX';
		$basepriceAfterTax				= 0;
		$dat											= 0;
		$dat_name									= 'COM_QAZAP_DISCOUNT_AFTER_TAX';	
		$discount									= 0;
		$salespriceBeforeDiscount	= 0;
		$salesprice								= 0;
		
		if(count($this->product_attributes))
		{
			$this->product_attributes_price = 0;
			foreach($this->product_attributes as $attribute)
			{
				$this->product_attributes_price += (float) $attribute->price;
			}
			//unset($this->product_attributes);
		}
		
		if($this->product_membership)
		{
			$this->product_membership_price = $this->product_membership->price;
			//unset($this->product_membership);
		}
		
		$totalVarients = ((float) $this->product_attributes_price + (float) $this->product_membership_price);
		$basepricewithVariants += $totalVarients;
		
		// If no base price is available and only custom price given
		// then back calculate the base price along with discount and tax.
		if(!$basepricewithVariants && $customprice)
		{
			$customprice = (float) $customprice;
			$salesprice = $customprice;
			$basepricewithVariants = $customprice;
			
			if($dat_rule)
			{
				if($dat_rule->calculation == 'value')
				{
					$dat = $dat_rule->value;
				}
				else
				{
					$dat = ($basepricewithVariants * 100) / $dat_rule->value;
				}
				
				$dat_name = $dat_rule->name;
				$basepricewithVariants = ($basepricewithVariants + $dat);
			}
			
			$basepriceAfterTax = $basepricewithVariants;
			
			if($tax_rule)
			{
				if($tax_rule->calculation == 'value')
				{
					$tax = $tax_rule->value;
				}
				else
				{
					$tax = ($basepricewithVariants * 100) / $tax_rule->value;
				}
				
				$tax_name = $tax_rule->name;
				$basepricewithVariants = ($basepricewithVariants - $tax);				
			}
			
			$basepriceBeforeTax = $basepricewithVariants;
			
			if($dbt_rule)
			{
				if($dbt_rule->calculation == 'value')
				{
					$dbt = $dbt_rule->value;
				}
				else
				{
					$dbt = ($basepricewithVariants * 100) / $dbt_rule->value;
				}
				
				$dbt_name = $dbt_rule->name;
				$basepricewithVariants = ($basepricewithVariants + $dbt);
			}
			
			$baseprice = ($basepricewithVariants - $totalVarients);
			$discount = ($dbt +	$dat);
			$salespriceBeforeDiscount = ($basepricewithVariants + $tax);
		}
		
		// If base price is available but no custom price is given
		// then use normal forward calculation method.	
		elseif($basepricewithVariants && $customprice == null)
		{
			$salesprice = $basepricewithVariants;
			
			if($dbt_rule)
			{
				if($dbt_rule->calculation == 'value')
				{
					$dbt = $dbt_rule->value;
				}
				else
				{
					$dbt = $salesprice * ($dbt_rule->value / 100);
				}
				
				$dbt_name = $dbt_rule->name;
				$salesprice = ($salesprice - $dbt);
			}
			
			$basepriceBeforeTax = $salesprice;
			
			if($tax_rule)
			{
				if($tax_rule->calculation == 'value')
				{
					$tax = $tax_rule->value;
				}
				else
				{
					$tax = $salesprice * ($tax_rule->value / 100);
				}
				
				$tax_name = $tax_rule->name;
				$salesprice = ($salesprice + $tax);				
			}
			
			$basepriceAfterTax = $salesprice;
			
			if($dat_rule)
			{
				if($dat_rule->calculation == 'value')
				{
					$dat = $dat_rule->value;
				}
				else
				{
					$dat = $salesprice * ($dat_rule->value / 100);
				}
				
				$dat_name = $dat_rule->name;
				$salesprice = ($salesprice - $dat);
			}
			
			$discount = ($dbt +	$dat);
			$salespriceBeforeDiscount = ($basepricewithVariants + $tax);
		}
		
		// When both base price and custom price are given discard assigned rules and
		// calculate tax and discount based on the difference between base price and custom price.
		elseif($basepricewithVariants && $customprice)
		{
			$customprice = (float) $customprice + $totalVarients;
			$salesprice = $customprice;
			
			if($salesprice >= $basepricewithVariants)
			{
				$tax = ($salesprice - $basepricewithVariants);
			}	
			else
			{
				$discount = ($basepricewithVariants - $salesprice);
			}
			
			$salespriceBeforeDiscount = ($basepricewithVariants + $tax);
		}
		
		$prices = array(
						'product_baseprice' => $baseprice,
						'product_basepricewithVariants' => $basepricewithVariants, 
						'product_dbt' => $dbt,
						'dbt_name' => $dbt_name,
						'product_basepriceBeforeTax' => $basepriceBeforeTax, 
						'product_tax' => $tax, 
						'tax_name' => $tax_name, 
						'product_basepriceAfterTax' => $basepriceAfterTax, 
						'product_dat' => $dat, 
						'dat_name' => $dat_name, 
						'product_discount' => $discount,
						'product_salespriceBeforeDiscount' => $salespriceBeforeDiscount, 
						'product_salesprice' => $salesprice
						);
		return $prices;
				
	}
	
	public function cleanProperties($property)
	{
		if(property_exists('QZPrices', $property))
		{
			unset($this->$property);
		}		
	}
	
}

?>