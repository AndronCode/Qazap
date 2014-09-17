<?php
/**
 * taxes.php
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
class QazapModelTaxes extends JModelList 
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
				'calculation_rule_name', 'a.calculation_rule_name',
				'description', 'a.description',
				'type_of_arithmatic_operation', 'a.type_of_arithmatic_operation',
				'math_operation', 'a.math_operation',
				'value', 'a.value',
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

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_publishing', '', 'string');
		$this->setState('filter.state', $published);
		
		$operation = $app->getUserStateFromRequest($this->context . '.filter.operation', 'filter_operation', '', 'string');
		$this->setState('filter.operation', $operation);	
		
		$calculation = $app->getUserStateFromRequest($this->context . '.filter.calculation', 'filter_calculation', '', 'string');
		$this->setState('filter.calculation', $calculation);				

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
							'list.select', 'a.*'
						        )
						);
		
		$query->from('`#__qazap_taxes` AS a');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');			
		$query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');		
	    
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
		
		
		$calculation = $this->setState('filter.calculation', '');
		$calculation = $calculation ? str_replace('*', '', $calculation) : $calculation;
		if(trim($calculation))
		{
			$query->where('a.math_operation = ' . $db->quote(trim($calculation)));
		}
		
		$operation = $this->setState('filter.operation', '');
		if(is_numeric($operation))
		{
			$query->where('a.type_of_arithmatic_operation = ' . (int) $operation);
		}		
		
		// Filter by search in title
		$search = $this->getState('filter.search');

		// Filter the items over the search string if set.
		if ($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
			// Add the clauses to the query.
			$query->where('a.calculation_rule_name LIKE ' . $token . ' OR a.value LIKE ' . $token);
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
