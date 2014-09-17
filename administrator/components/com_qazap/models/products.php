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

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelProducts extends JModelList 
{	
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JController
	* @since    1.0.0
	*/
  public function __construct($config = array()) 
  {
	if (empty($config['filter_fields'])) 
	{
		$config['filter_fields'] = array(
			'product_id', 'a.product_id',
			'ordering', 'a.ordering',
			'state', 'a.state',
			'block', 'a.block',
			'checked_out','a.checked_out',
			'product_name', 'b.product_name',
			'product_sku', 'a.product_sku',
			'featured', 'a.featured',
			'url', 'a.url',
			'manufacturer_id', 'a.manufacturer_id',
			'category_id', 'a.category_id',
			'product_baseprice', 'a.product_baseprice',
			'access', 'a.access',
			'manufacturer','m.manufacturer_name',
			'product_categories','d.category_name',
			'access_name','j.title',
			'parent_product_name', 'p.product_name',
			'vendor_admin', 'v.vendor_admin', 
			'vendor_group_id', 'v.vendor_group_id', 
			'shop_name', 'v.shop_name',
			'availability', '(a.in_stock - a.booked_order)'
		);
	}

		parent::__construct($config);			
  }

  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   */
  protected function populateState($ordering = null, $direction = null) 
  {
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$search_field = $app->getUserStateFromRequest($this->context . '.filter.search_field', 'filter_search');
		$this->setState('filter.search_field', $search_field);		

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$block = $app->getUserStateFromRequest($this->context . '.filter.block', 'filter_block', '', 'string');
		$this->setState('filter.block', $block);

		$stock = $app->getUserStateFromRequest($this->context . '.filter.stock', 'filter_stock', '', 'string');
		$this->setState('filter.stock', $stock);

		$category_id = $app->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'string');
		$this->setState('filter.category_id', $category_id);
		
		$vendor_id = $app->input->getInt('vendor_id', 0);
		$this->setState('filter.vendor_id', $vendor_id);    

		// Load the parameters.
		$params = JComponentHelper::getParams('com_qazap');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.ordering', 'asc');
  }

  /**
   * Method to get a store id based on model configuration state.
   *
   * This is necessary because the model is used by the component and
   * different modules that might need different sets of data or different
   * ordering requirements.
   *
   * @param	string		$id	A prefix for the store id.
   * @return	string		A store id.
   * @since	1.0.0
   */
	protected function getStoreId($id = '') 
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	* Build an SQL query to load the list data.
	*
	* @return	JDatabaseQuery
	* @since	1.0.0
	*/
	protected function getListQuery() 
	{			
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_langauge = $lang->getTag();
		$default_language = $lang->getDefault();
		$config = $this->getState('params');
		$user = JFactory::getUser();
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Select the required fields from the table.
		$query->select(	
				$this->getState(
					'list.select',
						array(
							'a.product_id', 
							'a.category_id',
							'a.ordering', 
							'a.state', 
							'a.block', 
							'a.product_sku', 
							'a.featured', 
							'a.multiple_pricing',
							'a.dbt_rule_id', 
							'a.dat_rule_id', 
							'a.tax_rule_id',
							'a.checked_out', 
							'a.checked_out_time',
							'CASE WHEN (a.in_stock - a.booked_order) > 0 THEN 1 ELSE 0 END AS availability', 
							'a.hits',
							'COUNT(child.product_id) AS children_count',								
							'm.manufacturer_name',
							'j.title AS access_name',
						)
				)
			);
		$query->from('`#__qazap_products` AS a');
		
		// If multiple product pricing enabled
		$multple_pricing = $config->get('multiple_product_pricing', 0);
		
		if($multple_pricing)
		{
			$case1  = '(CASE a.multiple_pricing ';
			$case1 .= 'WHEN 0 THEN a.product_baseprice ';		
			$case1 .= 'WHEN 1 THEN up.product_baseprice ';
			$case1 .= 'WHEN 2 THEN qp.product_baseprice ';	
			$case1 .= 'END) AS product_baseprice';
			$query->select($case1);
			
			$case2  = '(CASE a.multiple_pricing ';
			$case2 .= 'WHEN 0 THEN a.product_customprice ';		
			$case2 .= 'WHEN 1 THEN up.product_customprice ';
			$case2 .= 'WHEN 2 THEN qp.product_customprice ';	
			$case2 .= 'END) AS product_customprice';
			$query->select($case2);					
			
			// Join Usergroup based pricing. Show Public usergroup pricing
			$query->join('LEFT', '#__qazap_product_user_price AS up ON up.product_id = a.product_id AND up.usergroup_id = 1');
			
			// Join quantity based pricing
			$quantity = $config->get('minimum_purchase_quantity', 1);
			
			$query->join('LEFT', '#__qazap_product_quantity_price AS qp ON qp.product_id = a.product_id AND qp.max_quantity >= '.$quantity.' AND qp.min_quantity <= ' . (int) $quantity);
		}	
		else 
		{
			$query->select('a.product_baseprice, a.product_customprice');
		}		
		
		$query->join('LEFT', '#__qazap_products AS child ON child.parent_id = a.product_id AND child.parent_id != 0');
		
		// Join Product Details Table
		if($multiple_language)
		{
			$query->select('IF(b.product_name IS NULL, bd.product_name, b.product_name) AS product_name');
			$query->join('LEFT', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = '.$db->quote($present_langauge));
			$query->join('LEFT', '#__qazap_product_details AS bd ON bd.product_id = a.product_id AND bd.language = '.$db->quote($default_language));				
		}
		else
		{
			$query->select('b.product_name');
			$query->join('LEFT', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = '.$db->quote($present_langauge));
		}
		
		// Join vendor table
		$query->select('v.vendor_admin, v.vendor_group_id, v.shop_name');
		$query->join('LEFT', '#__qazap_vendor AS v ON v.id = a.vendor');

		// Join Manufacturer Table
		$query->join('LEFT', '#__qazap_manufacturers AS m ON m.id = a.manufacturer_id');
		
		// Join Product Category Details Tabels
		if($multiple_language)
		{
			$query->select('IF(d.title IS NULL, dd.title, d.title) AS category_name');
			$query->join('LEFT', '#__qazap_category_details AS d ON d.category_id = a.category_id AND d.language = '.$db->quote($present_langauge));
			$query->join('LEFT', '#__qazap_category_details AS dd ON dd.category_id = a.category_id AND dd.language = '.$db->quote($default_language));
		}
		else
		{
			$query->select('d.title AS category_name');
			$query->join('LEFT', '#__qazap_category_details AS d ON d.category_id = a.category_id AND d.language = '.$db->quote($present_langauge));
		}			
		
		$query->select('COUNT(notify.id) AS notify_count');
		$query->join('LEFT', '#__qazap_notify_product AS notify ON notify.product_id = a.product_id');
		
		// Join Joomla View Level Table
		$query->join('LEFT', '#__viewlevels AS j ON j.id = a.access');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');			
		$query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published)) 
		{
			$query->where('a.state = '.(int) $published);
		} 
		else if ($published === '') 
		{
			$query->where('(a.state IN (0, 1))');
		}
		
		$vendor_id = $this->getState('filter.vendor_id');
		
		if($vendor_id > 0)
		{
			$query->where('a.vendor = ' . (int) $vendor_id);
		}
		
		//Filter by block status
		$block = $this->getState('filter.block');

		if (is_numeric($block)) 
		{
			$query->where('a.block = '.(int) $block);
		}
		
		// Filter by category
		$category_id = $this->getState('filter.category_id');
		
		if (is_numeric($category_id)) 
		{
			$query->where('a.category_id = ' . (int) $category_id);
		}				
		
		//Filter by instock status
		$stock = $this->getState('filter.stock');

		if (is_numeric($stock) && $stock == '1') 
		{
			$query->where('(a.in_stock - a.booked_order) > 0');
		}
		else if(is_numeric($stock) && $stock == '0')
		{
			//print($stock);exit;
			$query->where('(a.in_stock - a.booked_order) <= 0');
		}
		 
		//Filter by Search Field
		$search = $this->getState('filter.search');
		if($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'b.product_name LIKE ' . $token;
			$searches[] = 'a.product_sku LIKE ' . $token;
			$searches[] = 'a.product_id LIKE ' . $token;
			$searches[] = 'm.manufacturer_name LIKE ' . $token;
			$searches[] = 'd.title LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}
		
		$query->where('a.parent_id = 0');
		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol && $orderDirn) 
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}
					
		$query->group('a.product_id');
		
		return $query;
	}
	/**
	* Load the list data.
	*
	* @see	JModelList
	* @since	1.0.0
	*/
	public function getItems() 
	{
		$items = parent::getItems();        
		return $items;
	}
	
	/**
	* Method to get children products of a parent product
	* 
	* @param		int $parent_id Parent product_id	* 
	* 
	* @return		object list of child products
	* @since		1.0
	*/		
	public function getChildren($parent_id = 0)
	{	
		$lang = JFactory::getLanguage();
		$multiple_language = count(JLanguageHelper::getLanguages()) > 1 ? true : false;
		$present_langauge = $lang->getTag();
		$default_language = $lang->getDefault();
			
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.product_id')
					->from('`#__qazap_products` AS a');
					
		if($multiple_language)
		{
			$query->select('IF(b.product_name IS NULL, bd.product_name, b.product_name) AS product_name');
			$query->join('LEFT', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = '.$db->Quote($present_langauge));
			$query->join('LEFT', '#__qazap_product_details AS bd ON bd.product_id = a.product_id AND bd.language = '.$db->Quote($default_language));				
		}
		else
		{
			$query->select('b.product_name');
			$query->join('LEFT', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = '.$db->Quote($default_language));
		}
		
		// Filter by published state
		$published = $this->getState('filter.state');
		
		if (is_numeric($published)) 
		{
			$query->where('a.state = '.(int) $published);
		} 
		elseif ($published === '') 
		{
      		$query->where('(a.state IN (0, 1))');
  		}
  	
	  	$query->where('a.parent_id = '.$parent_id);
	  	
	  	$query->group('a.product_id');
	  	
	  	$query->order('a.ordering ASC');
	  	
	  	$db->setQuery($query);
  	
	  	try 
		{
			$children = $db->loadObjectList();
		} 
		catch (Exception $e) 
		{
			JError::raiseWarning(500, $e->getMessage());
		}
			
			return $children;	
	}		

}
