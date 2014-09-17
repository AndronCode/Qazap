<?php
/**
 * userinfos.php
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
class QazapModelUserinfos extends JModelList 
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
				'user_id', 'a.user_id',
				'address_type', 'a.address_type',
				'vendor_id','v.id',
				'vendor_block','v.state',
				'user_name','u.name',
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

		// Set vendor state as filter // 
		$vendor_block = $app->getUserStateFromRequest($this->context . '.filter.vendor_block', 'filter.vendor_block', '', 'string');
		$this->setState('filter.vendor_block', $vendor_block);

		// Load the filter addresstype.
		$address_type = $app->getUserStateFromRequest($this->context . '.filter.address_type', 'filter_address_type', '', 'string');
		$this->setState('filter.address_type', $address_type);       

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
		$id.= ':' . $this->getState('filter.vendor_block');
		$id.= ':' . $this->getState('filter.address_type');

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
		$query->select(
			$this->getState('list.select', 'a.id, a.ordering, a.state, a.checked_out, a.checked_out_time, a.address_type, v.id AS vendor_id, u.name AS user_name, v.state AS vendor_block, b.name AS editor')
		);

		$query->from('`#__qazap_userinfos` AS a');
		$query->leftJoin('`#__users` AS u ON u.id = a.user_id');
		$query->leftJoin('`#__qazap_vendor` AS v ON v.vendor_admin = a.user_id');
		$query->leftJoin('`#__users` AS b ON b.id = a.checked_out');        

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

		// Filter by vendor_state
		$filter_vendor_block = $this->getState('filter.vendor_block');

		if (is_numeric($filter_vendor_block)) 
		{
			$query->where('v.state = '.(int) $filter_vendor_block);
		}

		// Filter by address type
		$filter_address_type = $this->state->get("filter.address_type");
		if ($filter_address_type == 'bt') 
		{
			$query->where("a.address_type = '".$db->escape($filter_address_type)."'");
		}
		elseif($filter_address_type == 'st')
		{
			$query->where("a.address_type = '".$db->escape($filter_address_type)."'");
		}
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) 
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
			// Add the clauses to the query.
			$query->where('u.name LIKE ' . $token. ' OR a.id LIKE '. $token);
		}

		// Add the list ordering clause.
		$fullOrdering = $this->state->get('list.fullordering');		
		if ($fullOrdering) 
		{
			$query->order($db->escape($fullOrdering));
		}

		return $query;
	}

	public function getItems() 
	{
		$items = parent::getItems();
		return $items;
	}
}
