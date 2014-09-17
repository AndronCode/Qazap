<?php
/**
 * orders.php
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
class QazapModelorders extends JModelList 
{
	protected $_ordergroup_ids = null;
	protected $_orders = array();
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JController
	* @since    1.6
	*/
	public function __construct($config = array()) 
	{
		if (empty($config['filter_fields'])) 
		{
			$config['filter_fields'] = array(
				'ordergroup_id', 'og.ordergroup_id',
				'ordergroup_number', 'og.ordergroup_number',
				'order_status', 'os.status_name',
				'payment_method', 'pm.payment_name',
				'ordergroup_total', 'og.cart_total',
				'ordergroup_number', 'og.ordergroup_number',
				'username', 'oa.first_name',
				'products_sales_price', 'a.products_sales_price',
				'product_tax_amount', 'a.product_tax_amount',
				'product_discount_amount', 'a.product_discount_amount',
				'order_subtotal', 'a.order_subtotal',
				'shipment_price', 'a.shipment_price',
				'shipment_tax', 'a.shipment_tax',
				'paymrnt_price', 'a.paymrnt_price',
				'payment_tax', 'a.payment_tax',
				'coupon_discount', 'a.coupon_discount',
				'order_total', 'a.order_total',
				'order_discount', 'a.order_discount',
				'order_tax', 'a.order_tax',
				'coupon_code', 'a.coupon_code',
				'order_currency', 'a.order_currency',
				'user_currency', 'a.user_currency',
				'currency_exchange_rate', 'a.currency_exchange_rate',
				'payment_method_id', 'a.payment_method_id',
				'shipment_method_id', 'a.shipment_method_id',
				'order_status', 'a.order_status',
				'customer_note', 'a.customer_note',
				'ip_address', 'a.ip_address',
				'created_on', 'a.created_on',
				'modified_on', 'a.modified_on',
				'modified_by', 'a.modified_by',
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
		
		$order_states = $app->getUserStateFromRequest($this->context . '.filter.orderstates', 'filter_orderstates', '', 'string');
		$this->setState('filter.orderstates', $order_states);       

		// Load the parameters.
		$params = JComponentHelper::getParams('com_qazap');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('og.ordergroup_id', 'asc');
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
	* @since	1.6
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
	* @since	1.6
	*/
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Join over the user field 'created_by'
		$query->select('og.ordergroup_id, og.ordergroup_number, og.order_currency, og.user_currency, og.currency_exchange_rate, og.cart_total, og.created_on');
		$query->from('#__qazap_ordergroups AS og');
		
		// Join over the user field 'created_by'
		$query->select('oa.first_name, CONCAT(oa.first_name, " ", oa.middle_name, " ", oa.last_name) as username');
		$query->join('LEFT', '#__qazap_order_addresses AS oa ON oa.ordergroup_id = og.ordergroup_id AND oa.address_type = ' . $db->quote('bt'));
		
		$query->select('os.status_name');
		$query->join('LEFT', '#__qazap_order_status AS os ON os.status_code = og.order_status');			
		
		// Join over the user field 'created_by'
		$query->select('pm.payment_name');
		$query->join('LEFT', '#__qazap_payment_methods AS pm ON pm.id = og.cart_payment_method_id');					

		$query->group('og.ordergroup_number, og.order_currency, og.user_currency, og.currency_exchange_rate, og.cart_total, og.created_on');

		$query->join('LEFT', '#__qazap_order AS o ON o.ordergroup_id = og.ordergroup_id');
		$query->join('LEFT', '#__qazap_vendor AS v ON o.vendor = v.id');
		$query->join('LEFT', '#__users AS user on og.user_id = user.id');

		// Filter by search in title
		$search = $this->getState('filter.search');
		// Filter the items over the search string if set.
		if($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'og.ordergroup_id LIKE ' . $token;
			$searches[] = 'og.ordergroup_number LIKE ' . $token;
			$searches[] = 'og.cart_total LIKE ' . $token;
			$searches[] = 'v.shop_name LIKE ' . $token;
			$searches[] = 'o.order_id LIKE ' . $token;
			$searches[] = 'oa.company LIKE ' . $token;
			$searches[] = 'oa.first_name LIKE ' . $token;
			$searches[] = 'oa.last_name LIKE ' . $token;
			$searches[] = 'oa.email LIKE ' . $token;
			$searches[] = 'user.name LIKE ' . $token;
			

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}
		
		$order_states = $this->getState('filter.orderstates');

		if($order_states && $order_states != '*')
		{
			$query->where('os.status_code = ' .$db->quote($order_states) );
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
		if($items === false && $this->getError())
		{
			$this->setError($this->getError());
			return false;
		}		
		
		if(!empty($items))
		{
			$this->processForDisplay($items);
		}
		
		return $items;
	}
	
	public function getOrders()
	{		
		$store = $this->getStoreId();
		
		if(isset($this->_orders[$store]))
		{
			return $this->_orders[$store];
		}
		
		if(!$this->_ordergroup_ids)
		{
			$this->_orders[$store] = false;
			return $this->_orders[$store];
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		$query->select('o.order_id, o.ordergroup_id, o.order_number, o.Total');
		$query->from('`#__qazap_order` AS o');
		
		$query->select('v.shop_name, v.id as vendor_id');
		$query->join('LEFT', '#__qazap_vendor AS v ON v.id = o.vendor');
		
		$query->select('os.status_name');
		$query->join('LEFT', '#__qazap_order_status AS os ON os.status_code = o.order_status');	
		
		$subQuery = 'SELECT order_id, SUM(commission) AS commission FROM #__qazap_order_items WHERE order_status NOT IN (' . implode(',', $db->quote($this->getNegativeOrderStates())) . ') GROUP BY order_id';
		
		$query->select('oi.commission');
		$query->join('LEFT', '(' . $subQuery . ') AS oi ON oi.order_id = o.order_id');
		
		try
		{
			$db->setQuery($query);
			$orders = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}	
		
		$return = array();
		
		if(!empty($orders))
		{
			foreach($orders as $order)
			{
				if(!isset($return[$order->ordergroup_id]))
				{
					$return[$order->ordergroup_id] = array();
				}
				
				$return[$order->ordergroup_id][] = $order;
			}
		}
		
		$this->_orders[$store] = $return;
		
		return $this->_orders[$store];		
	}
	
	protected function processForDisplay($items)
	{
		$ordergroup_ids = array();
		
		foreach($items as $item)
		{
			$ordergroup_ids[] = $item->ordergroup_id;
		}
		
		if(!empty($ordergroup_ids))
		{
			$this->_ordergroup_ids = $ordergroup_ids;
		}
	}
	
	protected function getNegativeOrderStates()
	{
		static $cache = null;

		if($cache === null)
		{
			$db = JFactory::getDbo();
			$sql = $db->getQuery(true)
						->select('status_code')
						->from('#__qazap_order_status')
						->where('stock_handle = -1');
			try
			{
				$db->setQuery($sql);
				$status_codes = $db->loadColumn();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$cache = $status_codes;						
		}

		return $cache;
	}		

}
