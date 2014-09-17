<?php
/**
 * shops.php
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

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelShops extends JModelList 
{
	protected $_statesByCountry = array();
	protected $_shopDetails = array();
	protected $_columns = null;

	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JModelList
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		if (empty($config['filter_fields'])) 
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
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
		$app = JFactory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		
		// Load the parameters.
		$params = $app->getParams();
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
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		if(!$columns = $this->getColumns($alias = 'a'))
		{
			$this->setError($this->getError());
			return false;
		}
		// Select the required fields from the table.
		$query->select(
		  				$this->getState('list.select', $columns)
						);
		
		$query->from('`#__qazap_vendor` AS a');
		$query->select('b.name AS editor')
				->join('LEFT', '#__users As b ON b.id = a.checked_out');
		
		$query->select('c.name AS vendor_admin')
				->join('LEFT', '#__users As c ON c.id = a.vendor_admin');
				
		$query->select('d.country_name')
				->join('LEFT', '#__qazap_countries AS d ON d.id = a.country');
		
		$query->select('(SELECT COUNT(e.product_id) FROM #__qazap_products AS e WHERE e.vendor = a.id AND e.state = 1 AND e.block = 0) AS product_count');
		
		$subQuery = ' (SELECT AVG(g.rating) AS average_rating, COUNT(g.rating) AS rating_count, f.vendor, f.product_id FROM #__qazap_products AS f JOIN #__qazap_reviews AS g ON g.product_id = f.product_id WHERE f.state = 1 AND f.block = 0 AND g.state = 1 GROUP BY f.vendor, f.product_id) ';
		
		$query->select('rating.average_rating, rating.rating_count')
			->join('LEFT', $subQuery . 'AS rating ON rating.vendor = a.id');
		
		$query->group($columns);
				

		// Filter by published state
		$active = $this->getState('filter.state');
		
		if (is_numeric($active))
		{
			$query->where('a.state = ' . (int) $active);
		}
		else
		{
			$query->where('a.state = 1');
		}
		
		// Filter by search in title
		$search = $this->getState('filter.search');
		
		// Add the list ordering clause.
		$listOrder	= $this->getState('list.ordering');
		$listDirn	= $this->getState('list.direction');
		if($listOrder && $listDirn)
		{
			$query->order($db->escape($listOrder . ' ' .$listDirn));
		}

		return $query;
	}

	public function getItems() 
	{
		if(!$items = parent::getItems())
		{
			$this->setError($this->getError());
			return false;
		}
		
		if(!empty($items))
		{
			foreach($items as &$item)
			{
				$item->category_list = (!empty($item->category_list) && is_string($item->category_list)) ? json_decode($item->category_list, true) : 0;
				$item->shipment_methods = (!empty($item->shipment_methods) && is_string($item->shipment_methods)) ? json_decode($item->shipment_methods, true) : 0;
				$item->image = (!empty($item->image) && is_string($item->image)) ? json_decode($item->image, true) : array();				
			}
			
		}
		
		return $items;
	}
	
	public function getShopDetails()
	{
		$lang = JFactory::getLanguage();
		$language = !empty($language) ? $language : $lang->getTag();
		$default_language = $lang->getDefault();
		
		if (!isset($this->_shopDetails[$language]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select('a.shop_id, a.lang, a.name, a.company, a.contact_person, a.address_1, ' .
									   'a.address_2, a.city, a.state, a.country, a.zip, a.phone_1, a.phone_2, ' .
									   'a.fax, a.mobile, a.vat, a.additional_info, a.tos, a.description, a.modified_time, ' .
									   'a.modified_by')
						->from('#__qazap_shop AS a')
						->select('b.country_name')
						->join('LEFT', '#__qazap_countries AS b ON b.id = a.country')		
						->select('c.state_name')
						->join('LEFT', '#__qazap_states AS c ON c.id = a.state')						
						->where('a.lang IN (' . $db->quote($language). ','. $db->quote('*'). ','.  $db->quote($default_language) . ')')
						->group('a.shop_id, a.lang, a.name, a.company, a.contact_person, a.address_1, ' .
									  'a.address_2, a.city, a.state, a.country, a.zip, a.phone_1, a.phone_2, ' .
									  'a.fax, a.mobile, a.vat, a.additional_info, a.tos, a.description, a.modified_time, ' .
									  'a.modified_by');				
			
			try
			{
				$db->setQuery($query);
				$datas = $db->loadObjectList('lang');			
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
				
			if (empty($datas))
			{
				return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_SHOP_NOT_FOUND'));
			}
			
			if(isset($datas[$language]))
			{
				$shop = $datas[$language];
			}
			elseif(isset($datas['*']))
			{
				$shop = $datas['*'];
			}
			else
			{
				$shop = $datas[$default_language];
			}						
			
			$this->_shopDetails[$language] = $shop;
		}
		
		return $this->_shopDetails[$language];
	}
	
	public function getColumns($alias = null)
	{
		if($this->_columns === null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				 					->select('field_title')
				 					->from('`#__qazap_vendorfields`')
				 					->where('state = 1')
				 					->where('field_type != ' . $db->quote('fieldset'))
									->order('ordering ASC');
			
			try
			{
				$db->setQuery($query);
				$results = $db->loadColumn();	
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$columns = array('id', 'vendor_admin', 'vendor_group_id', 'shop_name', 'category_list', 'shipment_methods', 'created_time');
			
			if(!empty($results))
			{
				$columns = array_merge($columns, $results);
			}
			
			if(!empty($alias))
			{
				$count = count($columns);
				
				for ($i = 0; $i < $count; $i++)
				{
					$columns[$i] = $alias . '.' . $columns[$i];
				}
			}
			return $columns;
		}
	}
}
