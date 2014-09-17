<?php
/**
 * cartattributestypes.php
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
class QazapModelCartattributestypes extends JModelList 
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
				'type', 'a.type',
				'title', 'a.title',
				'show_title', 'a.show_title',
				'published', 'a.published',
				'description', 'a.description',
				'tooltip', 'a.tooltip',
				'layout_position', 'a.layout_position',
				'hidden', 'a.hidden',
				'params', 'a.params',
				'name','b.name',
				'check_stock','a.check_stock'
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

		//Load Filter Check Stock//
		$check_stock = $app->getUserStateFromRequest($this->context . '.filter.check_stock', 'filter_check_stock', '', 'string');
		$this->setState('filter.check_stock', $check_stock);

		//Filtering type
		$this->setState('filter.type', $app->getUserStateFromRequest($this->context.'.filter.type', 'filter_type', '', 'string'));


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
						'list.select','a.id,a.title,a.check_stock,a.hidden,a.checked_out,a.checked_out_time,a.state,a.ordering,b.name,c.name AS editor'
						)
		);
		$query->from('`#__qazap_cartattributestype` AS a');
		$query->join('LEFT','`#__extensions` AS b ON a.type = b.extension_id');
		$query->join('LEFT','`#__users` AS c ON a.checked_out = c.id');
		$query->group('a.id,a.title,a.check_stock,a.hidden,a.state,a.ordering');



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

		//Filter By check Stock//
		$stock_check = $this->getState('filter.check_stock');
		if (is_numeric($stock_check)) 
		{
		    $query->where('a.check_stock = '.(int) $stock_check);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) 
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('( a.title LIKE '.$search.'  OR  b.name LIKE '.$search.'  OR  a.id LIKE '.$search.' )');
		}

		//Filtering type
		$filter_type = $this->state->get("filter.type");
		if ($filter_type) 
		{
			$query->where("a.type = '".$db->escape($filter_type)."'");
		}


		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol && $orderDirn) 
		{
		    $query->order($db->escape($orderCol . ' ' . $orderDirn));
		}
		return $query;
	}

	public function getItems() 
	{
		$items = parent::getItems();
		return $items;
	}
}
