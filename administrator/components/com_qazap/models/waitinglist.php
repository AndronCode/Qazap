<?php
/**
 * waitinglist.php
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
class QazapModelWaitinglist extends JModelList 
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
				'product_name', 'pd.product_name',
				'email', 'a.user_email',
				'name', 'b.name',
				'username', 'b.username'
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
		
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_language = $lang->getTag();
		$default_language = $lang->getDefault();
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				$this->getState(
					 'list.select', 'a.id, a.product_id, a.date, a.user_email'
				)
		);
		$query->from('`#__qazap_notify_product` AS a');
		$query->select('b.name, b.username, b.id AS user_id')
				->join('LEFT', '#__users AS b ON b.id = a.user_id');
		
		$query->select('c.checked_out, c.checked_out_time')
				->join('LEFT', '#__qazap_products AS c ON c.product_id = a.product_id');
		
		$query->select('d.username as editor')
				->join('LEFT', '#__users AS d ON d.id = c.checked_out');
		
		
		if($multiple_language)
		{
			$query->select('pd.product_name');			
		
			$query->join('INNER', '#__qazap_product_details AS pd ON pd.product_id = a.product_id AND pd.language = '.$db->quote($present_language));
			
		}
		else
		{
			$query->select('pd.product_name');
			$query->join('INNER', '#__qazap_product_details AS pd ON pd.product_id = a.product_id AND pd.language = '.$db->quote($default_language));
		}
		

		// Filter by search in title
		$search = $this->getState('filter.search');
		// Filter the items over the search string if set.
		if ($search != '')
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($search) . '%');

			// Add the clauses to the query.
			$query->where('a.id LIKE ' . $token. ' OR pd.product_name LIKE '. $token. ' OR b.username LIKE '. $token. ' OR b.name LIKE'.$token);
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		
		if ($orderCol && $orderDirn) 
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}
		//print(str_replace('#_','f8rup', $query));exit;
		return $query;
	}
	
	public function getItems() 
	{
		$items = parent::getItems();

		return $items;
	}
	
	/**
	* Method for delete from notify table
	* 
	* @return Boolean true if success, false if failed
	* 
	* @param array &pks Array of primary keys
	* 
	* @since 1.0 
	*/
	public function delete(&$pks)
	 {
	 	$pks = (array) $pks;
		if(empty($pks))
		{
			$this->setError(JText::_('COM_QAZAP_INVALID_DELETE_DATA'));
			return false;
		}
		$db = $this->getDbo();	
		
		$sql = $db->getQuery(true)
				 ->delete($db->quoteName('#__qazap_notify_product'))
				 ->where('id IN ('.implode(',',$pks).')');
		try
		{
			$db->setQuery($sql);
			$db->query();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	 }
}
