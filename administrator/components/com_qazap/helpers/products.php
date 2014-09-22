<?php
/**
 * products.php
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
class QZProducts
{
	
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	public static $instances = array();	
	
	/**
	 * Array of product nodes
	 *
	 * @var    mixed
	 * @since  1.0.0
	 */
	protected $_nodes;
	
	/**
	 * Hash of the instance
	 *
	 * @var    string
	 * @since  1.0.0
	 */	
	protected $_hash;
	
	/**
	 * Array of checked hashes -- used to save values when _nodes are null
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_checked = array();
	
	/**
	* Array of product ids
	* 
	* @var		array
	* @since	1.0.0
	*/	
	protected $_product_ids = NULL;
	
	/**
	* Count of total results with hash as key
	* 
	* @var		array
	* @since	1.0.0
	*/
	protected $_count;
	
	/**
	 * Database Connector
	 *
	 * @var    object
	 * @since  1.0.0
	 */	
	protected $_db;
	
	/**
	 * Array of options
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $_options = null;
	
	/**
	 * Array of filters
	 *
	 * @var    array
	 * @since  1.0.0
	 */	
	protected $_filters = null;
	
	/**
	 * Multiple languages. true or false
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $_multiple_language;
	
	/**
	 * Present langauge tag.
	 *
	 * @var    string
	 * @since  1.0.0
	 */	
	protected $_present_language;
	
	/**
	 * Default langauge tag.
	 *
	 * @var    string
	 * @since  1.0.0
	 */		
	protected $_default_language;

	/**
	 * Temporary array of children products.
	 *
	 * @var    array
	 * @since  1.0.0
	 */		
	protected $_tmp_children = array();

	/**
	 * Present order by field name.
	 *
	 * @var    string
	 * @since  1.0.0
	 */	
	protected $_ordering;
	
	/**
	 * Present order by direction.
	 *
	 * @var    string
	 * @since  1.0.0
	 */		
	protected $_direction;
	
	/**
	* Stores the loaded membership data
	* 
	* @var 		array
	* @since	1.0.0 
	*/
	protected $_memberships = array();
	
	protected $_do_ordering = true;
	
	protected $_alias = array();
	
	protected $_byalias = array();
	
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.0.0
	 */
	public function __construct($options = array(), $filters = array())
	{
		$this->_db = $this->loadDbo();
		$this->setLanguage();		
		
		$options['list_type']							= (isset($options['list_type'])) ? strtolower(trim($options['list_type'])) : 'all';
		$options['categories_as_filter']	= (isset($options['categories_as_filter'])) ? $options['categories_as_filter'] : true;
		$options['custom_fields']					= (isset($options['custom_fields'])) ? $options['custom_fields'] : true;
		$options['attributes']						= (isset($options['attributes'])) ? $options['attributes'] : true;
		$options['manufacturer']					= (isset($options['manufacturer'])) ? $options['manufacturer'] : true;
		$options['vendor']								= (isset($options['vendor'])) ? $options['vendor'] : true;
		$options['membership']						= (isset($options['membership'])) ? $options['membership'] : true;
		$options['pricing']								= (isset($options['pricing'])) ? (int)$options['pricing'] : true;
		$options['rating']								= (isset($options['rating'])) ? $options['rating'] : true;
		$options['countresult']						= (isset($options['countresult'])) ? (int)$options['countresult'] : false;
		$options['access']								= (isset($options['access'])) ? $options['access'] : true;
		$options['quantity']							= (isset($options['quantity'])) ? $options['quantity'] : null;		
		$options['category_params']				= (isset($options['category_params'])) ? $options['category_params'] : null;
		$options['limitstart']						= (isset($options['limitstart'])) ? $options['limitstart'] : null;
		$options['limit']									= (isset($options['limit'])) ? $options['limit'] : null;
		$options['user_id']								= (isset($options['user_id']) && $options['user_id']) ? $options['user_id'] : null;
		
		$this->_options = $options;		
		
		$filters['search']								= (isset($filters['search'])) ? $filters['search'] : null;
		$filters['searchphrase']					= (isset($filters['searchphrase'])) ? $filters['searchphrase'] : null;
		$filters['vendors']								= (isset($filters['vendors'])) ? $filters['vendors'] : null;		
		$filters['ordering']							= (isset($filters['ordering'])) ? $filters['ordering'] : null;
		$filters['direction']							= (isset($filters['direction'])) ? $filters['direction'] : null;
		$filters['state']									= (isset($filters['state'])) ? $filters['state'] : null;
		$filters['hide_block']						= (isset($filters['hide_block'])) ? $filters['hide_block'] : true;
		$filters['manufacturers']					= (isset($filters['manufacturers'])) ? $filters['manufacturers'] : null;
		$filters['attributes']						= (isset($filters['attributes'])) ? $filters['attributes'] : null;
		$filters['min_price']							= (isset($filters['min_price'])) ? $filters['min_price'] : null;
		$filters['max_price']							= (isset($filters['max_price'])) ? $filters['max_price'] : null;
		$filters['only_in_stock']					= (isset($filters['only_in_stock'])) ? $filters['only_in_stock'] : null;
		
		$this->_filters = $filters;				

		return true;
	}	
	
	/**
	 * Returns a reference to a QZProducts object
	 *
	 * @param   array   $options    An array of options
	 * @param   array   $filters    An array of filters
	 *
	 * @return  QZProducts         QZProducts object
	 *
	 * @since   1.0.0
	 */
	public static function getInstance($options = array(), $filters = array())
	{
		$hash = md5(serialize($options).serialize($filters));

		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}

		self::$instances[$hash] = new QZProducts($options, $filters);
		
		return self::$instances[$hash];
	}	
	
	/**
	* Get the list of available order by fields
	* 
	* @return	array		Array of ordering fields where key is the field name with exact alias
	* @since	1.0.0
	*/	
	public static function getOrderingFields($formField = true)
	{
		$fields = array(
			'p.product_id'=>'COM_QAZAP_PRODUCT_ID',
			'p.ordering'=>'COM_QAZAP_ORDERING',
			'pd.product_name'=>'COM_QAZAP_PRODUCT_NAME', // Product Name
			'p.product_sku'=>'COM_QAZAP_PRODUCT_SKU',
			'p.featured'=>'COM_QAZAP_FEATURED_PRODUCT',
			'v.shop_name'=>'COM_QAZAP_VENDOR', // Vendor Name
			'm.manufacturer_name'=>'COM_QAZAP_MANUFACTURER', // Manufacturer Name
			'cd.title'=>'COM_QAZAP_PRODUCT_CATEGORY', // Category title
			'p.product_baseprice'=>'COM_QAZAP_BASEPRICE', // Base Price
			'sprice.product_salesprice' => 'COM_QAZAP_SALES_PRICE', // Sales Price
			//'p.in_stock'=>'COM_QAZAP_FORM_LBL_PRODUCT_CATEGORY', // In Stock
			'p.ordered'=>'COM_QAZAP_ORDERED', // Ordered Quantity
			'p.product_length'=>'COM_QAZAP_PRODUCT_LENGTH', // Length
			'p.product_width'=>'COM_QAZAP_PRODUCT_WIDTH', // Width
			'p.product_height'=>'COM_QAZAP_PRODUCT_HEIGHT', // Height
			'p.product_weight'=>'COM_QAZAP_PRODUCT_WEIGHT', // Weight
			'u.name'=>'COM_QAZAP_CREATED_BY', // Created By
			'p.hits'=>'COM_QAZAP_MOST_VIEWED', // Hits		
		);
							
		if($formField)
		{
			return $fields;
		}
		
		$fields = array_keys($fields);
		
		foreach($fields as $key=>$value)
		{
			$tmp = $value;
			$value = explode('.', $value);
			
			if($tmp == 'cd.title')
			{
				$fields[$tmp] = 'category';
			}
			elseif($tmp == 'u.name')
			{
				$fields[$tmp] = 'createdby';
			}
			else
			{
				$fields[$tmp] = $value[1];
			}
			
			unset($fields[$key]);
		}
		
		return $fields;
	}
	
	public static function getDefaultOrder($config = null)
	{
		$config = $config ? $config : QZApp::getConfig();
		$default =  $config->get('product_order', 'p.ordering');
		$fields = self::getOrderingFields(false);
		if(!array_key_exists($default, $fields))
		{
			return false;
		}
		// Return the key of the value from fields array
		return $fields[$default];			
	}
	
	/**
	* Get the field name with alias from url variable of ordering\
	* 
	* @param		string 			$value	Ordering url variable
	* 
	* @return		string/false		Query field name with alias or false if invalid
	* @since		1.0.0
	*/	
	protected function getFieldByValue($value = null)
	{		
		$fields = self::getOrderingFields(false);
		
		if(!$value || !in_array($value, $fields))
		{
			return false;
		}
		
		// Return the key of the value from fields array
		return array_search($value, $fields);		
	}
	/**
	* Method to set present language parameters in QZProducts object
	* 
	* @since	1.0.0
	*/	
	protected function setLanguage()
	{
		$lang = JFactory::getLanguage();
		$this->_multiple_language = JLanguageMultilang::isEnabled();
		$this->_present_language = $lang->getTag();
		$this->_default_language = $lang->getDefault();		
	}
	
	/**
	 * Method to set the database driver object
	 *
	 * @param   JDatabaseDriver  $db  A JDatabaseDriver based object
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function getDbo()
	{
		return $this->_db;
	}
	
	/**
	 * Load the database driver.
	 *
	 * @return  JDatabaseDriver  The database driver.
	 *
	 * @since   1.0.0
	 */
	protected function loadDbo()
	{
		return JFactory::getDbo();
	}	
	
	/**
	* Get a single product QZProductNode object
	* 
	* @param	integer		$product_id		(Required) product id of the product
	* @param	integer		$category_id	(Optional) Category id of the product
	* @param	integer		$parent_id		(optional) Parent product id
	* 
	* @return		QZProductNode object
	* @since		1.0.0
	*/	
	public function get($product_id = NULL, $category_id = NULL, $parent_id = NULL)
	{		
		$product_id = (int) $product_id;
		
		if(!$product_id)
		{
			return false;
		}
		
		$category_id = $category_id ? (int) $category_id : array();

		$this->_do_ordering = false;
		
		$result = $this->getList($category_id, $product_id, $parent_id);
		
		if(isset($result[$product_id]))
		{
			return $result[$product_id];
		}
		
		return false;
	}
	
	/**
	 * Loads a specific product and its children in a QZProductNode object
	 *
	 * @param   mixed    $product_id         an optional id integer or equal to 'root'
	 * @param   boolean  $forceload  True to force  the _load method to execute
	 *
	 * @return  mixed    JCategoryNode object or null if $id is not valid
	 *
	 * @since   1.0.0.0
	 */
	public function getList($category_ids = array(), $product_ids = array(), $parent_id = NULL, $forceload = false)
	{
		$category_ids = (array) $category_ids;	
		$product_ids = (array) $product_ids;	
		
		// Convert category ids to integer
		$category_ids = array_map('intval', $category_ids);
		$product_ids = array_map('intval', $product_ids); 
		
		// If 0 exists load all categories
		if(in_array(0, $category_ids))
		{
			$category_ids = array();
		}
		
		// If 0 exists load all products
		if(in_array(0, $product_ids))
		{
			$product_ids = array();
		}		
		
		$this->_hash = md5('category_ids:'.serialize($category_ids).'product_ids:'.serialize($product_ids).'parent_id:'.$parent_id.'options'.serialize($this->_options).serialize($this->_filters));
		
		// If this $category_ids have not been processed yet, execute the _load method
		if ((!isset($this->_nodes[$this->_hash]) && !isset($this->_checked[$this->_hash])) || $forceload)
		{
			$this->_loadList($category_ids, $product_ids, $parent_id, $forceload);
		}

		// If we already have a value in _nodes for this $_hash, then use it.
		if (isset($this->_nodes[$this->_hash]))
		{
			return $this->_nodes[$this->_hash];
		}
		// If we processed this $category_hash already and it was not valid, then return null.
		elseif (isset($this->_checked[$this->_hash]))
		{
			return null;
		}

		return false;
	}

	/**
	* Get total count of the available products
	* 
	* @return	integer		Product count
	* @since	1.0.0
	*/	
	public function getCount()
	{
		if (isset($this->_count[$this->_hash]))
		{
			return $this->_count[$this->_hash];
		}
		
		return false;		
	}
	
	/**
	* Method to get Product Alias of a product
	* 
	* @param undefined $product_id
	* 
	* @return string
	*/
	
	public function getAlias($product_id)
	{
		if(!isset($this->_alias[$product_id]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('product_alias');
			$query->from('#__qazap_product_details');
			$query->where('product_id = ' . (int) $product_id);	
			
			if($this->_multiple_language)
			{
				$query->where('language = ' . $db->quote($this->_present_language));
			}
			else
			{
				$query->where('language = ' . $db->quote($this->_default_language));
			}
			
			$db->setQuery($query);
			$result = $db->loadResult();
			
			if(empty($result))
			{
				$this->_alias[$product_id] = (string) $product_id;
			}
			else
			{
				$this->_alias[$product_id] = (string) $result;
			}			
		}
		
		return $this->_alias[$product_id];
	}
	
	public function getByAlias($alias, $category_id = null)
	{
		$alias = (string) $alias;
		
		if(empty($alias))
		{
			return null;
		}
		
		if(isset($this->_byalias[$alias]))
		{
			return $this->_byalias[$alias];
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
						->select('p.product_id, p.category_id')
						->from('#__qazap_products AS p')
						->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = p.product_id')
						->where('pd.product_alias = ' . $db->quote($alias));
						
		if(!empty($category_id))
		{
			$query->where('p.category_id = ' . (int) $category_id);
		}
		
		$db->setQuery($query);
		$result = $db->loadObject();
		
		if(!empty($result))
		{
			$this->_byalias[$alias] = $result;
		}
		else
		{
			$this->_byalias[$alias] = null;
		}
		
		return $this->_byalias[$alias];
	}

	/**
	* Set the query for product result
	* 
	* @param undefined $category_ids
	* 
	*/
	protected function _getQuery($category_ids = array())
	{		
		if($this->_options['user_id'])
		{
			$user = JFactory::getUser($this->_options['user_id']);
		}
		else
		{
			$user = JFactory::getUser();
		}
		
		$db = $this->getDbo();		
		$app = JFactory::getApplication();
		$config = QZApp::getConfig();
		$priceTableJoined = false;	
		
		$query = $db->getQuery(true);

		// Right join with c for category
		$query->select('p.product_id, p.ordering, p.state, p.block, p.parent_id, p.product_sku,
			p.featured, p.vendor, p.urls, p.manufacturer_id, p.category_id, p.access, p.multiple_pricing, 
			p.dbt_rule_id, p.dat_rule_id, p.tax_rule_id, p.in_stock, p.ordered, p.booked_order, p.product_length, 
			p.product_length_uom, p.product_width, p.product_height, p.product_weight, p.product_weight_uom, 
			p.product_packaging, p.product_packaging_uom, p.units_in_box, p.images, p.related_categories, 
			p.related_products, p.membership, p.params, p.checked_out, p.checked_out_time,
			p.created_by, p.created_time, p.modified_by, p.modified_time, p.hits');
			
		$query->from('#__qazap_products AS p');
		
		if(($this->_filters['only_in_stock'] === null))
		{
			$only_in_stock = ($config->get('stockout_action') == 'hide_product');
		}
		else
		{
			$only_in_stock = $this->_filters['only_in_stock'];
		}
		
		if($only_in_stock  && $config->get('enablestockcheck'))
		{
			$query->where('(p.in_stock - p.booked_order) > 0');
		}
		
		if($this->_options['manufacturer'])
		{
			$query->select('m.manufacturer_name, m.manufacturer_email, m.manufacturer_category, m.description AS manufacturer_description, m.manufacturer_url, m.images AS manufacturer_images');
			$query->join('LEFT', '#__qazap_manufacturers AS m ON m.id = p.manufacturer_id');
		}
		
		$query->select('v.vendor_admin, v.vendor_group_id, v.shop_name');
		$query->join('INNER', '#__qazap_vendor AS v ON v.id = p.vendor');	

		if ($this->_options['access'])
		{
			$query->where('p.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}
		
		if($this->_options['rating'])
		{
			$subQueryRating = ' (SELECT rev.product_id, AVG(rev.rating) AS rating, COUNT(rev.id) AS review_count '.
												'FROM #__qazap_reviews AS rev JOIN #__users AS ru ON ru.id = rev.user_id '.
												'WHERE rev.state = 1 AND ru.block = 0 GROUP BY rev.product_id) ';
												
			$query->select('rating.rating, rating.review_count')
						->join('LEFT', $subQueryRating . 'AS rating ON rating.product_id = p.product_id');
		}
		
		if($user->guest)
		{
			$vendor_id = 0;
		}
		else
		{
			$qzuser = QZUser::get();
			$vendor_id = (int) $qzuser->get('vendor_id', 0);
		}

		if ($this->_filters['state'])
		{
			if($vendor_id > 0)
			{
				$query->where('CASE WHEN p.vendor <> '. $vendor_id .' THEN p.state = ' . (int) $this->_filters['state'] .' ELSE TRUE END'); 
			}
			else
			{
				$query->where('p.state = ' . (int) $this->_filters['state']);
			}			
		}
		else
		{
			if($vendor_id > 0)
			{
				$query->where('CASE WHEN p.vendor <> '. $vendor_id .' THEN p.state = 1 ELSE TRUE END'); 
			}
			else
			{
				$query->where('p.state = 1');
			}			
		}
		
		if($this->_options['list_type'] == 'featured')
		{
			$query->where('p.featured = 1');
		}
		elseif($this->_options['list_type'] == 'topselling')
		{
/*			$oiSubquery = ' (SELECT oi.product_id AS product_id, SUM(oi.product_quantity) AS order_quantity '.
										'FROM #__qazap_order_items AS oi WHERE oi.deleted = 0 GROUP BY oi.product_id )';
			$query->join('INNER', $oiSubquery . ' AS orderitems ON orderitems.product_id = p.product_id');*/
			$query->order($db->escape('p.ordered DESC, p.booked_order DESC'));
			$this->_do_ordering = false;
		}
		elseif($this->_options['list_type'] == 'latest')
		{
			$query->order($db->escape('p.product_id DESC'));
			$this->_do_ordering = false;
		}
		elseif($this->_options['list_type'] == 'random')
		{
			$query->order($db->escape('RAND()'));
			$this->_do_ordering = false;
		}		
				
		if($config->get('show_inactive_vendor_products', 0) == 0)
		{
			$query->where('v.state = 1');
		}
		
		if ($this->_filters['hide_block'])
		{
			if($vendor_id > 0)
			{
				$query->where('CASE WHEN p.vendor <> '. $vendor_id .' THEN p.block = 0 ELSE TRUE END'); 
			}
			else
			{
				$query->where('p.block = 0');
			}			
		}

		if (!empty($this->_filters['vendors']))
		{
			$this->_filters['vendors'] = (array) $this->_filters['vendors'];
			$this->_filters['vendors'] = array_map('intval', $this->_filters['vendors']);
			
			$query->where('p.vendor IN (' . implode(',', $this->_filters['vendors']). ')');
		}
		
		if (!empty($this->_filters['manufacturers']))
		{
			$this->_filters['manufacturers'] = (array) $this->_filters['manufacturers'];
			$this->_filters['manufacturers'] = array_map('intval', $this->_filters['manufacturers']);
			
			$query->where('p.manufacturer_id IN (' . implode(',', $this->_filters['manufacturers']). ')');
		}
		
		if(!empty($this->_filters['attributes']))
		{
			$filter_attr = (array) $this->_filters['attributes'];
			$filter_attr = array_map('intval', $filter_attr);
			$attrCount = count($filter_attr);
			
			$ftSubQuery = ' (SELECT attr.product_id FROM #__qazap_cartattributes AS attr JOIN #__qazap_cartattributes AS fattr ' .
				'ON fattr.value = attr.value WHERE fattr.id IN (' . implode(',', $filter_attr) . ') GROUP BY attr.product_id) ';
			$query->join('LEFT', $ftSubQuery . 'AS filterAttr ON filterAttr.product_id = p.product_id')
				->where('filterAttr.product_id IS NOT null');
						
		}
		
		// If multiple product pricing enabled
		$multple_pricing = $config->get('multiple_product_pricing', false);
		
		if($multple_pricing)
		{
			$case1  = '(CASE p.multiple_pricing ';
			$case1 .= 'WHEN 0 THEN p.product_baseprice ';		
			$case1 .= 'WHEN 1 THEN up.product_baseprice ';
			$case1 .= 'WHEN 2 THEN qp.product_baseprice ';	
			$case1 .= 'END) AS product_baseprice';
			$query->select($case1);
			
			$case2  = '(CASE p.multiple_pricing ';
			$case2 .= 'WHEN 0 THEN p.product_customprice ';		
			$case2 .= 'WHEN 1 THEN up.product_customprice ';
			$case2 .= 'WHEN 2 THEN qp.product_customprice ';	
			$case2 .= 'END) AS product_customprice';
			$query->select($case2);					
			
			// Join Usergroup based pricing
			
			if($user->guest)
			{				
				$query->join('LEFT', '#__qazap_product_user_price AS up ON up.product_id = p.product_id AND up.usergroup_id = 1');
			}
			else
			{
				$query->join('LEFT', '#__qazap_product_user_price AS up ON up.product_id = p.product_id AND up.usergroup_id IN('. implode(',', $user->groups) . ')');
			}
			
			// Join quantity based pricing
			$quantity = $this->_options['quantity'] ? $this->_options['quantity'] : (int) $config->get('minimum_purchase_quantity', 1);
			
			$query->join('LEFT', '#__qazap_product_quantity_price AS qp ON qp.product_id = p.product_id AND qp.max_quantity >= '.$quantity.' AND qp.min_quantity <= '.$quantity);
			
			// Filter by base price
			if($config->get('price_filter_type', 'baseprice') == 'baseprice')
			{
				if($this->_filters['min_price'] !== NULL && $this->_filters['max_price'] !== NULL)
				{
					$pricing_where  = 'CASE p.multiple_pricing ';	
					$pricing_where .= 'WHEN 0 THEN p.product_baseprice ';		
					$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
					$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
					$pricing_where .= 'END BETWEEN '.(float) $this->_filters['min_price'].' AND '.(float) $this->_filters['max_price'];
					$query->where($pricing_where);
				}
				elseif($this->_filters['min_price'] !== NULL)
				{
					$pricing_where  = (float) $this->_filters['min_price'].' <= ';
					$pricing_where .= 'CASE p.multiple_pricing ';	
					$pricing_where .= 'WHEN 0 THEN p.product_baseprice ';		
					$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
					$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
					$pricing_where .= 'END ';
					$query->where($pricing_where);
				}
				elseif($this->_filters['max_price'] !== NULL)
				{
					$pricing_where  = (float) $this->_filters['max_price'].' >= ';
					$pricing_where .= 'CASE p.multiple_pricing ';	
					$pricing_where .= 'WHEN 0 THEN p.product_baseprice ';		
					$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
					$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
					$pricing_where .= 'END ';
					$query->where($pricing_where);
				}					
			}
		
		}
		else 
		{
			$query->select('p.product_baseprice AS product_baseprice, p.product_customprice AS product_customprice');
			
			// Filter by base price
			if($config->get('price_filter_type', 'baseprice') == 'baseprice')
			{
				if($this->_filters['min_price'] !== NULL && $this->_filters['max_price'] !== NULL)
				{
					$query->where('p.product_baseprice BETWEEN '.(float) $this->_filters['min_price'].' AND '.(float) $this->_filters['max_price']);
				}
				elseif($this->_filters['min_price'] !== NULL)
				{
					$query->where('p.product_baseprice >= '. (float) $this->_filters['min_price']);
				}
				elseif($this->_filters['max_price'] !== NULL)
				{
					$query->where('p.product_baseprice <= '. (float) $this->_filters['max_price']);
				}				
			}
		}
		
		
		// Special system to filter directly by Sales Price
		if(($config->get('price_filter_type', 'baseprice') == 'salesprice') && ($this->_filters['min_price'] !== NULL || $this->_filters['max_price'] !== NULL))
		{
			$salepriceSubQuery = QZFilters::getInstance()->getSalesPriceSubQuery();
			$query->join('INNER', '(' . $salepriceSubQuery . ') as sprice ON sprice.product_id = p.product_id');
			
			if($this->_filters['min_price'] !== NULL && $this->_filters['max_price'] !== NULL)
			{
				$query->where('sprice.product_salesprice BETWEEN '.(float) $this->_filters['min_price'].' AND '.(float) $this->_filters['max_price']);
			}
			elseif($this->_filters['min_price'] !== NULL)
			{
				$query->where('sprice.product_salesprice >= '. (float) $this->_filters['min_price']);
			}
			elseif($this->_filters['max_price'] !== NULL)
			{
				$query->where('sprice.product_salesprice <= '. (float) $this->_filters['max_price']);
			}
			
			$priceTableJoined = true;			
		}
		
		
		$query->select('c.access AS category_access, c.params AS category_params');
		$query->join('LEFT', '#__qazap_categories AS c ON c.category_id = p.category_id');		
					
		if($category_count = count($category_ids))
		{
			// selected category_id
			if($category_count == 1)
			{
				$catids = array_values($category_ids);
				// Get products from the selected category
				if($this->_options['categories_as_filter'])
				{
					$query->join('LEFT', '#__qazap_categories AS s ON (s.lft <= c.lft AND s.rgt >= c.rgt)')
								->where('s.category_id = ' . $catids[0]);					
				}
				else
				{
					$query->where('p.category_id = ' . $catids[0]);	
				}

			}
			else
			{
				// Get products from the selected categories
				if($this->_options['categories_as_filter'])
				{						
					$query->join('LEFT', '#__qazap_categories AS s ON (s.lft <= c.lft AND s.rgt >= c.rgt)')
							->where('s.category_id IN(' . implode(',', $category_ids) .')');
				}
				else
				{
					$query->where('p.category_id IN(' . implode(',', $category_ids) .')');
				}
			}						

			$ordering_hash = ($category_count > 1) ? md5(serialize($category_ids)) : (int) $category_ids[0];
			$ordering_fields = self::getOrderingFields(false);
			//echo str_replace('#__', 'f8rup_', $query);exit;
			if(!$this->_ordering)
			{
				$default_ordering =  self::getDefaultOrder();			
				$last_ordering = $app->getUserState('com_qazap.product_list.ordering'.$ordering_hash, $default_ordering);	
						
				if($this->_filters['ordering'] && $ordering_field = $this->getFieldByValue($this->_filters['ordering']))
				{			
					$this->_ordering = $ordering_field;
				}
				elseif($last_ordering_field = $this->getFieldByValue($last_ordering))
				{
					$this->_ordering = $last_ordering_field;
				}
				else
				{
					$this->_ordering = 'p.ordering';
				}			
			}

			if(!$this->_direction)
			{
				if(isset($ordering_fields[$this->_ordering]) && $ordering_fields[$this->_ordering] == $last_ordering)
				{
					$last_direction = $app->getUserState('com_qazap.product_list.direction'.$ordering_hash, 'ASC');
				}
				else
				{
					$last_direction = $config->get('product_order_dir', 'ASC');
				}
				
				if($this->_filters['direction'] && in_array(strtoupper($this->_filters['direction']), array('ASC', 'DESC', '')))			
				{			
					$this->_direction = $this->_filters['direction'];
				}
				else
				{
					$this->_direction = $last_direction;
				}			
			}

			if($this->_do_ordering)
			{
				$app->setUserState('com_qazap.product_list.ordering'.$ordering_hash, $ordering_fields[$this->_ordering]);
				$app->setUserState('com_qazap.product_list.direction'.$ordering_hash, $this->_direction);
				
				// Set order and order direction
				if($this->_ordering == 'sprice.product_salesprice' && !$priceTableJoined)
				{
					$salepriceSubQuery = QZFilters::getInstance()->getSalesPriceSubQuery();
					$query->join('INNER', '(' . $salepriceSubQuery . ') as sprice ON sprice.product_id = p.product_id');				
				}				
				
				$query->order($db->escape($this->_ordering . ' ' . $this->_direction));			
			}			
		}
		
		$subQuery = ' (SELECT cat.category_id FROM #__qazap_categories AS cat JOIN #__qazap_categories AS parentcat' .
			' ON cat.lft BETWEEN parentcat.lft AND parentcat.rgt WHERE parentcat.published != 1'.
			' OR parentcat.access NOT IN (' . implode(',', $user->getAuthorisedViewLevels()) . ') GROUP BY cat.category_id) ';
		$query->join('LEFT', $subQuery . 'AS badcats ON badcats.category_id = p.category_id')
			->where('badcats.category_id is null');	
				
		// Group by
		$query->group(
		 'p.product_id, p.ordering, p.state, p.block, p.parent_id, p.product_sku,
			p.featured, p.vendor, p.urls, p.manufacturer_id, p.category_id, p.access, p.product_baseprice, 
			p.product_customprice, p.multiple_pricing, p.dbt_rule_id, p.dat_rule_id, p.tax_rule_id, p.in_stock,
			p.ordered, p.booked_order, p.product_length, p.product_length_uom, p.product_width, p.product_height,
			p.product_weight, p.product_weight_uom, p.product_packaging, p.product_packaging_uom, p.units_in_box,
			p.images, p.related_categories, p.related_products, p.membership, p.params, p.checked_out, p.checked_out_time,
			p.created_by, p.created_time, p.modified_by, p.modified_time, p.hits'
		);
		
		return clone $query;	
			
	}
	
	
	protected function _getDetailsQuery($query)
	{		
		$db = $this->getDbo();
		
		$query->select('pd.product_alias, pd.short_description, pd.product_description, pd.metakey, pd.metadesc, pd.metadata');
		
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('pd.product_alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('p.product_id');
		$case_when .= $query->concatenate(array($c_id, 'pd.product_alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when);		
			
		if($this->_multiple_language)
		{
			$query->select('CASE WHEN pd.product_name IS NULL THEN pdd.product_name ELSE pd.product_name END AS product_name');			
		
			$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = p.product_id AND pd.language = '.$db->quote($this->_present_language));
			$query->join('LEFT', '#__qazap_product_details AS pdd ON pdd.product_id = p.product_id AND pdd.language = '.$db->quote($this->_default_language));				
		}
		else
		{
			$query->select('pd.product_name');
			$query->join('INNER', '#__qazap_product_details AS pd ON pd.product_id = p.product_id AND pd.language = '.$db->quote($this->_default_language));
		}	

		// join category details table. Note: cd is for category details
		$query->select('cd.title AS category_name');
		if($this->_multiple_language)
		{
			$query->join('INNER', '#__qazap_category_details AS cd ON cd.category_id = p.category_id AND cd.language = '.$db->quote($this->_present_language));				
		}
		else
		{
			$query->join('INNER', '#__qazap_category_details AS cd ON cd.category_id = p.category_id AND cd.language = '.$db->quote($this->_default_language));
		}
		
		return clone $query;
	}
	/**
	 * Load method
	 *
	 * @param   array  $category_ids  Ids of category for which the products to be loaded
	 * @param   array  $product_ids  Ids of product for which the products to be loaded
	 * @param   array  $parent_id  Parent product id for which the products to be loaded
	 * @param   array  $forceload  Force load
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function _loadList($category_ids, $product_ids, $parent_id, $forceload)
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$config = QZApp::getConfig();

		// Record that has this $hash has been checked
		$this->_checked[$this->_hash] = true;

		$query = $this->_getQuery($category_ids);	
		
		// load only selected products and its children
		if($id_count = count($product_ids))
		{
			if($id_count == 1)
			{
				$product_ids = array_values($product_ids);
				$query->where('p.product_id = ' . $product_ids[0]);				
			}
			else
			{
				$query->where('p.product_id IN(' . implode(',', $product_ids) .')');
			}			
		}
		
		// load children only for selected parent product
		if($parent_id)
		{
			$query->where('p.parent_id = '. (int) $parent_id);
		}
		elseif(empty($product_ids))
		{
			$query->where('p.parent_id = 0');
		}
		
		if(!empty($this->_filters['search']))
		{
			$searchword = $this->_filters['search'];
			$searchphrase = (!empty($this->_filters['searchphrase'])) ? $this->_filters['searchphrase'] : 'any';
			
			switch($searchphrase)
			{
				case 'exact':
					$text = $db->quote('%' . $db->escape($searchword, true) . '%', false);
					$wheres2 = array();
					if($this->_multiple_language)
					{
						$wheres2[] = 'CASE WHEN pd.product_name IS NULL THEN pdd.product_name ELSE pd.product_name END LIKE '. $text;
					}
					else
					{
						$wheres2[] = 'pd.product_name LIKE ' . $text;
					}
					$wheres2[] = 'cd.title LIKE ' . $text;
					$wheres2[] = 'm.manufacturer_name LIKE ' . $text;
					$wheres2[] = 'v.shop_name LIKE ' . $text;

					$where = '(' . implode(') OR (', $wheres2) . ')';				
				break;
				
				case 'all':
				case 'any':
				default:
					$words = explode(' ', $searchword);
					$wheres = array();
					foreach ($words as $word)
					{
						$word = $db->quote('%' . $db->escape($word, true) . '%', false);
						$wheres2 = array();
						if($this->_multiple_language)
						{
							$wheres2[] = 'CASE WHEN pd.product_name IS NULL THEN pdd.product_name ELSE pd.product_name END LIKE '. $word;
						}
						else
						{
							$wheres2[] = 'pd.product_name LIKE ' . $word;
						}						
						$wheres2[] = 'cd.title LIKE ' . $word;
						$wheres2[] = 'm.manufacturer_name LIKE ' . $word;
						$wheres2[] = 'v.shop_name LIKE ' . $word;
						
						$wheres[] = implode(' OR ', $wheres2);
					}
					
				$where = '(' . implode(($searchphrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
			}
			
			$query->where($where);
		}		
		
		$detailsJoined = false;
		// If count result and not yet checked		
		if($this->_options['countresult'] && (!isset($this->_count[$this->_hash]) || $forceload))
		{
			if(!empty($this->_filters['search']))
			{
				$query = $this->_getDetailsQuery($query);
				$detailsJoined = true;
			}
			
			$this->_count[$this->_hash] = $this->_getListCount($query);
		}	
		
		if($this->_options['countresult'] && !$this->_count[$this->_hash])
		{
			$this->_nodes[$this->_hash] = null;
			return;
		}	

		// Count is done now join the details tables
		if(!$detailsJoined)
		{
			$query = $this->_getDetailsQuery($query);
		}		
		//echo str_replace('#__', 'f8rup_', $query);exit;

		// Get the results
		if($this->_options['limitstart'] !== NULL && $this->_options['limit'] !== NULL)
		{
			$db->setQuery($query, (int) $this->_options['limitstart'], (int) $this->_options['limit']);
		}
		else
		{
			$db->setQuery($query);	
		}
		//print(str_replace('#__','f8rup_',$query));exit;		
		// Load product list
		$results = $db->loadObjectList('product_id');
				
		//QZApp::dump($results);exit;

		if (count($results))
		{
			$this->_product_ids = array_keys($results);
			
			if($this->_options['custom_fields'])
			{
				$custom_fields = $this->_loadCustomFields();
			}
			
			if($this->_options['attributes'])
			{
				$attributes = $this->_loadAttributes();
			}
			
			// If multiple product pricing enabled
			$multple_pricing = $config->get('multiple_product_pricing', false);
			
			if($this->_options['pricing'] && $multple_pricing)
			{
				$quantity_prices = $this->_loadQuantityPrices();
			}
			
			// Foreach categories
			foreach ($results as $result)
			{
				// Deal with parent_id
				if ($result->parent_id == 0)
				{
					$result->parent_id = 'root';
				}

				if($this->_options['custom_fields'] && $custom_fields && array_key_exists($result->product_id, $custom_fields))
				{
					$result->custom_fields = $custom_fields[$result->product_id];
				}
				else
				{
					$result->custom_fields = NULL;
				}
		
				if($this->_options['attributes'] && $attributes && array_key_exists($result->product_id, $attributes))
				{
					$result->attributes = $attributes[$result->product_id];
				}
				else
				{
					$result->attributes = NULL;
				}		
				
				if($this->_options['pricing'] && $multple_pricing && $quantity_prices && array_key_exists($result->product_id, $quantity_prices))
				{
					$result->product_quantity_prices = $quantity_prices[$result->product_id];
				}
				else
				{
					$result->product_quantity_prices = NULL;
				}
				
				if(is_string($result->images) && $result->images)
				{
					$result->images = json_decode($result->images);
				}
					
				if(is_string($result->manufacturer_images) && $result->manufacturer_images)
				{
					$result->manufacturer_images = json_decode($result->manufacturer_images);
				}
				
				if(is_string($result->urls) && $result->urls)
				{
					$result->urls = json_decode($result->urls);
				}								
				// Create the node
				if (!isset($this->_nodes[$this->_hash]))
				{
					$this->_nodes[$this->_hash] = array();
				}
				
				if(!isset($this->_nodes[$this->_hash][$result->product_id]))
				{
					$this->_nodes[$this->_hash][$result->product_id] = new QZProductNode($result, $this);					
					if($this->_options['user_id'])
					{
						$this->_nodes[$this->_hash][$result->product_id]->set('user_id', $this->_options['user_id']);
					}
					$this->_nodes[$this->_hash][$result->product_id]->setPrices();
				}
			}
		}
		else
		{
			$this->_nodes[$this->_hash] = null;
		}
	}	

	/**
	* Method to get left and right sibling of a product
	* 
	* @param	integer	$product product_id of the product for which siblings need to be found
	* 
	* @return	array		array('left'=>, 'right'=>)
	* @since	1.0.0
	*/	
	public function getSiblings($product)
	{		
		if(isset($this->_checkedSiblings[$product->product_id]))
		{
			return $this->_checkedSiblings[$product->product_id];
		}
		
		$siblings = array('left'=>null, 'right'=>null);
		$db = $this->getDbo();
	
		foreach($siblings as $type=>&$sibling)
		{
			$query = $this->_getQuery(array($product->category_id));
			$query->clear('select')->clear('group')->select('p.product_id, p.category_id');
			
			if($this->_ordering == 'cd.title' || $this->_ordering == 'pd.product_name')
			{
				$query = $this->_getDetailsQuery($query);
			}			

			if(strtoupper($this->_direction) == 'ASC')
			{
				if($type == 'left')
				{
					$direction = 'DESC';
					$operation = ' <= ';
				} 
				else 
				{
					$direction = 'ASC';
					$operation = ' >= ';
				}				
			}
			else
			{
				if($type == 'left')
				{
					$direction = 'ASC';
					$operation = ' >= ';					
				} 
				else 
				{
					$direction = 'DESC';
					$operation = ' <= ';
				}				
			}
			
			if($this->_ordering == 'cd.title')
			{
				$field_name = 'category_name';
			}
			else
			{
				$parts = explode('.', $this->_ordering);
				$field_name = $parts[1];
			}			
			
			if(isset($product->$field_name))
			{
				$order_value = $product->$field_name;
				$order_name = $this->_ordering;
			}
			else
			{
				$order_value = $product->ordering;
				$order_name = 'p.ordering';
			}
			
			$product->parent_id = $product->parent_id == 'root' ? 0 : (int) $product->parent_id;
			
			$query->where('p.product_id != '.$product->product_id);
			$query->where('p.parent_id = '. $product->parent_id);
			$query->where($order_name . $operation . $db->quote($order_value));
			$query->order($order_name .' '.$direction);
			$db->setQuery($query);

			//echo str_replace('#__', 'f8rup_', $query);exit;
			
			$sibling = $db->loadObject();			
		}
		
		return $siblings;
	}


	/**
	 * Returns a record count for the query.
	 *
	 * @param   JDatabaseQuery|string  $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   12.2
	 */
	protected function _getListCount($query)
	{
		$db = $this->getDbo();
		// Use fast COUNT(*) on JDatabaseQuery objects if there no GROUP BY or HAVING clause:
		if ($query instanceof JDatabaseQuery
			&& $query->type == 'select'
			&& $query->having === null)
		{
			$query = clone $query;
			$query->clear('select')->clear('order')->clear('group')->select('COUNT(DISTINCT(p.product_id))');
			$db->setQuery($query);
			return (int) $db->loadResult();
		}

		// Otherwise fall back to inefficient way of counting all results.
		$db->setQuery($query);
		$db->execute();

		return (int) $db->getNumRows();
	}

	
	
	protected function _loadCustomFields()
	{
		$db = $this->getDbo();
		$product_ids = $this->_product_ids;
		$query = $db->getQuery(true);
		$query->select('a.id AS customfield_id, a.typeid AS type_id, a.value,'. 
										'a.product_id, a.ordering AS customfield_ordering');
		$query->from('#__qazap_customfield AS a');
		$query->select('b.ordering AS type_ordering, b.title AS type_title, b.show_title AS type_show_title, '.
										'b.description AS type_description, b.tooltip AS type_tooltip, b.layout_position AS type_layout_position, '.
										'b.hidden AS type_hidden, b.params AS type_params');			
		$query->join('INNER', '#__qazap_customfieldtype AS b ON b.id = a.typeid');
		$query->select('plugin.element AS type_plugin, plugin.enabled AS type_plugin_published')
					->join('LEFT', '#__extensions AS plugin ON plugin.extension_id = b.type');		
		$query->where('b.state = 1');
		$query->where('plugin.enabled = 1');
		
		if(count($product_ids) == 1)
		{
			$product_ids = array_values($product_ids);
			$query->where('a.product_id = ' .$product_ids[0]);
		}
		else
		{
			$query->where('a.product_id IN ('. implode(',', $product_ids) .')');
		}		
		
		$query->order('a.ordering ASC, b.ordering ASC');
		$query->group('a.id, a.typeid, a.value, a.product_id, a.ordering');		
		
  	try 
  	{
  		$db->setQuery($query);
			$custom_fields = $db->loadObjectList();
		} 
		catch (RuntimeException $e) 
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		if(empty($custom_fields))
		{
			return NULL;
		}
		
		$result = array();
		
		foreach($custom_fields AS $custom_field)
		{
			if(!isset($result[$custom_field->product_id]))
			{
				$result[$custom_field->product_id] = array();
			}
			
			$temp = new JRegistry;
			$temp->loadString($custom_field->type_params);
			$custom_field->type_params = $temp;
			
			$type_id = $custom_field->type_id;
			
			if(!isset($result[$custom_field->product_id][$type_id]))
			{
				$result[$custom_field->product_id][$type_id] = new stdClass;
				$result[$custom_field->product_id][$type_id]->data = array();
				$result[$custom_field->product_id][$type_id]->display = null;
				$result[$custom_field->product_id][$type_id]->compare_display = null;
			}
			
			foreach($custom_field as $key=>$value)
			{
				if(strpos($key, 'type_') === 0)
				{
					$tmp = str_replace('type_', '', $key);
					$result[$custom_field->product_id][$type_id]->$tmp = $value;
					unset($custom_field->$key);
				}
			}
			
			$result[$custom_field->product_id][$type_id]->data[] = $custom_field;
		}		
		
		return $result;
		
	}
	
	
	protected function _loadAttributes($attr_ids = null)
	{
		$db = $this->getDbo();
		$product_ids = $this->_product_ids;
		$productDisplay = true;
		
		$query = $db->getQuery(true);
		$query->select('a.id AS attribute_id, a.typeid AS type_id, a.value, a.price, a.stock, a.ordered,'.
										'a.booked_order, a.product_id, a.ordering AS attribute_ordering');
		$query->from('#__qazap_cartattributes AS a');
		$query->select('b.ordering AS type_ordering, b.title AS type_title, b.show_title AS type_show_title, '.
										'b.description AS type_description, b.tooltip AS type_tooltip, b.hidden AS type_hidden, '.
										'b.check_stock AS type_check_stock, b.params AS type_params');		
		$query->join('INNER', '#__qazap_cartattributestype AS b ON b.id = a.typeid');
		$query->select('plugin.element AS type_plugin, plugin.enabled AS type_plugin_published')
					->join('LEFT', '#__extensions AS plugin ON plugin.extension_id = b.type');
		$query->where('b.state = 1');
		$query->where('plugin.enabled = 1');
		
		if(!empty($attr_ids))
		{
			$attr_ids = (array) $attr_ids;
			$attr_ids = array_filter(array_map('intval', $attr_ids));
			$productDisplay = false;
		}
		
		if($attrCount = count($attr_ids))
		{
			if($attrCount == 1)
			{
				$attr_ids = array_values($attr_ids);
				$query->where('a.id = ' .$attr_ids[0]);
			}
			else
			{
				$query->where('a.id IN ('. implode(',', $attr_ids) .')');
			}
		}
		elseif($productCount = count($product_ids))
		{
			if($productCount == 1)
			{
				$product_ids = array_values($product_ids);
				$query->where('a.product_id = ' .$product_ids[0]);				
			}
			else
			{
				$query->where('a.product_id IN ('. implode(',', $product_ids) .')');
			}
		}
		
		$query->order('b.ordering ASC, a.ordering ASC');
		$query->group('a.id, a.typeid, a.value, a.price, a.stock, a.ordered, '.
									'a.booked_order, a.product_id, a.ordering');		
		
  	try 
  	{
  		$db->setQuery($query);
			$attributes = $db->loadObjectList();
		} 
		catch (RuntimeException $e) 
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		if(empty($attributes))
		{
			return null;
		}
		
		$result = array();
		
		if($productDisplay)
		{
			foreach($attributes AS $attribute)
			{
				if(!isset($result[$attribute->product_id]))
				{
					$result[$attribute->product_id] = array();
				}
				
				$temp = new JRegistry;
				$temp->loadString($attribute->type_params);
				$attribute->type_params = $temp;
				
				$type_id = $attribute->type_id;
				
				if(!isset($result[$attribute->product_id][$type_id]))
				{
					$result[$attribute->product_id][$type_id] = new stdClass;
					$result[$attribute->product_id][$type_id]->data = array();
					$result[$attribute->product_id][$type_id]->display = null;
					$result[$attribute->product_id][$type_id]->compare_display = null;
				}
				
				foreach($attribute as $key=>$value)
				{
					if(strpos($key, 'type_') === 0)
					{
						$tmp = str_replace('type_', '', $key);
						$result[$attribute->product_id][$type_id]->$tmp = $value;
						unset($attribute->$key);
					}
				}
				
				$result[$attribute->product_id][$type_id]->data[] = $attribute;
			}
			
			return $result;			
		}
		else
		{
			foreach($attributes AS &$attribute)
			{
				$temp = new JRegistry;
				$temp->loadString($attribute->type_params);
				$attribute->type_params = $temp;
				
				$type_id = $attribute->type_id;
				
				$attribute->display = null;
				
				foreach($attribute as $key => $value)
				{
					if(strpos($key, 'type_') === 0 && $key != 'type_id')
					{
						$tmp = str_replace('type_', '', $key);
						$attribute->$tmp = $value;
						unset($attribute->$key);
					}
				}				
			}
			
			return $attributes;			
		}

	}
	
	protected function _loadQuantityPrices()
	{
		$db = $this->getDbo();
		$product_ids = $this->_product_ids;
		$query = $db->getQuery(true);
		$query->select('quantity_price_id, product_id, min_quantity, max_quantity, product_baseprice, product_customprice');
		$query->from('#__qazap_product_quantity_price');
		$query->where('product_id IN ('.implode(',', $product_ids).')');
		$query->group('quantity_price_id, product_id, min_quantity, max_quantity, product_baseprice, product_customprice');
		
  	try {
  		$db->setQuery($query);
			$prices = $db->loadObjectList('quantity_price_id');
		} catch (RuntimeException $e) {
			JError::raiseWarning(500, $e->getMessage());
		}

		if(empty($prices))
		{
			return null;
		}
		
		$result = array();	
		
		foreach($prices AS $key=>$price)
		{
			$product_id = $price->product_id;
			
			if(!isset($result[$product_id]))
			{
				$result[$product_id] = array();
			}
			
			$result[$product_id][$key] = $price;
		}
		
		return $result;		
		
	}
	
	/**
	* Method to get all membership data
	* 
	* @param	array		$ids	Array of membership ids
	* 
	* @return	array/false	Object List of membership data or false in case of failure
	*/
	public function getMemberships($ids = array())
	{
		if ($this->_memberships === null)
		{
			$this->_memberships = array();
		}
		
		$ids = (array) $ids;
		$ids = array_filter(array_map('intval', $ids));		
		$hash = md5(serialize($ids));
		
		if(!($idCount = count($ids)) && !(isset($this->_memberships[$hash])))
		{
			$this->_memberships[$hash] = null;
		}
		
		if(isset($this->_memberships[$hash]))
		{
			return $this->_memberships[$hash];
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('a.id, a.ordering, a.state, a.plan_name, a.plan_duration, a.description, a.price, '.
									'a.access_to_members, a.jusergroup_id, a.jview_id, a.created_by, a.created_time, '.
									'a.modified_by, a.modified_time')
					->from('#__qazap_memberships as a')
					->where('a.state = 1');

		if($idCount == 1)
		{
			$query->where('a.id = '.$ids[0]);
		}
		else
		{
			$query->where('a.id IN (' . implode(',', $ids) . ')')					
					->order('a.ordering ASC');
		}

		$query->group('a.id, a.ordering, a.state, a.plan_name, a.plan_duration, a.description, a.price, '.
									'a.access_to_members, a.jusergroup_id, a.jview_id, a.created_by, a.created_time, '.
									'a.modified_by, a.modified_time');
					
  	try {
  		$db->setQuery($query);
			$this->_memberships[$hash] = $db->loadObjectList();	
		} catch (RuntimeException $e) {
			JError::raiseWarning(500, $e->getMessage());
		}
						
		return $this->_memberships[$hash];
	}
	

	protected $_selectChecked = array();
	protected $_selectNodes	= array();
	protected $_selectHash;

	public function getSelection($product_id, $attr_ids = null, $membership_id = null, $quantity = null, $forceload = false)
	{
		$options = array(
			'product_id' => $product_id,
			'attr_ids' => $attr_ids,
			'membership_id' => $membership_id,
			'quantity' => $quantity
		);
		 
		 $this->_selectHash = md5(serialize($options));

			// If this selection have not been processed yet, execute the _loadSelection method
			if ((!isset($this->_selectNodes[$this->_selectHash]) && !isset($this->_selectChecked[$this->_selectHash])) || $forceload)
			{
				$this->_options['quantity'] = $quantity;
				$this->_options['rating'] = false;
				$this->_do_ordering = false;
				$this->_loadSelection($product_id, $attr_ids, $membership_id, $quantity);
			}

			// If we already have a value in _selectNodes for this _selectHash, then use it.
			if (isset($this->_selectNodes[$this->_selectHash]))
			{
				return $this->_selectNodes[$this->_selectHash];
			}
			// If we processed this _selectHash already and it was not valid, then return null.
			elseif (isset($this->_selectChecked[$this->_selectHash]))
			{
				return null;
			}

			return false;
	}
	 
	 /**
	 * Load method for a selected product with selected attributes, membership and specific quantity
	 * 
	 * @param integer		$product_id
	 * @param array			$attr_ids
	 * @param integer		$membership_id
	 * @param integer		$quantity
	 * 
	 * @return	void
	 * @since		1.0.0
	 */	 
	protected function _loadSelection($product_id, $attr_ids = null, $membership_id = null, $quantity = null)
	{
		$db = $this->getDbo();

		// Record that has this $hash has been checked
		$this->_selectChecked[$this->_selectHash] = true;

		$query = $this->_getQuery();
		$query = $this->_getDetailsQuery($query);
		
		$query->where('p.product_id = ' . $product_id);		
	
		$db->setQuery($query);	

  	try {
  		$db->setQuery($query);
			$product = $db->loadObject();
		} catch (RuntimeException $e) {
			JError::raiseWarning(500, $e->getMessage());
		}		

		if(!empty($product))
		{
			$attr_ids = array_filter((array) $attr_ids);
			
			if(count($attr_ids))
			{
				$product->product_attributes = $this->_loadAttributes($attr_ids);				
			}
			
			if($membership_id)
			{
				if($membership = $this->getMemberships($membership_id))
				{
					$product->product_membership = $membership[0];
				}				
			}
				
			if($product->images && is_string($product->images))
			{
				$product->images = json_decode($product->images);
			}
			
			if($product->related_categories && is_string($product->related_categories))
			{
				$product->related_categories = json_decode($product->related_categories);
			}
			
			if($product->related_products && is_string($product->related_products))
			{
				$product->related_products = json_decode($product->related_products);
			}
			
			if($product->membership && is_string($product->membership))
			{
				$product->membership = json_decode($product->membership);
			}									
				
			if(isset($product->manufacturer_images) && is_string($product->manufacturer_images))
			{
				$product->manufacturer_images = json_decode($product->manufacturer_images);
			}
			
			if($product->urls && is_string($product->urls))
			{
				$product->urls = json_decode($product->urls);
			}
			
			$tmp = new JRegistry;
			$tmp->loadString($product->params);
			$product->params = $tmp;
			
			if(!isset($this->_selectNodes[$this->_selectHash]))
			{
				$this->_selectNodes[$this->_selectHash] = new QZProductNode($product, $this);				
				if($this->_options['user_id'])
				{
					$this->_selectNodes[$this->_selectHash]->set('user_id', $this->_options['user_id']);
				}
				else
				{
					$user = JFactory::getUser();
					$this->_selectNodes[$this->_selectHash]->set('user_id', $user->get('id'));
				}	
				$this->_selectNodes[$this->_selectHash]->setPrices();			
			}
		}
		else
		{
			$this->_selectNodes[$this->_selectHash] = null;
		}
	}	


}


if(!class_exists('QZObject'))
{
	require(JPATH_ADMINISTRATOR . '/components/com_qazap/helpers/object.php');
}

/**
 * Helper class to load Product Information
 *
 * @package     Qazap.helper
 * @since       1.0.0
 */
class QZProductNode extends QZObject
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  1.0.0
	 */
	public $product_id = null;
	public $product_name = null;
	public $category_name = null;
	public $ordering = null;
	public $state = null;
	public $block = null;
	public $parent_id = null;
	public $product_sku = null;
	public $rating = null;
	public $review_count = null;
	public $featured = null;
	public $vendor = null;
	public $vendor_admin = null;
	public $vendor_group_id = null;
	public $shop_name = null;	
	public $manufacturer_name = null;
	public $manufacturer_email = null;
	public $manufacturer_category = null;
	public $manufacturer_description = null;
	public $manufacturer_url = null;
	public $manufacturer_images = null;	
	public $urls = null;
	public $manufacturer_id = null;
	public $category_id = null;
	public $access = null;
	public $category_access = null;
	public $product_baseprice = null;
	public $product_customprice = null;
	public $multiple_pricing = null;
	public $dbt_rule_id = null;
	public $dat_rule_id = null;
	public $tax_rule_id = null;
	public $custom_fields = NULL;	
	public $attributes = NULL;	
	public $in_stock = null;
	public $ordered = null;
	public $booked_order = null;
	public $product_length = null;
	public $product_length_uom = null;
	public $product_width = null;
	public $product_height = null;
	public $product_weight = null;
	public $product_weight_uom = null;
	public $product_packaging = null;
	public $product_packaging_uom = null;
	public $units_in_box = null;
	public $images = null;
	public $related_categories = null;
	public $related_products = null;
	public $membership = null;
	public $params = null;
	public $checked_out = null;
	public $checked_out_time = null;
	public $created_by = null;
	public $created_time = null;
	public $modified_by = null;
	public $modified_time = null;
	public $hits = null;
	public $product_alias = null;
	public $short_description = null;
	public $product_description = null;
	public $metakey = null;
	public $metadesc = null;
	public $metadata = null;
  public $category_params = NULL; 
  public $product_quantity_prices = NULL;  
	public $user_id = null;
  
  /**
	* Selected attributes for cart and order processing
	* 
	* @var		array/null
	* @since	1.0.0
	*/
  public $product_attributes = null;
  
  /**
	* Selected membership for cart and order processing
	* 
	* @var		array/null
	* @since	1.0.0
	*/	
	public $product_membership = null;
	/**
	 * Prices of the product
	 *
	 * @var			array	Store an array of various pricings else null.
	 * @since		1.0.0
	 */
	public $prices = null;	

	/**
	 * Slug fo the category (used in URL)
	 *
	 * @var    string
	 * @since  1.0.0.0
	 */
	public $slug = null;


	/**
	 * Siblings of the product
	 *
	 * @var    integer
	 * @since  1.0.0.0
	 */
	protected $_siblings = null;

	/**
	 * Constructor of this tree
	 *
	 * @var
	 * @since  1.0.0.0
	 */
	protected $_constructor = null;
	
	protected $_relatedProducts = null;
	
	protected $_relatedCategories = null;
	
	protected $_memberships = null;
	
	protected $_attributes = null;
	
	protected $_customfields = null;
	
	/**
	 * Class constructor
	 *
	 * @param   array          $product     The product data.
	 * @param   JCategoryNode  $constructor  The tree constructor.
	 *
	 * @since   1.0.0.0
	 */
	public function __construct($product = null, $constructor = null)
	{
		if ($product)
		{
			$this->setProperties($product);
			if ($constructor)
			{
				$this->_constructor = $constructor;
			}
			return true;
		}

		return false;
	}

	/**
	 * Get the children of this node
	 * 
	 * @param   array		$options
	 * @param		array		$filters
	 *
	 * @return  array  The children
	 *
	 * @since   1.0.0
	 */
	public function getChildren($options = array(), $filters = array())
	{
		$options['countresult'] = false;
		$products = QZProducts::getInstance($options, $filters);
		return $products->getList(0, 0, $this->product_id);
	}
	
	/**
	 * Get the parent of this node
	 *
	 * @param   array		$options
	 * @param		array		$filters
	 *
	 * @return  object  The parent
	 *
	 * @since   1.0.0
	 */
	public function getParent($options = array(), $filters = array())
	{
		if(empty($this->parent_id))
		{
			return null;
		}
		
		$options['countresult'] = false;
		$products = QZProducts::getInstance($options, $filters);
		
		return $products->get($this->parent_id);
	}	

	/**
	 * Returns the right or left sibling of a category
	 *
	 * @param   boolean  $right  If set to false, returns the left sibling
	 *
	 * @return  mixed  QZProductsNode object with the sibling information or
	 *                 NULL if there is no sibling on that side.
	 *
	 * @since          1.0.0
	 */	
	public function getSiblings()
	{
		if(!$this->_siblings)
		{
			$this->_siblings = $this->_constructor->getSiblings($this);
		}		
		
		return $this->_siblings;
	}
	
	/**
	 * Returns the user that created the category
	 *
	 * @param   boolean  $modified_user  Returns the modified_user when set to true
	 *
	 * @return  JUser  A JUser object containing a userid
	 *
	 * @since   1.0.0.0
	 */
	public function getAuthor($modified_user = false)
	{
		if ($modified_user)
		{
			return JFactory::getUser($this->modified_user_id);
		}

		return JFactory::getUser($this->created_user_id);
	}
	
	/**
	 * Returns the all different types of prices after calculations
	 *
	 * @return  object  QZPrices object containing all prices of the product
	 * @since   1.0.0
	 */
	public function setPrices()
	{
		if (!$this->prices)
		{
			$prices = new QZPrices($this);
			$this->prices = $prices->get();	
		}
	}	
	
	public function getPrices()
	{
		if (!$this->prices)
		{
			$this->setPrices();	
		}
		return $this->prices;		
	}
	
	public function getParams()
	{
		if (!($this->params instanceof JRegistry))
		{
			$temp = new JRegistry;
			$temp->loadString($this->params);
			$this->params = $temp;
		}

		return $this->params;		
	}
	
	public function getMetadata()
	{
		if (!($this->metadata instanceof JRegistry))
		{
			$temp = new JRegistry;
			$temp->loadString($this->metadata);
			$this->metadata = $temp;
		}

		return $this->metadata;		
	}	
	
	public function &getRelatedProducts()
	{
		if($this->related_products && is_string($this->related_products))
		{
			$this->related_products = json_decode($this->related_products, true);
		}
		
		if(empty($this->related_products))
		{
			$this->_relatedProducts = false;
			return $this->_relatedProducts;
		}

		if($this->_relatedProducts === null)
		{
			$options = array();
			$options['countresult'] = false;
			$options['custom_fields'] = false;
			$options['attributes'] = false;
			$filters = array();		
			$helper = QZProducts::getInstance($options, $filters);
			$relatedProducts = $helper->getList(0, $this->related_products);
			$this->_relatedProducts = QZHelper::sortArrayByArray($relatedProducts, $this->related_products);
		}
		
		return $this->_relatedProducts;
	}
	
	public function &getMemberships()
	{
		if(is_string($this->membership) && $this->membership)
		{
			$this->membership = json_decode($this->membership, true);
		}
		
		if(empty($this->membership))
		{
			$this->_memberships = false;
			return $this->_memberships;
		}
		
		if($this->_memberships === null)
		{
			$memberships = $this->_constructor->getMemberships($this->membership);
			
			if(count($memberships))
			{
				$this->_memberships = new stdClass;
				$this->_memberships->data = $memberships;
				$this->_memberships->field_name = 'qzform[membership]';
				$this->_memberships->field_id = 'qzform_membership';
				$layout = new JLayoutFile('qazap.memberships.memberships'); 
				$this->_memberships->display = $layout->render($this->_memberships);
				// Clear unnecessary memory
				unset($memberships);				
			}
		}		
		return $this->_memberships;
	}
	
	
	public function &getAttributes()
	{
		if($this->_attributes === null)
		{
			$dispatcher	= JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapcartattributes');
			
			if($this->attributes)
			{
				foreach($this->attributes as $data) 
				{
					$data->field_name = 'qzform[attributes]['.$data->id.']';
					$data->field_id = 'qzform_attributes_'.$data->id;
					$cartattributes = $dispatcher->trigger('onDisplayProduct', array(&$data));
					if(empty($data->compare_display))
					{
						$data->compare_display = $data->display;
					}					
					$this->_attributes[] = $data;						
				}				
			}						
			else
			{
				$this->_attributes = false;
			}
		}
		return $this->_attributes;
	}
	
	
	public function &getCustomfields()
	{
		if($this->_customfields === null)
		{
			$dispatcher	= JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapcustomfields');
			
			if($this->custom_fields)
			{
				foreach($this->custom_fields as $data) 
				{
					if(!$data->layout_position === null || empty($data->layout_position))
					{
						$data->layout_position = 'standard';
					}
					if(!isset($this->_customfields[$data->layout_position]))
					{
						$this->_customfields[$data->layout_position] = array();
					}
					$data->field_name = 'qzform[customfields]['.$data->id.']';
					$data->field_id = 'qzform_customfields_'.$data->id;
					$cartattributes = $dispatcher->trigger('onDisplayProduct', array(&$data));
					if(empty($data->compare_display))
					{
						$data->compare_display = $data->display;
					}
					$this->_customfields[$data->layout_position][] = $data;						
				}				
			}						
			else
			{
				$this->_customfields = false;
			}
		}
		return $this->_customfields;
	}	

}
