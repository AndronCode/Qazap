<?php
/**
 * seller.php
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
jimport('joomla.filesystem.file');
jimport('joomla.event.dispatcher');
/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelSeller extends JModelList
{	
	protected $_paymentSummary = null;
	protected $_paymentDetails = null;
	protected $_recentOrders = null;
	
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
				'order_number','a.order_number',
				'Total','a.Total',
				'status_name','c.status_name',
				'payment_id', 'a.payment_id',
				'date', 'a.date',
				'total_confirmed_order', 'a.total_confirmed_order',
				'total_confirmed_commission', 'a.total_confirmed_commission',
				'payment_amount', 'a.payment_amount',
				'product_name','pd.product_name',
				'product_name','pdd.product_name',
				'product_id','p.product_id',
				'category_name','cd.title',
				'created_time','p.created_time'
				
				
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = 'null', $direction = 'DESC')
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$layout = $app->input->getString('layout');
		$listContext = null;
		$listLayouts = array('orderlist', 'productlist', 'paymentlist');
		
		$ordergroup_id = $input->getInt('ordergroup_id');
		$this->setState('ordergroup.id', $ordergroup_id);		
		
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$user	= QZUser::get();
		$this->setState('qzuser', $user);
		$this->setState('vendor.id', $user->get('vendor_id', 0));
		
		if(in_array($layout, $listLayouts))
		{
			$listContext = 'com_qazap.seller.' . $layout;
			$defaultOrder = 'a.' . substr($layout, 0, -4) . '_id';
			
			if($layout == 'productlist')
			{
				$defaultOrder = 'p.product_id';
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
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . $this->getState('ordergroup.id');
		$id .= ':' . $this->getState('vendor.id');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}
	/**
	 * Get the master query for retrieving a list of results subject to the model state.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */	

	protected function getListQuery()
	{
		$layout = $this->getState('layout');
		
		if($layout == 'productlist')
		{
			return $this->getProductListQuery();
		}
		elseif($layout == 'paymentlist')
		{
			return $this->getPaymentListQuery();
		}
		else
		{
			return $this->getOrderListQuery();
		}
	}
	
	
	protected function getOrderListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.order_id, a.ordergroup_id, a.user_id, a.vendor, a.order_number, a.productTotalTax, '.
				'a.productTotalDiscount, a.totalProductPrice, a.shipmentTax, a.shipmentPrice, a.paymentTax, a.paymentPrice, a.CartDiscountBeforeTax, a.CartDiscountBeforeTaxInfo, a.CartTax, a.CartTaxInfo, a.CartDiscountAfterTax, a.CartDiscountAfterTaxInfo, a.coupon_discount, a.coupon_code, a.coupon_data, a.TotalTax, a.TotalDiscount, a.Total'				
			)			
		);
		
		$query->from('#__qazap_order AS a');
		
		$query->select('b.ordergroup_number, b.order_currency')
			  ->join('INNER', '#__qazap_ordergroups as b ON b.ordergroup_id = a.ordergroup_id');
			  
		$query->select('c.status_name')
			  ->join('INNER', '#__qazap_order_status AS c ON c.status_code = a.order_status');
			  
		$query->select('d.first_name, d.middle_name, d.last_name')
			  ->join('LEFT', '#__qazap_order_addresses as d ON d.ordergroup_id = a.ordergroup_id AND d.address_type = ' . $db->quote('bt'));
			  
		$query->where('a.vendor = ' . $this->getState('vendor.id'));
		
		$search = $this->getState('filter.search');

        if(!empty($search))
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($search) . '%');
			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'a.order_number LIKE ' . $token;
			$searches[] = 'b.ordergroup_number LIKE ' . $token;
			$searches[] = 'd.email LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}
		
		$ordering = $this->getState('list.ordering');
		$dirn = $this->getState('list.direction');
		
		if(!empty($ordering) && !empty($dirn))
		{
			// Add the list ordering clause.
			$query->order($db->escape($ordering) . ' ' . $dirn);
		}
		

		return $query;
	}
	
	public function getPaymentListQuery()
	{
		$user = QZUser::get();
		$juser = $user->juser;
		
		if($juser->guest)
		{
			$this->setError(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'));
			return false;
		}
		if(!$user->vendor_id)
		{
			$this->setError(JText::_('COM_QAZAP_INVALID_VENDOR'));
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			 ->select('a.payment_id, a.date, a.total_order_value, a.total_confirmed_order, a.total_commission_value, a.total_confirmed_commission, a.payment_amount, a.last_payment_amount, a.last_payment_date, a.total_paid_amount, a.total_balance, a.payment_status')
			 ->from('#__qazap_payments AS a');
		
		$query->select('b.name')
			->join('LEFT','#__extensions AS b ON a.payment_method = b.extension_id');
		
		$query->select('c.currency')
			->join('LEFT', '#__qazap_currencies AS c ON a.currency = c.id');
					
		$query->where('a.vendor = '. (int)$user->get('vendor_id'));
		$search = $this->getState('filter.search');

        if(!empty($search))
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($search) . '%');
			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'a.order_number LIKE ' . $token;
			$searches[] = 'b.ordergroup_number LIKE ' . $token;
			$searches[] = 'd.email LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}
		
		$ordering = $this->getState('list.ordering');
		$dirn = $this->getState('list.direction');
		
		if(!empty($ordering) && !empty($dirn))
		{
			// Add the list ordering clause.
			$query->order($db->escape($ordering) . ' ' . $dirn);
		}

		return $query;
	}
	
	public function getProductListQuery()
	{
		
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_language = $lang->getTag();
		$default_language = $lang->getDefault();
		
		$user = QZUser::get();
		$juser = $user->juser;
		$config = $this->getState('params');
		
		if($juser->guest)
		{
			$this->setError(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'));
			return false;
		}
		if(!$user->vendor_id)
		{
			$this->setError(JText::_('COM_QAZAP_INVALID_VENDOR'));
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
				->select('p.product_id, p.ordering, p.state, p.block, p.parent_id, p.product_sku,
			p.featured, p.vendor, p.urls, p.manufacturer_id, p.category_id, p.access, p.multiple_pricing, 
			p.dbt_rule_id, p.dat_rule_id, p.tax_rule_id, p.in_stock, p.ordered, p.booked_order, p.product_length, 
			p.product_length_uom, p.product_width, p.product_height, p.product_weight, p.product_weight_uom, 
			p.product_packaging, p.product_packaging_uom, p.units_in_box, p.images, p.related_categories, 
			p.related_products, p.membership, p.params, p.checked_out, p.checked_out_time,
			p.created_by, p.created_time, p.modified_by, p.modified_time, p.hits');
			
		$query->from('#__qazap_products AS p');
		
		$query->select('pd.product_alias, pd.short_description, pd.product_description, pd.metakey, pd.metadesc, pd.metadata');
		
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('pd.product_alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('p.product_id');
		$case_when .= $query->concatenate(array($c_id, 'pd.product_alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when);
		
		$multple_pricing = $config->get('multiple_product_pricing', false);
		
		if($multple_pricing)
		{
			$case1  = '(CASE p.multiple_pricing ';
			$case1 .= 'WHEN 0 THEN p.product_baseprice ';		
			$case1 .= 'WHEN 1 THEN up.product_baseprice ';
			$case1 .= 'WHEN 2 THEN qp.product_baseprice ';	
			$case1 .= 'END) AS product_baseprice';
			$query->select($case1);
			
			$case2  = '(CASE p.multiple_pricing ';
			$case2 .= 'WHEN 0 THEN p.product_customprice ';		
			$case2 .= 'WHEN 1 THEN up.product_customprice ';
			$case2 .= 'WHEN 2 THEN qp.product_customprice ';	
			$case2 .= 'END) AS product_customprice';
			$query->select($case2);					
			
			// Join Usergroup based pricing
			$query->join('LEFT', '#__qazap_product_user_price AS up ON up.product_id = p.product_id AND up.usergroup_id = 1');			
			// Join quantity based pricing
			$quantity = (int) $this->getState('quantity');
			$quantity = $quantity ? $quantity : (int) $config->get('minimum_purchase_quantity', 1);
			
			$query->join('LEFT', '#__qazap_product_quantity_price AS qp ON qp.product_id = p.product_id AND qp.max_quantity >= '.$quantity.' AND qp.min_quantity <= '.$quantity);
			
			// Filter by price
			if($this->getState('min_price') !== NULL && $this->getState('max_price') !== NULL)
			{
				$pricing_where  = 'CASE p.multiple_pricing ';	
				$pricing_where .= 'WHEN 0 THEN p.product_baseprice ';		
				$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
				$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
				$pricing_where .= 'END BETWEEN '.(float) $this->getState('min_price').' AND '.(float) $this->getState('max_price');
				$query->where($pricing_where);
			}
			elseif($this->getState('min_price') !== NULL)
			{
				$pricing_where  = (float) $this->getState('min_price').' <= ';
				$pricing_where .= 'CASE p.multiple_pricing ';	
				$pricing_where .= 'WHEN 0 THEN p.product_baseprice ';		
				$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
				$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
				$pricing_where .= 'END ';
				$query->where($pricing_where);
			}
			elseif($this->getState('max_price') !== NULL)
			{
				$pricing_where  = (float) $this->getState('max_price').' >= ';
				$pricing_where .= 'CASE p.multiple_pricing ';	
				$pricing_where .= 'WHEN 0 THEN p.product_baseprice ';		
				$pricing_where .= 'WHEN 1 THEN up.product_baseprice ';
				$pricing_where .= 'WHEN 2 THEN qp.product_baseprice ';
				$pricing_where .= 'END ';
				$query->where($pricing_where);
			}			
		}
		else 
		{
			$query->select('p.product_baseprice AS product_baseprice, p.product_customprice AS product_customprice');
			// Filter by price
			if($this->getState('min_price') !== NULL && $this->getState('max_price') !== NULL)
			{
				$query->where('p.product_baseprice BETWEEN '.(float) $this->getState('min_price').' AND '.(float) $this->getState('max_price'));
			}
			elseif($this->getState('min_price') !== NULL)
			{
				$query->where('p.product_baseprice >= '. (float) $this->getState('min_price'));
			}
			elseif($this->getState('max_price') !== NULL)
			{
				$query->where('p.product_baseprice <= '. (float) $this->getState('max_price'));
			}
		}		
			
		if($multiple_language)
		{
			$query->select('CASE WHEN pd.product_name IS NULL THEN pdd.product_name ELSE pd.product_name END AS product_name');			
		
			$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = p.product_id AND pd.language = '.$db->quote($present_language));
			$query->join('LEFT', '#__qazap_product_details AS pdd ON pdd.product_id = p.product_id AND pdd.language = '.$db->quote($default_language));				
		}
		else
		{
			$query->select('pd.product_name');
			$query->join('INNER', '#__qazap_product_details AS pd ON pd.product_id = p.product_id AND pd.language = '.$db->quote($default_language));
		}	

		// join category details table. Note: cd is for category details
		$query->select('cd.title AS category_name');
		if($multiple_language)
		{
			$query->join('LEFT', '#__qazap_category_details AS cd ON cd.category_id = p.category_id AND cd.language = '.$db->quote($present_language));				
		}
		else
		{
			$query->join('INNER', '#__qazap_category_details AS cd ON cd.category_id = p.category_id AND cd.language = '.$db->quote($default_language));
		}
		
		if(($this->getState('only_in_stock') === null))
		{
			$only_in_stock = ($config->get('stockout_action') == 'hide_product');
		}
		else
		{
			$only_in_stock = $this->getState('only_in_stock');
		}
		
		if($only_in_stock  && $config->get('enablestockcheck'))
		{
			$query->where('(p.in_stock - p.booked_order) > 0');
		}
		
		$manufacturer = $this->getState('manufacturer');
		
		if(!empty($manufacturer))
		{
			$query->select('m.manufacturer_name, m.manufacturer_email, m.manufacturer_categories, m.description AS manufacturer_description, m.manufacturer_url, m.images AS manufacturer_images');
			$query->join('LEFT', '#__qazap_manufacturers AS m ON m.id = p.manufacturer_id');
		}
		
		if ($this->getState('hide_block'))
		{
				$query->where('p.block = 0');		
		}	
		
		$state = $this->getState('state');
		
		if (!empty($state))
		{
			$query->where('p.state = ' . (int) $state);
		}
		
		$manufacturers = $this->getState('manufacturers');
		
		if (!empty($manufacturers))
		{
			$manufacturers = (array) $manufacturers;
			$manufacturers = array_map('intval', $manufacturers);
			
			$query->where('p.manufacturer_id IN (' . implode(',', $manufacturers). ')');
		}
		
		$query->group(
		 	'p.product_id, p.ordering, p.state, p.block, p.parent_id, p.product_sku,
			p.featured, p.vendor, p.urls, p.manufacturer_id, p.category_id, p.access, p.product_baseprice, 
			p.product_customprice, p.multiple_pricing, p.dbt_rule_id, p.dat_rule_id, p.tax_rule_id, p.in_stock,
			p.ordered, p.booked_order, p.product_length, p.product_length_uom, p.product_width, p.product_height,
			p.product_weight, p.product_weight_uom, p.product_packaging, p.product_packaging_uom, p.units_in_box,
			p.images, p.related_categories, p.related_products, p.membership, p.params, p.checked_out, p.checked_out_time,
			p.created_by, p.created_time, p.modified_by, p.modified_time, p.hits'
		);
		
		$query->where('p.vendor = '.$user->vendor_id );
		
		$search = $this->getState('filter.search');

        if(!empty($search))
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($search) . '%');
			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'p.product_id LIKE ' . $token;
			$searches[] = 'pd.product_name LIKE ' . $token;
			$searches[] = 'pdd.product_name LIKE ' . $token;
			$searches[] = 'cd.title LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}
		
		$ordering = $this->getState('list.ordering');
		$dirn = $this->getState('list.direction');
		if(!empty($ordering) && !empty($dirn))
		{
			// Add the list ordering clause.
			$query->order($db->escape($ordering) . ' ' . $dirn);
		}
		return $query;
		
	}
	
	public function getItems()
	{
		return parent::getItems();
	}		
	
		
	/**
	* method for getting recent orders
	*  
	* @return object details of the order
	*/
	public function getRecentOrders()
	{
		$params = JComponentHelper::getparams('com_qazap');
		if($this->_recentOrders === null)
		{
			$db = $this->getDbo();		
			$query = $this->getOrderListQuery()
					->clear('where')
					->where('a.vendor = ' . $this->getState('vendor.id'));
					
	        if($this->getState('filter.search'))
			{
				// Escape the search token.
				$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
				// Compile the different search clauses.
				$searches = array();
				$searches[] = 'a.order_number LIKE ' . $token;
				$searches[] = 'b.ordergroup_number LIKE ' . $token;
				$searches[] = 'd.email LIKE ' . $token;

				// Add the clauses to the query.
				$query->where('(' . implode(' OR ', $searches) . ')');
			}
			try
			{
				$db->setQuery($query, 0, $params->get('recent_order_limit'));
				$orders = $db->loadObjectList();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(!empty($orders))
			{
				$this->_recentOrders = $orders;
			}
			else
			{
				$this->_recentOrders = false;
			}
		}

		return $this->_recentOrders;
	}
	
	public function getOrderDetails($ordergroup_id = null, $thisVendor = null)
	{
		$orderGroupId = $ordergroup_id ? $ordergroup_id : $this->getState('ordergroup.id');
		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'models');
		$model = JModelLegacy::getInstance('Order','QazapModel',array('ignore_request'=>true));
		$ordergroup = $model->getOrderGroupByID($orderGroupId);
		
		if(!$ordergroup)
		{
			JError::raiseError(500, $ordergroup->getError());
			return false;
		}
		
		$vendor_ids = array_keys($ordergroup->vendor_carts);
		$thisVendor = $thisVendor ? $thisVendor : (int) $this->getState('vendor.id');
		
		if(empty($vendor_ids) || !in_array($thisVendor, $vendor_ids))
		{
			JError::raiseError(404, 'Invalid order');
		}
		
		$order = $ordergroup->vendor_carts[$thisVendor];
		$ordergroup->vendor_carts = array($thisVendor => $order);
		return $ordergroup;
	}
	
	public function updateOrderStatus($data)
	{
		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'models');
		$model = JModelLegacy::getInstance('Order','QazapModel',array('ignore_request'=>true));
		
		if(!$model->updateOrderStatus($data))
		{
			$this->setError(JText::sprintf('COM_QAZAp_SAVE_FAILED', $model->getError()));
			return false;
		}
		return true;		
	}
	
	public function updateItemStatus($data)
	{
		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'models');
		$model = JModelLegacy::getInstance('Order','QazapModel',array('ignore_request'=>true));
		
		if(!$model->updateItemStatus($data))
		{
			$this->setError(JText::sprintf('COM_QAZAP_SAVE_FAILED', $model->getError()));
			return false;
		}
		return true;		
	}

	public function getPaymentDetails()
	{
		
		if($this->_paymentDetails === null)
		{
			$app = JFactory::getApplication();
			$user = QZUser::get();
			$juser = $user->juser;
 		
			$payment_id = $app->input->getInt('payment_id');
			$db = $this->getDbo();
		
			$query = $db->getQuery(true)
			 ->select('a.vendor, a.payment_id, a.date, a.total_order_value, a.total_confirmed_order, a.total_commission_value, a.total_confirmed_commission, a.last_payment_amount, a.last_payment_date, a.total_paid_amount, a.total_balance, a.payment_amount, a.balance, a.payment_status, a.payment_method')
			 ->from('#__qazap_payments AS a');
		
			$query->select('b.shop_name')
			->join('LEFT', '#__qazap_vendor AS b ON a.vendor = b.id');
		
			$query->select('c.currency')
			->join('LEFT', '#__qazap_currencies AS c ON a.currency = c.id');
			
			$query->select('d.name AS method_name')
			->join('LEFT', '#__extensions AS d ON a.payment_method = d.extension_id');
		
			$query->where('a.payment_id = '. (int)$payment_id);
		
			try
			{
				//print(str_replace('#__', 'f8rup_', $query));exit;
				$db->setQuery($query);
				$paymentDetails = $db->loadObject();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
		
		
			if($juser->guest)
			{
				$this->setError(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'));
				return false;
			}
		
			if($user->vendor_id !== $paymentDetails->vendor)
			{
				JError::raiseError(404, 'Invalid input');
				return false;
			}
			
			$this->_paymentDetails = !empty($paymentDetails) ? $paymentDetails : array();			
		}
		return $this->_paymentDetails;
	}
	
	public function getPaymentSummary()
	{
		if($this->_paymentSummary === null)
		{
			$vendor_id = $this->getState('vendor.id');
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->from('#__qazap_vendor as a')
				->select('SUM(p.payment_amount) AS total_payment, COUNT(p.payment_id) AS count')
				->join('LEFT', '#__qazap_payments AS p ON p.vendor = a.id AND p.payment_status = 1');
		
			$subQuery1 = '(SELECT SUM(conf.product_totalprice) AS total_confirmed_order, SUM(conf.commission) AS total_confirmed_commission, conf.vendor, conf.order_status from #__qazap_order_items AS conf GROUP BY conf.vendor, conf.order_status)';
		
			$query->select('c.total_confirmed_order, c.total_confirmed_commission');
			$query->join('LEFT', $subQuery1 . ' AS c ON (c.vendor = a.id AND c.order_status = ' . $db->quote('Z') .')');	

			$subQuery2 = '(SELECT SUM(total.product_totalprice) AS total_order_value, SUM(total.commission) AS total_commission_value, total.vendor, total.deleted from #__qazap_order_items AS total INNER JOIN #__qazap_order_status AS status ON (total.order_status = status.status_code AND status.stock_handle != -1) GROUP BY total.vendor, total.deleted)';
		
			$query->select('t.total_order_value, t.total_commission_value');
			$query->join('LEFT', $subQuery2 . ' AS t ON (t.vendor = a.id AND t.deleted = 0)');

			$query->where('a.id = ' . (int) $vendor_id);

			try
			{
				$db->setQuery($query);
				$result = $db->loadObject();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$this->_paymentSummary = !empty($result) ? $result : array();			
		}

		return $this->_paymentSummary; 
	}
	
	
}