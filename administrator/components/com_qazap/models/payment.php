<?php
/**
 * payment.php
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

jimport('joomla.application.component.modeladmin');

/**
 * Qazap model.
 */
class QazapModelPayment extends JModelAdmin
{
	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0
	 */
	public function getTable($type = 'Payment', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_qazap.payment', 'payment', array('control' => 'jform', 'load_data' => $loadData));
        
        
		if (empty($form)) 
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.payment.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();
            
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{
			
			$this->setState('paymentmethod.id', $item->payment_method);
			
			if(isset($item->params) && !is_array($item->params))
			{
				$item->params = json_decode($item->params);
			}
			$this->setState('paymentmethod.params', $item->params);
			//Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_payments');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	public function getVendorHistory()
	{
		$db = $this->getDBO();
		$input = JFactory::getApplication()->input;
		$vendorId = $input->get('vendor_id', 0, 'int');
		$orders = new stdClass;
		// GET TOTAL ORDERS AND TOTAL COMMISSIONS // 
		
		$sql = $db->getQuery(true)
			 ->select(array('SUM(product_totalprice) AS total_order_value','SUM(commission) AS total_commission_value'))
			 ->from('#__qazap_order_items AS a')
			 ->join("LEFT", '#__qazap_order_status As b on a.order_status = b.status_code')
			 ->where('a.vendor = '.$vendorId)
			 ->where('a.deleted = 0')
			 ->where('b.stock_handle != -1');
		$db->setQuery($sql);
		$total_order_items = $db->loadObject();
		
		// GET CONFIRMED ORDERS ANDCOMMISSIONS // 
		
		$sql->clear()
			 ->select(array('SUM(product_totalprice) AS total_confirmed_order','SUM(commission) AS total_confirmed_commission'))
			 ->from('#__qazap_order_items')
			 ->where('vendor = '.$vendorId.' AND order_status = '.$db->quote('Z'));
		$db->setQuery($sql);
		$total_confirmed_items = $db->loadObject();
		
		// GET PAYMENTS DETAILS // 
		$sql->clear()
			 ->select(array('p.payment_amount AS last_payment_amount','p.date AS last_payment_date','SUM(DISTINCT pa.payment_amount) AS total_paid_amount'))
			 ->from('#__qazap_payments AS pa')
			 ->leftjoin('#__qazap_payments AS p ON p.payment_id = (SELECT MAX(a.payment_id) FROM #__qazap_payments AS a WHERE a.vendor = '.$vendorId.')')
			 ->where('pa.vendor = '.$vendorId);
		$db->setQuery($sql);
		$total_payments = $db->loadObject();
		
		foreach($total_order_items as $key=>$value)
		{
			$orders->$key = number_format($value,6,'.','');
		}
		foreach($total_confirmed_items as $key => $value)
		{
			$orders->$key = number_format($value,6,'.','');
		}
		foreach($total_payments as $key=>$value)
		{
			if($key != 'last_payment_date')
			{
				$orders->$key = number_format($value,6,'.','');	
			}
			else
			{
				$orders->$key = $value;
			}
			
		}
		$orders->total_balance = $orders->total_commission_value - $orders->total_paid_amount;
		return $orders;
	}
	
	
	public function getPaymentParams()
	{
		$saved_method_id = $this->setState('paymentmethod.id', '');
		$new_method_id = JFactory::getApplication()->input->get('extension_id', 0, 'int');
		
		if(!$saved_method_id && !$new_method_id)
		{
			return;
		}
		elseif($new_method_id)
		{
			$dispatcher	= JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapvendorpayment');
			$plugin = QazapHelper::getPlugin($new_method_id);
			$form = NULL;
			
			$results = $dispatcher->trigger('onDisplayPayment', array($plugin, &$form));
			
			foreach($results as $result)
			{
				if($result === false)
				{
					$this->setError($dispatcher->getError());
					return false;
				}
			}
			
			return $form;
		}
		elseif($saved_method_id)
		{
			$dispatcher	= JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapvendorpayment');
			$plugin = QazapHelper::getPlugin($saved_method_id);
			$params = $this->getState('paymentmethod.params', array());
			$params = (object) $params;
			$form = NULL;
			
			$results = $dispatcher->trigger('onDisplayPayment', array($plugin, &$form, $params));

			foreach($results as $result)
			{
				if($result === false)
				{
					$this->setError($dispatcher->getError());
					return false;
				}
			}
			
			return $form;			
		}
		
	}
	
	public function save($data)
	{
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		$recurrsive = false;

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			// Include the qazapvendorpayment plugins for the on save events.
			JPluginHelper::importPlugin('qazapvendorpayment');

			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('payment', &$data, $isNew));
			
			if (in_array(false, $result, true))
			{
				$this->setError($dispatcher->getError());
				return false;
			}			
			
	
			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}
			
			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}			

			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			
			$paymentData = JArrayHelper::fromObject($table);
			
			// Trigger the onContentAfterSave event.
			if($table->payment_method != 'm' && !$table->payment_status && $table->state)
			{
				$plugin = QZPlugins::get($table->payment_method);
				$paymentData = array_merge($data, $paymentData);
				$result = $dispatcher->trigger('onProcessPayment', array($plugin, &$paymentData));
				$recurrsive = true;
			}
			elseif($table->payment_method != 'm' && $table->payment_status === 1  && !$table->state)
			{
				$plugin = QZPlugins::get($table->payment_method);
				$paymentData = array_merge($data, $paymentData);
				$result = $dispatcher->trigger('onCancelPayment', array($plugin, &$paymentData));
				$recurrsive = true;
			}			
			
		}
		
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}		
		
		// Clean the cache.
		$this->cleanCache();
		
		// Trigger the onContentAfterSave event.
		$dispatcher->trigger('onAfterSave', array('payment', $data, $isNew));
		
		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		
		$this->setState($this->getName() . '.new', $isNew);
		
		if(isset($data['send_mail']) && $data['send_mail'] && $isNew)
		{
			$mail = QZApp::getModel('mail');
			
			if($mail->send('payment', $data))
			{
				$paymentData['mail_sent'] = 1;
				$paymentData['send_mail'] = 0;
				$recurrsive = true;
			}
		}
		
		
		if($recurrsive)
		{
			return $this->save($paymentData);
		}

		return true;
	}
}