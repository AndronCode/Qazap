<?php
/**
 * profilelists.php
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

/**
 * Profile model class for Users.
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapModelProfilelists extends JModelList
{
	protected $_cache = array();
	protected $_wishlist = null;
	protected $_notifylist = null;
	protected $_orderlist = null;
	
	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see     JController
	* @since   1.0.0
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
				'created_on', 'og.created_on'
			);
		}

		parent::__construct($config);
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */	
	protected function populateState($ordering = 'null', $direction = 'DESC')
	{
		$app = JFactory::getApplication();
		$listContext = null;
		
		$layout = $app->input->getString('layout');
		$listLayouts = array('wishlist', 'notifylist', 'orderlist');
		
		if(in_array($layout, $listLayouts))
		{
			$listContext = 'com_qazap.profile.' . $layout;
			$defaultOrder = 'a.id';
			
			if($layout == 'orderlist')
			{
				$defaultOrder = 'og.ordergroup_id';
			}
		}
		
		if($listContext !== null)
		{
			$limit = $app->getUserStateFromRequest($listContext . '.limit', 'limit', $app->getCfg('list_limit', 0), 'uint');
			$limitstart = $app->getUserStateFromRequest($listContext . '.limitstart', 'limitstart', 0, 'uint');
			$filter_search = $app->getUserStateFromRequest($listContext . '.filter_search', 'filter-search', null, 'string');	
			$orderCol = $app->getUserStateFromRequest($listContext . '.filter_order', 'filter_order', $defaultOrder, 'string');	
			$orderCol = !empty($orderCol) ? $orderCol : $defaultOrder;
			$orderDir = $app->getUserStateFromRequest($listContext . '.filter_order_Dir', 'filter_order_Dir', 'DESC', 'string');	
			$orderDir = !empty($orderDir) ? $orderDir : 'DESC';
			
			$this->setState('list.limit', $limit);
			$this->setState('list.start', $limitstart);					
			$this->setState('list.ordering', $orderCol);
			$this->setState('list.direction', $orderDir);
			$this->setState('filter.search', $filter_search);			
		}		
		
		$this->setState('layout', $layout);	
		
		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);
	}
	
/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('layout');

		return parent::getStoreId($id);
	}	
	
	protected function getListQuery()
	{
		$layout = $this->getState('layout');
		
		if($layout == 'wishlist')
		{
			return $this->getWishListQuery();
		}
		elseif($layout == 'waitinglist')
		{
			return $this->getWaitingListQuery();
		}
		else
		{
			return $this->getOrderListQuery();
		}
	}
	
	public function getItems()
	{
		$store = $this->getStoreId();
		
		if(isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}	
			
		$data = parent::getItems();
		
		if($data === false && $this->getError())
		{
			return JError::raiseError(500, $this->getError());
		}
		
		$layout = $this->getState('layout');
		
		if(($layout == 'wishlist' || $layout == 'waitinglist') && !empty($data))
		{

			$wishListProductId = array();
			
			foreach($data as $result)
			{
				$wishListProductId[] = $result->product_id;
			}
			
			$options = array('custom_fields' => false, 'attributes'=> false);
			
			$helper = QZProducts::getInstance($options);
			$products = $helper->getList(0, $wishListProductId);
			
			if(empty($products))
			{
				return false;
			}
			
			foreach($data as $key => &$result)
			{
				if(isset($products[$result->product_id]))
				{
					$result->product = $products[$result->product_id];
				}
				else
				{
					unset($data[$key]);
				}
			}
					
			$this->cache[$store] = $data;		
		}
		
		return $data;
	}	
	
		
	/*
	* 
	* Get Wishlist products
	*
	*/
	public function getWishListQuery()
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			 	->select('id, product_id')
			 	->from('#__qazap_wishlist')
			 	->where('user_id = ' . (int) $user->get('id'));
		
		return $query;			 
	}

	/*
	* 
	* Get Notifylist products
	*
	*/
	public function getWaitingListQuery()
	{
		$user = JFactory::getUser();
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			 ->select('id, product_id')
			 ->from('#__qazap_notify_product')
			 ->where('user_id = ' . (int) $user->get('id'));
			 
		return $query;
	}
	
	protected function getOrderListQuery()
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
		$query->join('INNER', '#__qazap_order_status AS os ON os.status_code = og.order_status');			
		
		// Join over the user field 'created_by'
		$query->select('pm.payment_name');
		$query->join('LEFT', '#__qazap_payment_methods AS pm ON pm.id = og.cart_payment_method_id');					

		$query->group('og.ordergroup_number, og.order_currency, og.user_currency, og.currency_exchange_rate, og.cart_total, og.created_on');

		$query->join('LEFT', '#__qazap_order AS o ON o.ordergroup_id = og.ordergroup_id');
		$query->join('LEFT', '#__qazap_vendor AS v ON o.vendor = v.id');
		$query->join('LEFT', '#__users AS user on og.user_id = user.id');		
		
		$user = JFactory::getuser();
		$query->where('og.user_id = ' . (int) $user->get('id'));
		
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
			$searches[] = 'os.status_name LIKE ' . $token;
			$searches[] = 'pm.payment_name LIKE ' . $token;
			
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
}
