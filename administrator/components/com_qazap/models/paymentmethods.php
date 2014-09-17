<?php
/**
 * paymentmethods.php
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
class QazapModelPaymentmethods extends JModelList 
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
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'payment_name', 'a.payment_name',
				'payment_description', 'a.payment_description',
				'payment_method', 'a.payment_method',
				'tax','a.tax',
				'price','a.price',
				'tax_calculation','a.tax_calculation',
				'shopper_group', 'a.shopper_group',
				'list_order', 'a.list_order',
				'vendor', 'b.shop_name',
				'name', 'c.name'
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
		                'list.select', 'a.id, a.ordering, a.checked_out, a.checked_out_time, a.state, a.payment_name, a.payment_description, a.price, a.tax, a.tax_calculation, c.name, d.name AS editor'
		        )
		);
		$query->from('`#__qazap_payment_methods` AS a');
		$query->join('LEFT', '`#__extensions` AS c ON a.payment_method = c.extension_id');
		$query->join('LEFT', '`#__users` AS d ON a.checked_out = d.id');
		$query->group('a.id, a.ordering, a.checked_out, a.checked_out_time, a.state, a.payment_name, a.payment_description, a.price, a.tax, a.tax_calculation');
	  
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
			$searches[] = 'a.payment_name LIKE ' . $token;
			$searches[] = 'a.payment_method LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
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
