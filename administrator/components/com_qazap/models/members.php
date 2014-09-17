<?php
/**
 * members.php
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
class QazapModelMembers extends JModelList 
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
			'membership_id', 'a.membership_id',
			'from_date', 'a.from_date',
			'to_date', 'a.to_date',
			'user_name','b.username',
			'plan_name','c.plan_name'
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

	$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
	$this->setState('filter.state', $published);

	$valid_till = $app->getUserStateFromRequest($this->context . '.filter.valid_till', 'filter_valid_till', '', 'string');
	$this->setState('filter.valid_till', $valid_till);

	// Filter By Status 
	$this->setState('filter.member_id', $app->getUserStateFromRequest($this->context.'.filter.member_id', 'filter_member_id', '', 'string'));

	// Filter By Plan Name 
	$this->setState('filter.plan_name', $app->getUserStateFromRequest($this->context.'.filter.plan_name', 'filter_plan_name', '', 'string'));

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
	// Create a new query object.
	$db = $this->getDbo();
	$query = $db->getQuery(true);

	// Select the required fields from the table.
	$query->select(
		$this->getState(
				'list.select', 'a.id,a.ordering,a.status,a.checked_out,a.checked_out_time,a.from_date,a.to_date,a.created_time,b.username,b.email,c.plan_name,d.name AS editor'
		)
	);
	$query->from('`#__qazap_members` AS a');
	$query->join('INNER', '#__users AS b ON (a.user_id = b.id)');
	$query->join('INNER', '#__qazap_memberships AS c ON (a.membership_id = c.id)');
	$query->join('LEFT', '#__users AS d ON (a.checked_out = d.id)'); 
	$query->group('a.id,a.ordering,a.status,a.checked_out,a.checked_out_time,a.from_date,a.to_date,a.created_time');             
	// Filter by published state
	$published = $this->getState('filter.state');
	if(is_numeric($published))
	{
		$query->where('a.status = ' . $published);
	}

	// Filter by search in title
	$search = $this->getState('filter.search');
	if (!empty($search)) 
	{
	   // Escape the search token.
		$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
		// Add the clauses to the query.
		$query->where('b.username LIKE ' . $token . ' OR c.plan_name LIKE ' . $token . ' OR a.id = ' . $token);
	}

	// Filter By Date//
	$valid_till = $this->state->get("filter.valid_till");
	if ($valid_till != '') 
	{
			$query->where("a.to_date <= '".$db->escape($valid_till)."'");
	}

	// Filter By Plan Name
	$filter_plan_name = $this->state->get("filter.plan_name");
	if (is_numeric($filter_plan_name)) 
	{
			$query->where("a.membership_id = '".$db->escape($filter_plan_name)."'");
	}

	// Add the list ordering clause.

	return $query;
	}

	public function getItems() 
	{
	$items = parent::getItems();
	return $items;
	}

	public function getPlans()
	{
	$options = array();
	$db = JFactory::getDBO();
	$sql = $db->getQuery(true)
				->select(array('a.plan_name AS text','a.id AS value'))
				->from('#__qazap_memberships as a');
	$db->setQuery($sql);
	$memberShips = $db->loadObjectList();
	return $memberShips;
	}
}
