<?php
/**
 * vendorfields.php
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
class QazapModelVendorfields extends JModelList 
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
				'field_name', 'a.field_name',
				'max_length', 'a.max_length',
				'field_title', 'a.field_title',
				'description', 'a.description',
				'field_type', 'a.field_type',
				'required', 'a.required',
				'show_in_vendorbilling_form', 'a.show_in_vendorbilling_form',
				'show_in_userbilling_form', 'a.show_in_userbilling_form',
				'show_in_shipment_form', 'a.show_in_shipment_form',
				'read_only', 'a.read_only',
				'published', 'a.published',
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


		//Filtering required
		$this->setState('filter.required', $app->getUserStateFromRequest($this->context.'.filter.required', 'filter_required', '', 'string'));

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
				'list.select', 'a.id, a.state, a.ordering, a.checked_out, a.checked_out_time, a.field_name, a.field_title, a.field_type, a.required, b.name AS editor'
			)
		);
		$query->from('`#__qazap_vendorfields` AS a');
		$query->leftjoin('`#__users` AS b ON a.checked_out = b.id');




		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('a.state = '.(int) $published);
		}
    

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) 
		{
			if (stripos($search, 'id:') === 0) 
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			} 
			else 
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.required LIKE '.$search.'  OR  a.show_in_vendorbilling_form LIKE '.$search.'  OR  a.show_in_userbilling_form LIKE '.$search.'  OR  a.show_in_shipment_form LIKE '.$search.' )');
			}
		}

        

		//Filtering required
		$filter_required = $this->state->get("filter.required");
		if (is_numeric($filter_required)) 
		{
			$query->where("a.required = '".$db->escape($filter_required)."'");
		}
       
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
		$items = parent::getItems();
      
		return $items;
	}
}
