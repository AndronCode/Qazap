<?php
/**
 * home.php
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
class QazapModelHome extends JModelLegacy 
{
	protected $options = array();
	protected $orders = null;
	protected $latest_products = null;
	protected $topselling_products = null;
  
  /**
  * Constructor.
  *
  * @param    array    An optional associative array of configuration settings.
  * @see        JModelList
  * @since    1.0.0
  */
  public function __construct($config = array()) 
  {
    parent::__construct($config);
    
    $options = array();
    $options['max_order_count'] = 5;
    $options['latest_product'] 	= 5;
    $options['top_selling']		= 5;

    $this->options = $options;    
  }

	protected function populateState()
	{
		$app = JFactory::getApplication();
		
		$layout = $app->input->getString('layout', null);
		$this->setState('layout', $layout);
		
		$period = $app->input->getCmd('period', null);
		$this->setState('period', $period);
		
		$from_date = $app->input->getString('from', null);
		$this->setState('from_date', $from_date);
		
		$to_date = $app->input->getString('to', null);
		$this->setState('to_date', $to_date);			
		
		$order_status = $app->input->get('order_status', null, 'array');
		$this->setState('order_status', $order_status);
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

	public function getCounts() 
	{
		$layout = $this->getState('layout');
		$db = $this->getDbo();

		switch(strtolower($layout)) 
		{
			case 'ordercount' :      
			$query = $this->getOrderCount();
			break;        
			case 'vendorcount' :      
			$query = $this->getVendorCount();
			break;      
			default:      
			$query = $this->getProductCount();  
		}

	}
	
	public function getProductCount()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(product_id)')
					->from('#__qazap_products');
		
		return $query;		
	}
	
	public function getOrderCount()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(ordergroup_id)')
					->from('#__qazap_ordergroups');
		
		return $query;		
	}
	
	public function getVendorCount()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(id)')
					->from('#__qazap_vendor');
		
		return $query;
	}
	
	public function getOrders()
	{
		if($this->orders === null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
									->select('a.ordergroup_id, a.ordergroup_number, a.cart_total, a.order_status, a.created_on')
									->from('#__qazap_ordergroups AS a');
					
			$query->select('COUNT(o.order_id) AS order_count')
						->join('LEFT', '#__qazap_order AS o ON o.ordergroup_id = a.ordergroup_id');
			
			$query->order($db->escape('a.ordergroup_id DESC'))
						->group('a.ordergroup_id, a.ordergroup_number, a.cart_total, a.order_status, a.created_on');

			try
			{
				$db->setQuery($query, 0, $this->options['max_order_count']);
				$orders = $db->loadObjectList();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			$this->orders = !empty($orders) ? $orders : array();	
		}
		
		return  $this->orders;
	}
	
	public function getOrderHistory()
	{
		$period = $this->getState('period', 'all');
		$from_date = $this->getState('from_date');
		$to_date = $this->getState('to_date');
		$order_status = $this->getState('order_status');				
		$db = $this->getDbo();
		
		$query = $db->getQuery(true)
								->select('COUNT(ordergroup_id) AS count, SUM(cart_total) AS total')
								->from('#__qazap_ordergroups');
		
		switch (strtolower($period))
		{
			case 'last7days' :
				$query->select('DATE(created_on) AS created_on')
							->where('created_on >= ( CURDATE() - INTERVAL 7 DAY )')
							->group('DATE(created_on)');
				break;			
			case 'last30days' :
				$query->select('DATE(created_on) AS created_on')
							->where('created_on >= ( CURDATE() - INTERVAL 30 DAY )')
							->group('DATE(created_on)');
				break;				
			case 'lastmonth' :
				$query->select('DATE(created_on) AS created_on')
							->where('created_on >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)')
							->where('created_on <= DATE_SUB(NOW(), INTERVAL 1 MONTH)')
							->group('DATE(created_on)');
				break;			
			case 'thismonth' :
				$query->select('DATE(created_on) AS created_on')
							->where('created_on >= LAST_DAY(CURDATE()) + INTERVAL 1 DAY - INTERVAL 1 MONTH')
							->where('created_on <= LAST_DAY(CURDATE()) + INTERVAL 1 DAY')
							->group('DATE(created_on)');	
				break;										
			case 'last1year' :
				$query->select('DATE(created_on) AS created_on')
							->where('created_on >= DATE_SUB(NOW(),INTERVAL 1 YEAR)')
							->group('YEAR(created_on), MONTH(created_on)');
				break;						
			case 'custom' :
				$query->select('DATE(created_on) AS created_on')
							->group('DATE(created_on)');
				break;
			case 'all' :
			default :
				$query->select('DATE(created_on) AS created_on')
							->group('DATE(created_on)');
			
		}
		
		if(!empty($order_status))
		{
			$query->where('order_status IN (' . implode(',', $db->quote($order_status)) . ')');
		}
		
		try
		{
			$db->setQuery($query);
			$results = $db->loadAssocList();
		}		
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$period = !empty($period) ? $period : 'last7days';
		$return = array();
		$return['counts'] = array();
		$return['counts']['label'] = JText::_('COM_QAZAP_PLOT_LBL_' . strtoupper($period));
		$return['counts']['data'] = array();
		$return['totals'] = array();
		$return['totals']['label'] = JText::_('COM_QAZAP_PLOT_LBL_' . strtoupper($period));
		$return['totals']['data'] = array();
		
		if(!empty($results))
		{
			foreach($results as $result)
			{
				$result['created_on'] = strtotime($result['created_on']) * 1000;				
				$return['totals']['data'][] = array($result['created_on'], round($result['total'], 2));
				$return['counts']['data'][] = array($result['created_on'], $result['count']);
			}
		}
		
		return $return;		
	}
	
	public function getLatestProducts()
	{
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_langauge = $lang->getTag();
		$default_language = $lang->getDefault();
		$config = QZApp::getConfig();
    $multple_pricing = $config->get('multiple_product_pricing', 0);

		if($this->latest_products === null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select('a.product_id, a.dbt_rule_id, a.dat_rule_id, a.tax_rule_id, a.in_stock, a.ordered, a.booked_order, a.created_time, a.checked_out, a.checked_out_time, a.hits')
						->from('#__qazap_products AS a');
            
			// If multiple product pricing enabled
			if($multple_pricing)
			{
				$case1  = '(CASE a.multiple_pricing ';
				$case1 .= 'WHEN 0 THEN a.product_baseprice ';		
				$case1 .= 'WHEN 1 THEN up.product_baseprice ';
				$case1 .= 'WHEN 2 THEN qp.product_baseprice ';	
				$case1 .= 'END) AS product_baseprice';
				$query->select($case1);

				$case2  = '(CASE a.multiple_pricing ';
				$case2 .= 'WHEN 0 THEN a.product_customprice ';		
				$case2 .= 'WHEN 1 THEN up.product_customprice ';
				$case2 .= 'WHEN 2 THEN qp.product_customprice ';	
				$case2 .= 'END) AS product_customprice';
				$query->select($case2);					

				// Join Usergroup based pricing. Show Public usergroup pricing
				$query->join('LEFT', '#__qazap_product_user_price AS up ON up.product_id = a.product_id AND up.usergroup_id = 1');

				// Join quantity based pricing
				$quantity = $config->get('minimum_purchase_quantity', 1);

				$query->join('LEFT', '#__qazap_product_quantity_price AS qp ON qp.product_id = a.product_id AND qp.max_quantity >= '.$quantity.' AND qp.min_quantity <= ' . (int) $quantity);
			}	
			else 
			{
				$query->select('a.product_baseprice, a.product_customprice');
			}	
      	
			// Join Product Details Table
      $query->select('b.product_name');
      
			if($multiple_language)
			{				
				$query->join('INNER', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = ' . $db->quote($present_langauge));				
			}
			else
			{
				$query->join('INNER', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = ' . $db->quote($default_language));
			}
      
		// Join Category Table
		$query->join('LEFT', '#__qazap_categories AS c ON c.category_id = a.category_id');
		$query->select('cd.title AS category_name');
      
			if($multiple_language)
			{				
				$query->join('INNER', '#__qazap_category_details AS cd ON cd.category_id = c.category_id AND cd.language = ' . $db->quote($present_langauge));
			}
			else
			{
				$query->join('INNER', '#__qazap_category_details AS cd ON cd.category_id = c.category_id AND cd.language = ' . $db->quote($default_language));
			}
      
		// Join Vendor Table
		$query->select('v.shop_name');
		$query->join('INNER', '#__qazap_vendor AS v ON v.id = a.vendor');    

			$query->order('a.product_id DESC');
		$query->group('a.product_id, a.dbt_rule_id, a.dat_rule_id, a.tax_rule_id, a.in_stock, a.ordered, a.booked_order, a.created_time, a.hits');

		// Join Editor
		$query->select('e.name AS editor');
		$query->join('LEFT', '`#__users` AS e ON e.id = a.checked_out');

			try
			{
				$db->setQuery($query, 0, $this->options['latest_product']);
				$latest_products = $db->loadObjectList();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			$this->latest_products = !empty($latest_products) ? $latest_products : array();	
		}
    
		return  $this->latest_products;
	}
	
	
	public function getTopsellingProducts()
	{
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_langauge = $lang->getTag();
		$default_language = $lang->getDefault();
		$config = QZApp::getConfig();
    $multple_pricing = $config->get('multiple_product_pricing', 0);

		if($this->topselling_products === null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select('a.product_id, a.dbt_rule_id, a.dat_rule_id, a.tax_rule_id, a.in_stock, a.ordered, a.booked_order, (a.ordered + a.booked_order) AS total_order_count, a.created_time, a.checked_out, a.checked_out_time, a.hits')
						->from('#__qazap_products AS a');
            
			// If multiple product pricing enabled
			if($multple_pricing)
			{
				$case1  = '(CASE a.multiple_pricing ';
				$case1 .= 'WHEN 0 THEN a.product_baseprice ';		
				$case1 .= 'WHEN 1 THEN up.product_baseprice ';
				$case1 .= 'WHEN 2 THEN qp.product_baseprice ';	
				$case1 .= 'END) AS product_baseprice';
				$query->select($case1);

				$case2  = '(CASE a.multiple_pricing ';
				$case2 .= 'WHEN 0 THEN a.product_customprice ';		
				$case2 .= 'WHEN 1 THEN up.product_customprice ';
				$case2 .= 'WHEN 2 THEN qp.product_customprice ';	
				$case2 .= 'END) AS product_customprice';
				$query->select($case2);					

				// Join Usergroup based pricing. Show Public usergroup pricing
				$query->join('LEFT', '#__qazap_product_user_price AS up ON up.product_id = a.product_id AND up.usergroup_id = 1');

				// Join quantity based pricing
				$quantity = $config->get('minimum_purchase_quantity', 1);

				$query->join('LEFT', '#__qazap_product_quantity_price AS qp ON qp.product_id = a.product_id AND qp.max_quantity >= '.$quantity.' AND qp.min_quantity <= ' . (int) $quantity);
			}	
			else 
			{
				$query->select('a.product_baseprice, a.product_customprice');
			}
      		
			// Join Product Details Table
      $query->select('b.product_name');
      
			if($multiple_language)
			{				
				$query->join('INNER', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = ' . $db->quote($present_langauge));				
			}
			else
			{
				$query->join('INNER', '#__qazap_product_details AS b ON b.product_id = a.product_id AND b.language = ' . $db->quote($default_language));
			}
      
      // Join Category Table
      $query->join('LEFT', '#__qazap_categories AS c ON c.category_id = a.category_id');
      $query->select('cd.title AS category_name');
      
			if($multiple_language)
			{				
				$query->join('INNER', '#__qazap_category_details AS cd ON cd.category_id = c.category_id AND cd.language = ' . $db->quote($present_langauge));
			}
			else
			{
				$query->join('INNER', '#__qazap_category_details AS cd ON cd.category_id = c.category_id AND cd.language = ' . $db->quote($default_language));
			}
      
      // Join Vendor Table
      $query->select('v.shop_name');
      $query->join('INNER', '#__qazap_vendor AS v ON v.id = a.vendor');      
      
      $query->group('a.product_id, a.dbt_rule_id, a.dat_rule_id, a.tax_rule_id, a.in_stock, a.ordered, a.booked_order, a.created_time, a.hits');
			$query->order($db->escape('total_order_count DESC'));

      // Join Editor
      $query->select('e.name AS editor');
      $query->join('LEFT', '`#__users` AS e ON e.id = a.checked_out');
      
			try
			{
				$db->setQuery($query, 0, $this->options['top_selling']);
				$topselling_products = $db->loadObjectList();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			$this->topselling_products = !empty($topselling_products) ? $topselling_products : array();	
		}
    
		return  $this->topselling_products;
	}
}
