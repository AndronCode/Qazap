<?php
/**
 * emailtemplates.php
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
class QazapModelEmailtemplates extends JModelList 
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
																		'created_by', 'a.created_by',
																		'name', 'a.name',
																		'subject', 'a.subject',
																		'head', 'a.head',
																		'body', 'a.body',
																		'default', 'a.default',
																		'purpose', 'a.purpose',
																		'language', 'a.lang'
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

		// Load the filter addresstype.
		$this->setState('filter.purpose', $app->getUserStateFromRequest($this->context.'.filter.purpose', 'filter_purpose', '', 'string'));

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
						'list.select', 'a.id,a.ordering,a.state,a.checked_out,a.checked_out_time,a.name,a.subject,a.default,a.purpose, a.lang'
					)
				);
		$query->from('`#__qazap_emailtemplates` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the language
		$query->select('l.title AS language_title')
		->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.lang');

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

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

		// Filter by search in title
		$search = $this->getState('filter.search');
		if ($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
			// Add the clauses to the query.
			$query->where('a.name LIKE ' . $token .' OR a.id LIKE '. $token .' OR a.subject Like '.$token.' OR a.purpose LIKE'. $token );
		}

		// Filter By purpose of the email
		$filter_purpose = $this->state->get("filter.purpose");
		if ($filter_purpose != '') 
		{
			$query->where("a.purpose = '".$db->escape($filter_purpose)."'");
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
    
	public function getFields()
	{
		$model = QZApp::getModel('Emailtemplate', array('ignore_request'=>true));
		return $model->getTemplates();
	}
}
