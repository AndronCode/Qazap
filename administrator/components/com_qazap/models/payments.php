<?php
/**
 * payments.php
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
class QazapModelPayments extends JModelList 
{
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see      JController
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		if (empty($config['filter_fields'])) 
		{
			$config['filter_fields'] = array(
			'payment_id', 'a.payment_id',
			'shop_name', 'b.shop_name',
			//'date', 'a.date',
			'state', 'a.state',
			'payment_status','a.payment_status',
			'payment_amount', 'a.payment_amount',
			'payment_method', 'a.payment_method',
			'from_date', 'a.date',
			'mail_sent','a.mail_sent'
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

		$processed = $app->getUserStateFromRequest($this->context . '.filter.process', 'filter_process', '1', 'string');
		$this->setState('filter.process', $processed);

		$payment_status = $app->getUserStateFromRequest($this->context . '.filter.payment_status', 'filter_payment_status', '*', 'string');
		$this->setState('filter.payment_status', $payment_status);

		$mail_sent = $app->getUserStateFromRequest($this->context . '.filter.mail_sent', 'filter_mail_sent', '*', 'string');
		$this->setState('filter.mail_sent', $mail_sent);

		// Filter By Vendor //	

		$vendor = $app->getUserStateFromRequest($this->context . '.filter.vendor', 'filter_vendor', '', 'string');
		$this->setState('filter.vendor', $vendor);

		$from_date = $app->getUserStateFromRequest($this->context . '.list.from_date', 'filter_from_date', '', 'string');
		$this->setState('list.from_date', $from_date); 

		$to_date = $app->getUserStateFromRequest($this->context . '.list.to_date', 'filter_to_date', '', 'string');
		$this->setState('list.to_date', $to_date);		

		// Load From Date Filter //		
		$list = $app->getUserStateFromRequest($this->context . '.list', 'list', '', 'array');

		/*$from_date = isset($list['from_date']) ? $list['from_date'] : null;
		$this->setState('list.from_date', $from_date);*/

		$to_date = isset($list['to_date']) ? $list['to_date'] : null;
		$this->setState('list.to_date', $to_date);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_qazap');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.payment_id', 'asc');
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
	* @since	1.1
	*/
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
		$this->getState(
		    'list.select', 'a.payment_id, a.state, a.date, a.payment_amount, a.payment_method, a.payment_status, a.mail_sent, a.checked_out, a.checked_out_time , b.shop_name, c.name AS editor'
		)
		);

		$query->from('`#__qazap_payments` AS a');
		$query->leftjoin('`#__qazap_vendor` AS b ON a.vendor = b.id');
		$query->leftjoin('`#__users` AS c ON a.checked_out = c.id')
		->group('a.payment_id, a.state, a.date, a.payment_amount, a.payment_method, a.payment_status, a.mail_sent, a.checked_out, a.checked_out_time');

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		//Filter by published state		
		$processed = $this->getState('filter.process');

		if (is_numeric($processed)) 
		{
			$query->where("a.state = ".$processed);
		}

		// Filter By Payment Status // 		
		$payment_status = $this->getState('filter.payment_status', '*');

		if ($payment_status != '*') 
		{
			$query->where('a.payment_status = '.(int) $payment_status);
		}

		// Filter By Mail Sent//
		$mailSent = $this->getState('filter.mail_sent', '*');
		if ($mailSent != '*') 
		{
			$query->where('a.mail_sent = '.(int) $mailSent);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) 
		{
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');
			// Add the clauses to the query.
			$query->where('b.shop_name LIKE ' . $token . ' OR a.payment_id LIKE ' . $token);
		}

		// Filter by From Date// 

		$from_date 	= $this->getState('list.from_date','');
		$to_date	= $this->getState('list.to_date','');
		if ($from_date != '' && $to_date == '') 
		{
			$query->where('a.date >= ' .$db->quote($from_date));
		}

		//Filter By To Date//
		if ($from_date == '' && $to_date != '') 
		{
			$query->where('a.date <= ' .$db->quote($to_date));
		}
		//Filter By Both//
		if ($from_date != '' && $to_date != '') 
		{
			$query->where('a.date BETWEEN ' .$db->quote($from_date).' AND '. $db->quote($to_date));
		}

		if($vendor = $this->getState('filter.vendor', 0))
		{
			$query->where('a.vendor = '.$vendor);
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
	
	/**
	* Method to get to payment list items 
	* 
	* @return		object
	* @since		1.0.0
	*/	
	public function getItems() 
	{
		$items = parent::getItems();      
		return $items;
	}
	
	/**
	* Method to get to Total Payment Summery based on set filters
	* 
	* @return		object
	* @since		1.0.0
	*/	
	public function getPaymentTotal()
	{
		$vendor =  $this->getState('filter.vendor', 0);
		$state  =  $this->getState('filter.process', 1);
		$payment_status = $this->getState('filter.payment_status', '*');
		$mail_sent = $this->getState('filter.mail_sent', '*');
		$from_date = $this->getState('list.from_date', '');
		$to_date = $this->getState('list.to_date', '');
		
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			 ->select(array('SUM(payment_amount) AS total_payment', 'COUNT(payment_id) AS count'));
		$sql->from('`#__qazap_payments`');
		
		if($state != '*')
		{
			$sql->where('state = '.$state);	
		}
		else
		{
			$sql->where('state = 1');
		}
		
		if($vendor)
		{
			$sql->where('vendor = '.$vendor);
		}
		
		if($payment_status != '*')
		{
			$sql->where('payment_status = '.$payment_status);
		}
		
		if($mail_sent != '*')
		{
			$sql->where('mail_sent = '.$mail_sent);
		}
		if($from_date != '' && $to_date == '')
		{
			$sql->where('date >= '.$db->quote($from_date));
		}
		if($from_date == '' && $to_date != '')
		{
			$sql->where('date <= '.$db->quote($to_date));
		}
		if($from_date != '' && $to_date != '')
		{
			$sql->where('date BETWEEN '.$db->quote($from_date).' AND '.$db->quote($to_date));
		}
		
		try {
			$db->setQuery($sql);
			$payment_result = $db->loadObject();
		} catch (Exception $e) {
			$this->setError($e->getMessage());	
			return false;		
		}
		$sql->clear()
			 ->select(array('SUM(product_totalprice) AS total_confirmed_order','SUM(commission) AS total_confirmed_commission'))
			 ->from('#__qazap_order_items')
			 ->where('order_status = '.$db->quote('Z'));
			 
		if($vendor)
		{
			$sql->where('vendor = '.$db->quote($vendor));
		}
	 
		$db->setQuery($sql);
		
		try 
		{
			$db->setQuery($sql);
			$confirmed_result = $db->loadObject();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMassage());	
			return false;		
		}
		
		// 
		$sql->clear()
			 ->select(array('SUM(product_totalprice) AS total_order_value','SUM(commission) AS total_commission_value'))
			 ->from('#__qazap_order_items AS a')
			 ->join("LEFT", '#__qazap_order_status As b on a.order_status = b.status_code')
			 ->where('a.deleted = 0')
			 ->where('b.stock_handle != -1');
			 	
		if($vendor)
		{
			$sql->where('vendor = '.$db->quote($vendor));
		}
			 
		$db->setQuery($sql);
		
		try 
		{
			$db->setQuery($sql);
			$result = $db->loadObject();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());	
			return false;		
		}	
		
		$result->total_confirmed_order = $confirmed_result->total_confirmed_order;
		$result->total_confirmed_commission = $confirmed_result->total_confirmed_commission;
		$result->total_payment = $payment_result->total_payment;
		$result->earning = ($result->total_confirmed_order - $result->total_confirmed_commission);
		$result->balance = ($result->earning - $result->total_payment);
		$result->count = $payment_result->count;		
		unset($payment_result, $confirmed_result);
			
		return $result;
	}
	
}
