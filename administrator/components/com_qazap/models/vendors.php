<?php
/**
 * vendors.php
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
class QazapModelVendors extends JModelList 
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
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'created_by.created_by',
				'vendor_admin', 'u.name',
				'shop_name', 'a.shop_name',
				'vendor_group','g.title'
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

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_qazap');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
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

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));		
		$query->from('#__qazap_vendor AS a');

		// Join over the user field 'created_by'
		$query->select('auth.name AS author');
		$query->join('LEFT', '#__users AS auth ON auth.id = a.created_by');
		
		// Join over the user field 'checked_out'
		$query->select('editor.name AS editor');
		$query->join('LEFT','`#__users` AS editor ON editor.id = a.checked_out');

		// Join over Vendor Admin
		$query->select('u.name AS vendor_admin_name, u.username, u.email AS juser_email');
		$query->leftjoin('#__users AS u ON u.id = a.vendor_admin');

		// Join over Vendor Group
		$query->select('g.title AS vendor_group_name');
		$query->join('LEFT', '#__qazap_vendor_groups AS g ON a.vendor_group_id = g.vendor_group_id');
		
		// Join over for product count
		$subQuery = ' (SELECT COUNT(p.product_id) from #__qazap_products AS p WHERE p.vendor = a.id) ';
		$query->select($subQuery . ' AS product_count');
		//$query->join('LEFT', '#__qazap_products AS p ON p.vendor = a.id');		

		// Filter by published state
		$approved = $this->getState('filter.state');
		
		if (is_numeric($approved)) 
		{
			$query->where('a.state = ' . (int) $approved);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) 
		{
			if (stripos($search, 'vendor_id:') === 0) 
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			} 
			else 
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
			}
		}

		// Add the list ordering clause.
		$listOrder	= $this->getState('list.ordering');
		$listDirn	= $this->getState('list.direction');
		
		if(!empty($listOrder) && !empty($listDirn))
		{
			$query->order($db->escape($listOrder . ' ' . $listDirn));
		}
		
		return $query;
	}

	public function getItems() 
	{
		$items = parent::getItems();        
		return $items;
	}

}
