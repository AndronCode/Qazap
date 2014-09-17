<?php
/**
 * filters.php
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


class QZFilters extends QZObject
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  1.0
	 */
	public static $instances = array();
	
	protected static $_rules = array();
	
	protected $_options;
	
	protected $_config;
	
	protected $_states;
	
	protected $_brands = null;
	
	protected $_attributes = null;
	
	protected $_maxmin_salesprice = null;
	
	protected $_maxmin_baseprice = null;
	
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.0
	 */
	public function __construct($options = array())
	{
		$options['igonore_request'] = isset($options['igonore_request']) ? $options['igonore_request'] : false;
		$options['igonore_states'] = isset($options['igonore_states']) ? $options['igonore_states'] : false;
		$options['categories_as_filter'] = isset($options['categories_as_filter']) ? $options['categories_as_filter'] : true;
		
		$this->_options = $options;
		$this->_config = QZApp::getConfig();
		$this->_loadRequests();
		
		return true;
	}	
	
	protected function _loadRequests()
	{
		if(!$this->_options['igonore_request'] || !$this->_options['igonore_states'])
		{
			$app = JFactory::getApplication();
			$requests = array();

			$requests['category_id']		= $app->input->getInt('category_id', 0);
			$requests['product_id']			= $app->input->getInt('product_id', 0);
			
			if(!$this->_options['igonore_states'])
			{
				$categoryModel = QZApp::getModel('category', array(), false);
				$context = $categoryModel->get('context');				

				// Optional filter text
				$search = $app->getUserState($context . '.filter.search', null, 'string');
				$requests['filter_search'] = $app->input->getString('filter_search', $search);
				
				// Vendor Filter
				$vendors = $app->getUserState($context . '.filter.vendors', null, 'array');
				$requests['vendor_id'] = $app->input->get('vendor_id', $vendors, 'array');

				// Manufacturer Filter
				$manufacturers = $app->getUserState($context . '.filter.manufacturers', null, 'array');
				$requests['brand_id'] = $app->input->get('brand_id', $manufacturers, 'array');
				
				// Attribute Filter
				$attributes = $app->getUserState($context . '.filter.attributes', null, 'array');
				$requests['attribute_id'] = $app->input->get('attribute', $attributes, 'array');

				// Minimum Price Filter
				$min_price = $app->getUserState($context . '.filter.min_price', null, 'float');
				$requests['min_price'] = $app->input->get('min_price', $min_price, 'float');	
				
				// Maximum Price Filter
				$max_price = $app->getUserState($context . '.filter.max_price', null, 'float');
				$requests['max_price'] = $app->input->get('max_price', $max_price, 'float');
				
				// Filter Only In Stock
				$only_in_stock = $app->getUserState($context . '.filter.only_in_stock', 0, 'uint');
				$requests['only_in_stock'] = $app->input->getInt('only_in_stock', $only_in_stock);				
			}			
			else
			{
				$requests['category_id']		= $app->input->getInt('category_id', 0);
				$requests['product_id']			= $app->input->getInt('product_id', 0);
				$requests['filter_search']	= $app->input->getString('filter_search', null);		
				$requests['vendor_id']			= $app->input->get('vendor_id', array(), 'array');
				$requests['brand_id']				= $app->input->get('brand_id', array(), 'array');
				$requests['attribute_id']		= $app->input->get('attribute', array(), 'array');
				$requests['min_price']			= $app->input->get('min_price', null, 'float');	
				$requests['max_price']			= $app->input->get('max_price', null, 'float');
				$requests['only_in_stock']	= $app->input->getInt('only_in_stock', 0);				
			}

			$this->_states = new JRegistry;
			$this->_states->loadArray($requests);
		}
	}
	
	public function getState($name, $default = null)
	{
		if (!($this->_states instanceof JRegistry))
		{		
			$this->_loadRequests();	
		}
		
		return $this->_states->get($name, $default);
	}

	/**
	 * Returns a reference to a QZFilters class object
	 *
	 * @param   array   $config    An array of options
	 *
	 * @return  QZFilters         QZFilters class object
	 *
	 * @since   1.0
	 */
	public static function getInstance($config = array())
	{
		$hash = md5(serialize($config));

		if (!isset(self::$instances[$hash]))
		{
			self::$instances[$hash] = new QZFilters($config);
		}		
		
		return self::$instances[$hash];
	}	

	public function getAttributes()
	{
		$attributes = $this->_loadAttributes();
		$error = $this->getError();
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapcartattributes');
		
		if($attributes === false && !empty($error))
		{
			$this->setError($this->getError());
			return false;
		}
		
		if(!empty($attributes))
		{
			$result = array();			
			$active = (array) $this->getState('attribute_id');
						
			foreach($attributes AS $key => &$attribute)
			{
				$type_id = $attribute->type_id;
				$checked = (isset($attribute->attribute_id) && in_array($attribute->attribute_id, $active)) ? true : false;
				
				if(!empty($attribute))
				{
					if(!isset($result[$type_id]))
					{
						$result[$type_id] = new stdClass;
						$result[$type_id]->data = array();
					}		
					
					foreach($attribute as $property => $value)
					{														
						if(strpos($property, 'type_') === 0)
						{
							$property = str_replace('type_', '', $property);
							
							if(property_exists($result[$type_id], $property))
							{
								continue;
							}
							elseif($property == 'params')
							{
								$temp = new JRegistry;
								$temp->loadString($value);
								$result[$type_id]->$property = $temp;										
							}
							else
							{
								$result[$type_id]->$property = $value;
							}								
						}
						else
						{
							if(!isset($result[$type_id]->data[$attribute->attribute_id]))
							{
								$result[$type_id]->data[$attribute->attribute_id] = new stdClass;								
							}
													
							$result[$type_id]->data[$attribute->attribute_id]->$property = $value; 
						}
					}
					
					$result[$type_id]->data[$attribute->attribute_id]->display = $attribute->value; 
					$result[$type_id]->data[$attribute->attribute_id]->checked = $checked;
					$result[$type_id]->field_name = 'qzform[attribute][]';
					$result[$type_id]->field_id = 'qzform-filter-attribute-' . $type_id;
					
					// Trigger plugin event to allow processing of filter element display
					$return = $dispatcher->trigger('onDisplayFilter', array(&$result[$type_id]));
				}				
				unset($attributes[$key]);
			}
			
			return $result;
		}
		
		return $attributes;
	}
	
	
	public function getBrands()
	{
		$brands = $this->_loadBrands();
		$active = (array) $this->getState('brand_id');
		$error = $this->getError();
		
		if($brands === false && !empty($error))
		{
			$this->setError($error);
			return false;
		}
		
		if(!empty($brands))
		{
			foreach($brands as &$brand)
			{
				$brand->checked = in_array($brand->id, $active) ? true : false;
			}
			
			$tmp = $brands;
			$brands = new stdClass;
			$brands->data = $tmp;
			unset($tmp);
			$brands->field_name = 'qzform[brand_id][]';
			$brands->field_id = 'qzform-filter-brand';			
		}
		
		return $brands;
	}
	
	
	public function getPrices()
	{
		$config = QZApp::getConfig();
		
		if($config->get('price_filter_type', 'baseprice') == 'salesprice')
		{
			$prices = $this->_loadMaxMinFinalPrice();
		}		
		else
		{
			$prices = $this->_loadMaxMinBasePrice();
		}
		
		$error = $this->getError();
		
		if($prices === false && !empty($error))
		{
			$this->setError($this->getError());
			return false;
		}
		
		if($prices instanceof stdClass)
		{
			$prices->min_price = (float) $prices->min_price;
			$prices->max_price = (float) $prices->max_price;		
			$prices->active_min_price = $this->getState('min_price');
			$prices->active_max_price = $this->getState('max_price');
			$prices->field_name = array();
			$prices->field_name['min_price'] = 'qzform[min_price]';
			$prices->field_name['min_price_unfiltered'] = 'qzform[min_price_unfiltered]';
			$prices->field_name['max_price'] = 'qzform[max_price]';
			$prices->field_name['max_price_unfiltered'] = 'qzform[max_price_unfiltered]';
			$prices->field_id = array();
			$prices->field_id['min_price'] = 'qzform-filter-min-price';
			$prices->field_id['max_price'] = 'qzform-filter-max-price';			
		}
		
		return $prices;		
	}	
	
	protected function _loadBrands()
	{
		if($this->_brands === null)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('mfg.id, mfg.manufacturer_name AS brand_name')
						->from('#__qazap_manufacturers AS mfg')
						->join('INNER', '#__qazap_products AS d ON d.manufacturer_id = mfg.id');
						
			$query = $this->_getCommonQuery($query, 'd', 'brand');
			
			$query->group('mfg.id, mfg.manufacturer_name');
			$query->order('mfg.ordering ASC');
			
			try
			{
				$db->setQuery($query);
				$results = $db->loadObjectList();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}	
			
			if(!empty($results))
			{
				$this->_brands = $results;
			}
			else
			{
				$this->_brands = false;
			}
		}
		
		return $this->_brands;
	}
	
	protected function _loadAttributes()
	{
		if($this->_attributes === null)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('a.id AS attribute_id, a.typeid AS type_id, a.value');
			$query->from('#__qazap_cartattributes AS a');
			$query->select('b.ordering AS type_ordering, b.title AS type_title, b.show_title AS type_show_title, '.
										'b.description AS type_description, b.tooltip AS type_tooltip, b.hidden AS type_hidden, '.
										'b.check_stock AS type_check_stock, b.params AS type_params');		
			$query->join('INNER', '#__qazap_cartattributestype AS b ON b.id = a.typeid');
			$query->select('c.element AS type_plugin')
						->join('INNER', '#__extensions AS c ON c.extension_id = b.type');

			$query->join('INNER', '#__qazap_products AS d ON d.product_id = a.product_id');
						
			$query = $this->_getCommonQuery($query, 'd', 'attribute');
						
			$query->where('b.state = 1');
			$query->where('c.enabled = 1');		
			$query->order('b.ordering ASC, a.ordering ASC');
			$query->group('a.typeid, a.value');	

			try
			{
				$db->setQuery($query);
				$results = $db->loadObjectList();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}	
			
			if(!empty($results))
			{
				$this->_attributes = $results;
			}
			else
			{
				$this->_attributes = false;
			}
		}
		
		return $this->_attributes;
	}
		

	protected function _loadMaxMinFinalPrice()
	{	
		if($this->_maxmin_salesprice === null)
		{
			$db = JFactory::getDbo();
			$subQuery = $this->getSalesPriceSubQuery();
			
			$query = $db->getQuery(true)
							->select('(MAX(a.product_salesprice) * 1) AS max_price, (MIN(a.product_salesprice) * 1) AS min_price')
							->from('(' . $subQuery . ') AS a')
							->join('INNER', '#__qazap_products AS d ON d.product_id = a.product_id');
						
			$query = $this->_getCommonQuery($query, 'd');		

			try
			{
				$db->setQuery($query);
				$results = $db->loadObject();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(!empty($results))
			{
				$this->_maxmin_salesprice = $results;
			}
			else
			{
				$this->_maxmin_salesprice = false;
			}
		}			
		
		return $this->_maxmin_salesprice;
	}
	
	protected function _loadMaxMinBasePrice()
	{
		if($this->_maxmin_baseprice === null)
		{
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$config = QZApp::getConfig();
			$multple_pricing = $this->_config->get('multiple_product_pricing', false);
			
			if($multple_pricing)
			{			
				$case  = '(CASE bp.multiple_pricing ';
				$case .= 'WHEN 0 THEN bp.product_baseprice ';		
				$case .= 'WHEN 1 THEN bup.product_baseprice ';
				$case .= 'WHEN 2 THEN bqp.product_baseprice ';	
				$case .= 'END) AS product_baseprice';
				
				$subQuery  = '(SELECT bp.product_id, ' . $case;
				$subQuery .= ' FROM #__qazap_products AS bp ';
							
				if($user->guest)
				{	
					$subQuery .= ' LEFT JOIN #__qazap_product_user_price AS bup ON bup.product_id = bp.product_id AND bup.usergroup_id = 1';
				}
				else
				{
					$subQuery .= ' LEFT JOIN #__qazap_product_user_price AS bup ON bup.product_id = bp.product_id AND bup.usergroup_id IN ('. implode(',', $user->groups) . ')';
				}
				
				// Join quantity based pricing
				$quantity = (int) $config->get('minimum_purchase_quantity', 1);
				
				$subQuery .= ' LEFT JOIN #__qazap_product_quantity_price AS bqp ON bqp.product_id = bp.product_id AND bqp.max_quantity >= ' . $quantity . ' AND bqp.min_quantity <= ' . $quantity;	
				
				$subQuery .= ' GROUP BY bp.product_id) ';			
		
				$query = $db->getQuery(true)
								->select('(MAX(b.product_baseprice) * 1) AS max_price, (MIN(b.product_baseprice) * 1) AS min_price')
								->from('#__qazap_products AS a')
								->join('LEFT', $subQuery . 'AS b ON b.product_id = a.product_id');
							
				$query = $this->_getCommonQuery($query, 'a');			
			}
			else
			{
				$query = $db->getQuery(true)
								->select('(MAX(a.product_baseprice) * 1) AS max_price, (MIN(a.product_baseprice) * 1) AS min_price')
								->from('#__qazap_products AS a');							
							
				$query = $this->_getCommonQuery($query, 'a', 'price');			
			}		

			try
			{
				$db->setQuery($query);
				$results = $db->loadObject();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(!empty($results))
			{
				$this->_maxmin_baseprice = $results;
			}
			else
			{
				$this->_maxmin_baseprice = false;
			}
		}
			
		return $this->_maxmin_baseprice;
	}
	

	public function getRules($type = 'product', $published = true, $userfilter = true, $idAsKey = true)
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
	
	public function getSalesPriceSubQuery()
	{
		$config		= $this->_config;		
		$user			= JFactory::getUser();
		
		$valid = '';
		$validRules = $this->getRules();
		
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
		$query .= 'WHEN final.product_customprice IS NULL OR final.product_customprice = "" THEN final.product_salesprice * 1 ';		
		$query .= 'ELSE final.product_customprice * 1 ';
		$query .= 'END) AS product_salesprice ';
    $query .= 'FROM  #__qazap_products AS subp ';                   
    $query .= 'LEFT JOIN ' . $datSubQuery . ' AS final ON final.product_id = subp.product_id ';   
    $query .= 'GROUP BY subp.product_id';
    
    return $query;
	}		
	
	protected function _getCommonQuery(&$query, $product_alias = 'd', $ignore_request = null)
	{
		$user = JFactory::getUser();
		$subQuery = ' (SELECT e.product_id AS unique_product_id FROM #__qazap_products AS e';
		$subQueryWhere = array();

		$filter_attr = $this->getState('attribute_id');
		
		if(!empty($filter_attr) && ($ignore_request != 'attribute'))
		{			
			$filter_attr = array_map('intval', $filter_attr);

			if(count($filter_attr) == 1)
			{
				$ftSubQuery = ' (SELECT attr.product_id FROM #__qazap_cartattributes AS attr JOIN #__qazap_cartattributes AS fattr ' .
				'ON fattr.value = attr.value WHERE fattr.id = ' . (int) $filter_attr[0] . ' GROUP BY attr.product_id) ';
			}
			else
			{
				$ftSubQuery = ' (SELECT attr.product_id FROM #__qazap_cartattributes AS attr JOIN #__qazap_cartattributes AS fattr ' .
				'ON fattr.value = attr.value WHERE fattr.id IN (' . implode(',', $filter_attr) . ') GROUP BY attr.product_id) ';
			}
			
			$subQuery .= ' JOIN ' . $ftSubQuery . 'AS filterAttr ON filterAttr.product_id = e.product_id ';
			
			$subQueryWhere[] = 'filterAttr.product_id IS NOT null';			
		}		
		
		$vendor_id = $this->getState('vendor_id');
		
		if(!empty($vendor_id) && ($ignore_request != 'vendor'))
		{			
			if(count($vendor_id) == 1)
			{
				$subQueryWhere[] = 'e.vendor = ' . (int) $vendor_id[0];
			}
			else
			{
				$subQueryWhere[] = 'e.vendor IN (' . implode(',', $vendor_id) . ')';
			}
		}
		
		$brand_id = $this->getState('brand_id');
		
		if(!empty($brand_id) && ($ignore_request != 'brand'))
		{			
			if(count($brand_id) == 1)
			{
				$subQueryWhere[] = 'e.manufacturer_id = ' . (int) $brand_id[0];
			}
			else
			{
				$subQueryWhere[] = 'e.manufacturer_id IN (' . implode(',', $brand_id) . ')';
			}
		}
				
		// If multiple product pricing enabled
		$multple_pricing = $this->_config->get('multiple_product_pricing', false);
		$min_price = $this->getState('min_price');
		$max_price = $this->getState('max_price');
		
		if($multple_pricing && (!empty($min_price) || !empty($max_price)) && ($ignore_request != 'price'))
		{	
			// Join Usergroup based pricing			
			if($user->guest)
			{	
				$subQuery .= ' LEFT JOIN #__qazap_product_user_price AS up ON up.product_id = e.product_id AND up.usergroup_id = 1';
			}
			else
			{
				$subQuery .= ' LEFT JOIN #__qazap_product_user_price AS up ON up.product_id = e.product_id AND up.usergroup_id IN ('. implode(',', $user->groups) . ')';
			}
			
			// Join quantity based pricing
			$quantity = (int) $this->_config->get('minimum_purchase_quantity', 1);

			$subQuery .= ' LEFT JOIN #__qazap_product_quantity_price AS qp ON qp.product_id = e.product_id AND qp.max_quantity >= ' . $quantity . ' AND qp.min_quantity <= ' . $quantity;			
			
			// Filter by price
			if(!empty($min_price) && !empty($max_price))
			{
				$pricing_where  = 'CASE e.multiple_pricing ';	
				$pricing_where .= 'WHEN 0 THEN e.product_baseprice ';		
				$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
				$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
				$pricing_where .= 'END BETWEEN ' . (float) $min_price .' AND ' . (float) $max_price;
				$subQueryWhere[] = $pricing_where;
			}
			elseif(!empty($min_price))
			{
				$pricing_where  = (float) $min_price .' <= ';
				$pricing_where .= 'CASE e.multiple_pricing ';	
				$pricing_where .= 'WHEN 0 THEN e.product_baseprice ';		
				$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
				$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
				$pricing_where .= 'END ';
				$subQueryWhere[] = $pricing_where;
			}
			elseif(!empty($max_price))
			{
				$pricing_where  = (float) $max_price . ' >= ';
				$pricing_where .= 'CASE e.multiple_pricing ';	
				$pricing_where .= 'WHEN 0 THEN e.product_baseprice ';		
				$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
				$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
				$pricing_where .= 'END ';
				$subQueryWhere[] = $pricing_where;
			}			
		}
		elseif($ignore_request != 'price')
		{
			// Filter by price
			if(!empty($min_price) && !empty($max_price))
			{
				$subQueryWhere[] = 'e.product_baseprice BETWEEN ' . (float) $min_price.' AND ' . (float) $max_price;
			}
			elseif(!empty($min_price))
			{
				$subQueryWhere[] = 'e.product_baseprice >= ' . (float) $min_price;
			}
			elseif(!empty($max_price))
			{
				$subQueryWhere[] = 'e.product_baseprice <= '. (float) $max_price;
			}
		}		
		
		if(!empty($subQueryWhere))
		{
			$subQuery .= ' WHERE ' . implode(' AND ', $subQueryWhere);
		}		
		
		$subQuery .= ' GROUP BY e.product_id) '; //echo str_replace('#__', 'f8rup_', $subQuery);exit;
		
		$query->select('count(f.unique_product_id) AS product_count')
					->join('LEFT', $subQuery . 'AS f ON f.unique_product_id = ' . $product_alias . '.product_id');
		
		$category_id = $this->getState('category_id', 0);
		
	
		if($category_id > 0)
		{
			if($this->_options['categories_as_filter'])
			{
				$query->join('INNER', '#__qazap_categories AS g ON g.category_id = ' . $product_alias . '.category_id');
				$query->join('LEFT', '#__qazap_categories AS h ON (h.lft <= g.lft AND h.rgt >= g.rgt)')
							->where('h.category_id = ' . (int) $category_id);					
			}
			else
			{
				$query->where($product_alias . '.category_id = ' . (int) $category_id);	
			}	
		}
		
		if(!$this->_config->get('show_inactive_vendor_products', 0))
		{
			$query->join('INNER', '#__qazap_vendor AS vend ON vend.id = ' . $product_alias . '.vendor');	
			$query->where('vend.state = 1');
		}		
		
		$query->where($product_alias . '.state = 1')
					->where($product_alias . '.block = 0')
					->where($product_alias . '.parent_id = 0')
					->where($product_alias . '.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');			
		
		return $query;		
	}	

}

