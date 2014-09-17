<?php
/**
 * states.php
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
class QazapModelStates extends JModelList 
{
	protected $_statesByCountry = array();

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
				'country_name', 'b.country_name',
				'state_name', 'a.state_name',
				'state_2_code', 'a.state_2_code',
				'state_3_code', 'a.state_3_code',
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

		$country_id = $app->input->getInt('country_id', 0);
		$this->setState('country_id', $country_id);
		
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
		$id.= ':' . $this->getState('country_id');

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
		          'list.select', array('a.*','b.country_name, c.name AS editor')
		  )
		);
		$query->from('`#__qazap_states` AS a');
		$query->join('LEFT','`#__qazap_countries` AS b ON b.id=a.country_id');
		$query->join('LEFT','`#__users` AS c ON c.id=a.checked_out');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published))
		{
			$query->where('a.state = '.(int) $published);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		// Filter the items over the search string if set.
		if ($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');

			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'a.state_name LIKE ' . $token;
			$searches[] = 'b.country_name LIKE ' . $token;
			$searches[] = 'a.state_3_code LIKE ' . $token;
			$searches[] = 'a.state_2_code LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
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
		return parent::getItems();
	}
    
	public function getItemsByCountry($country_id = null)
	{
		$country_id = $country_id ? $country_id : (int) $this->getState('country_id');
		
		if(!$country_id)
		{
			return null;
		}		
		
		if(!isset($this->_statesByCountry[$country_id]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
			     		->select('id, state_name')
				 			->from('#__qazap_states')
				 			->where('country_id = ' . (int) $country_id)
				 			->where('state = 1');
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
			
			if(empty($results))
			{
				$this->_statesByCountry[$country_id] = null;
			}
			else
			{
				$this->_statesByCountry[$country_id] = $results;
			}			
		}
		
		return $this->_statesByCountry[$country_id];
	}

}
