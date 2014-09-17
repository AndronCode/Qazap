<?php
/**
 * order.php
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

if(!class_exists('QZCart'))
{
	require(QZPATH_HELPER_ADMIN . DS . 'cart.php');
}
if(!class_exists('QZOrder'))
{
	require(QZPATH_HELPER_ADMIN . DS . 'order.php');
}
if(!class_exists('QZCartItemNode'))
{
	require(QZPATH_HELPER_ADMIN . DS . 'orderitem.php');
}

/**
 * Qazap model.
 */
class QazapModelOrder extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_QAZAP';
	
	protected $_orderGroupIDs = array();
	
	protected $_orderGroupNumbers = array();
	
	protected $_comments = array();
	
	protected $_history = array();
	
	protected $_vendor_emails = array();
	
	protected $_historyData = null;
	
	protected $_paymentMethods = array();
	
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		$this->_historyData = new stdClass;
		parent::__construct($config);
	}
	
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = $app->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}	
	
	public function getTable($type = 'Ordergroup', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function groupToArray($group_id)
	{
		if(strpos($group_id, '::') === false)
		{
		$this->setError('Invalid Group ID');
		return false;
		}

		$product_group_id = explode('::', $group_id);
		$product_id = $product_group_id[0];
		$product_attr_ids = isset($product_group_id[1]) ? $product_group_id[1] : 0;
		$product_attr_ids = (strpos($product_attr_ids, ':') === false) ? $product_attr_ids : explode(':', $product_attr_ids);
		$membership_id = isset($product_group_id[2]) ? $product_group_id[2] : 0;
		$return = array($product_id, (array) $product_attr_ids, $membership_id);

		return  $return;	
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_qazap.order', 'order', array('control' => 'jform', 'load_data' => $loadData));  

		if (empty($form)) 
		{
			return false;
		}		
		
		return $form;
	}

	/**
	 * Add dynamic Userinfo form fields
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$address_id_form = new SimpleXMLElement('<fieldset name="order_address_id_set"></fieldset>');
		$field = $address_id_form->addChild('field');
		$field->addAttribute('name', 'order_address_id');
		$field->addAttribute('type', 'hidden');
		
		$addressForm = new SimpleXMLElement('<form></form>');
		$fields = $addressForm->addChild('fields');
		$fields->addAttribute('name', 'billing_address');
		$BTAddressFieldsets = QazapHelper::getUserFields('bt');
		if(is_array($BTAddressFieldsets))
		{
			$BTAddressFieldsets[] = $address_id_form;
			foreach($BTAddressFieldsets as $fieldset)
			{
				self::addNode($fields, $fieldset);
			}
		}
		
		$fields = $addressForm->addChild('fields');
		$fields->addAttribute('name', 'shipping_address');
		$STAddressFieldsets = QazapHelper::getUserFields('st');			
		if(is_array($STAddressFieldsets))
		{
			$STAddressFieldsets[] = $address_id_form;
			foreach($STAddressFieldsets as $fieldset)
			{
				$fieldset->attributes()->name = 'shipping_' . ((string) $fieldset->attributes()->name);
				self::addNode($fields, $fieldset);
			}
		}			
		
		$form->load($addressForm);
		parent::preprocessForm($form, $data, $group);		
	}	

	protected static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		// Add the new child node.
		$node = $source->addChild($new->getName(), trim($new));
		// Add the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			$node->addAttribute($name, $value);
		}
		// Add any children of the new node.
		foreach ($new->children() as $child)
		{
			self::addNode($node, $child);
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.order.data', array());

		if (empty($data)) 
		{
			$data = $this->getOrderGroupByID();            
		}

		return $data;
	}

	/**
	* Method to update an Order group status
	* 
	* @param array $data	- Array of data for the update in the following format
	* 
	* $data['ordergroup_id'] => ordergroup_id, 
	* $data['order_status'] => P, X, C etc. Order status code, 
	* $data['apply_to_all_orders'] => true/false, 
	* $data['comment'] => comment string
	* 
	* @return	boolean
	* @since	1.0.0
	*/
	public function updateOrdergroupStatus($data)
	{
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		
		if(!$pk)
		{
			$this->setError('No Order group id found in the form data');
			return false;
		}
		
		$order_status = $data['order_status'];
		$apply_to_all_orders = isset($data['apply_to_all_orders']) ? $data['apply_to_all_orders'] : true;
		$comment = isset($data['comment']) ? $data['comment'] : null;
	    
		if(!$ordergroup = $this->getOrderGroupByID($pk))
		{
			$this->setError($this->getError());
			return false;
		}
		
		$old_order_status = $ordergroup->order_status;
		
		if($apply_to_all_orders)
		{
			$ordergroup->setOrderStatus($order_status);
		}
		else
		{
			$ordergroup->set('order_status', $order_status);
		}
		
		if(isset($data['payment_received']))
		{
			$ordergroup->set('payment_received', $data['payment_received']);
		}
		
		if(isset($data['payment_refunded']))
		{
			$ordergroup->set('payment_refunded', $data['payment_refunded']);
		}		
		
		$static_comment = JText::sprintf('COM_QAZAP_ORDERGROUP_STATUS_UPDATED', QazapHelper::orderStatusNameByCode($old_order_status), QazapHelper::orderStatusNameByCode($order_status));
		
		$comment = $static_comment . ' ' . (string) $comment;

		$this->setComment($comment);
		
		if(!$this->saveOrderGroup($ordergroup, $skipLoad = true))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}

	public function updateOrderStatus($data)
	{
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		
		if(!$pk)
		{
			$this->setError('No Order group id found in the form data');
			return false;
		}
		
		$order_status = $data['order_status'];
		$order_id = $data['order_id'];
		$apply_to_all_items = isset($data['apply_to_all_items']) ? $data['apply_to_all_items'] : true;
		$comment = isset($data['comment']) ? $data['comment'] : null;

		if(!$ordergroup = $this->getOrderGroupByID($pk))
		{
			$this->setError($this->getError());
			return false;
		}
		
		if(!$order = $ordergroup->getOrderByOrderID($order_id))
		{
			$this->setError('Invalid order id');
			return false;
		}
		
		$old_order_status = $order->order_status;
		
		if($apply_to_all_items)
		{
			$order->setOrderStatus($order_status);
		}
		else
		{
			$order->set('order_status', $order_status);
		}
		
		$static_comment = JText::sprintf('COM_QAZAP_ORDER_STATUS_UPDATED', $order->order_number, QazapHelper::orderStatusNameByCode($old_order_status), QazapHelper::orderStatusNameByCode($order_status));
		
		$comment = $static_comment . ' ' . (string) $comment;

		$this->setComment($comment);
		
		if(!$this->saveOrderGroup($ordergroup, $skipLoad = true))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;		
	}
	
	
	public function updatePayments($data)
	{
		$data = (array) $data;
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		
		if(!$pk)
		{
			$this->setError('No Order group id found in the form data');
			return false;
		}
		
		$payment_received = isset($data['payment_received']) ? $data['payment_received'] : 0;
		$payment_refunded = isset($data['payment_refunded']) ? $data['payment_refunded'] : 0;

		$payment_received = str_replace(',', '', trim($payment_received));
		$payment_refunded = str_replace(',', '', trim($payment_refunded));

		if(!$ordergroup = $this->getOrderGroupByID($pk))
		{
			$this->setError($this->getError());
			return false;
		}		

		$ordergroup->set('payment_received', $payment_received);
		$ordergroup->set('payment_refunded', $payment_refunded);
		$this->setComment('COM_QAZAP_ORDERGROUP_PAYMENT_DETAILS_UPDATED');
		
		if(!$this->saveOrderGroup($ordergroup, $skipLoad = true))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;    	
	}
	
	public function updateOrderAddress($data, $address_type)
	{		
		$data = (array) $data;
		$address_type = strtolower($address_type);
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		
		if(!$pk)
		{
			$this->setError('No Order group id found in the form data');
			return false;
		}
		
		if(!in_array($address_type, array('billing_address', 'shipping_address')) || !isset($data[$address_type]))
		{
			$this->setError('Invalid address type or address data');
			return false;			
		}
		
    	if(!$ordergroup = $this->getOrderGroupByID($pk))
    	{
			$this->setError($this->getError());
			return false;
		}		
		
		$ordergroup->set($address_type, $data[$address_type]);
		//qzdump($ordergroup);exit;

		$this->setComment('COM_QAZAP_ORDERGROUP_'.$address_type.'_UPDATED');
		
		if(!$this->saveOrderGroup($ordergroup, $skipLoad = true))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;  		
	}
	
	public function updateItemQuantity($data)
	{
		$data = (array) $data;
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$config	= QZApp::getConfig();

		if(!isset($data['group_id']) || !isset($data['quantity']))
		{
			$this->setError('Invalid Data');
			return false;
		}	
		
		if(!$pk)
		{
			$this->setError('No Order group id found in the form data');
			return false;
		}
    
    	if(!$ordergroup = $this->getOrderGroupByID($pk))
    	{
			$this->setError($this->getError());
			return false;
		}	
	
		$group_id = $data['group_id'];
		$quantity = $data['quantity'];
		
		list($product_id, $attr_ids, $membership_id) = $this->groupToArray($group_id);

		if(!$in_cart = $ordergroup->getProduct($group_id))
		{
			$this->setError('Can not found the item in the order');
			return false;	
		}
		
		// Hold the old quantity in a variable
		$old_quantity = $in_cart->product_quantity;

		if(!$orderItem = QZOrderitem::getItem($product_id, $attr_ids, $membership_id, $quantity, $ordergroup->user_id))
		{
			$this->setError($orderItem->getError());
			return false;			
		}
		
		if(!$orderItem->checkQuantity())
		{	
			$this->setError($orderItem->getError());
			return false;
		}

		if($config->get('enablestockcheck', 1) && !$orderItem->checkStock($old_quantity))
		{
			$this->setError($orderItem->getError());
			return false;
		}	
		
		$orderItem->createGroup();
		
		if(!$orderItem->calculateCommission())
		{
			$this->setError($orderItem->getError());
			return false;
		}		
		
		foreach($orderItem as $key => $value)
		{
			if($value)
			{
				$in_cart->set($key, $value);
			}
		}
					
		if(!$ordergroup->calculateCart())
		{
			$this->setError($ordergroup->getError());
			return false;			
		}

		//qzdump($ordergroup);exit;

		$this->setComment(JText::sprintf('COM_QAZAP_ORDER_ITEM_QUANTITY_UPDATED', $in_cart->product_name, $old_quantity, $in_cart->product_quantity));
		
		if(!$this->saveOrderGroup($ordergroup, $skipLoad = true))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true; 		
	}
	
	
	
	public function deleteItem($data)
	{
		$data = (array) $data;
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$config	= QZApp::getConfig();

		if(!isset($data['group_id']))
		{
			$this->setError('Invalid product group id');
			return false;
		}	
		
		if(!$pk)
		{
			$this->setError('No Order group id found in the form data');
			return false;
		}
    
    	if(!$ordergroup = $this->getOrderGroupByID($pk))
    	{
			$this->setError($this->getError());
			return false;
		}	

		if(!$in_cart = $ordergroup->getProduct($data['group_id']))
		{
			$this->setError('Can not found the item in the order');
			return false;	
		}
		
		$in_cart->set('deleted', 1);
		$in_cart->set('order_status', 'D');
		
		$this->setComment(JText::sprintf('COM_QAZAP_ORDER_ITEM_DELETED', $in_cart->product_name));
		
		if(!$ordergroup->calculateCart())
		{
			$this->setError($ordergroup->getError());
			return false;			
		}		
		
		if(!$this->saveOrderGroup($ordergroup, $skipLoad = true))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;					
	}
	
	
	public function updateItemStatus($data)
	{
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		
		if(!$pk)
		{
			$this->setError('No Order group id found in the form data');
			return false;
		}
		
    	$order_status = $data['order_status'];
    	$group_id = $data['group_id'];
		$comment = isset($data['comment']) ? $data['comment'] : null;
    
    	if(!$ordergroup = $this->getOrderGroupByID($pk))
    	{
			$this->setError($this->getError());
			return false;
		}

		if(!$in_cart = $ordergroup->getProduct($group_id))
		{
			$this->setError('Can not found the item in the order');
			return false;	
		}
		
		// Capture the old status
		$old_order_status = $in_cart->order_status;
		
		// Set the new status
		$in_cart->set('order_status', $order_status);
		
		$static_comment = JText::sprintf('COM_QAZAP_ORDER_ITEM_STATUS_UPDATED', $in_cart->product_name, QazapHelper::orderStatusNameByCode($old_order_status), QazapHelper::orderStatusNameByCode($order_status));
		
		$this->setComment($static_comment . ' ' . $comment);
		
		if(!$this->saveOrderGroup($ordergroup, $skipLoad = true))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}
	
	/**
	* Method to set comments
	* 
	* @param string $comment Comments/notes
	* 
	* @return void
	*/
	public function setComment($comment)
	{
		if(!in_array($comment, $this->_comments))
		{
			$this->_comments[] = JText::_($comment);
		} 
	}

	/**
	* Method to get the comments set in an instance
	* 
	* @return mixed (string/null)
	*/
	public function getComments()
	{
		if(count($this->_comments))
		{
			return implode(' ', $this->_comments);
		}
		
		return null;
	}
	

	/**
	* Method to get History of an order group
	* 
	* @param integer $ordergroup_id Order group ID.
	* 
	* @return mixed (array/null) Object list of history
	*/	
	public function getOrderHistory($ordergroup_id = null)
	{
		$ordergroup_id = $ordergroup_id ? $ordergroup_id : (int) $this->getState($this->getName() . '.id');
		
		if(!isset($this->_history[$ordergroup_id]))
		{
			$db = $this->getDbo();
			$sql =$db->getQuery(true)
					->select('a.order_history_id, a.ordergroup_id, a.comments, a.mail_to_buyer, a.mail_to_vendor, '.
					'a.order_status, a.created_on, a.created_by')
					->from('#__qazap_order_history AS a')
					->select('b.status_name')
					->join('LEFT', '#__qazap_order_status AS b ON a.order_status = b.status_code')
					->select('c.name AS editor')
					->join('LEFT', '#__users AS c ON c.id = a.created_by')
					->where('a.ordergroup_id = '. (int) $ordergroup_id)
					->order('a.order_history_id ASC');
			try
			{
				$db->setQuery($sql);
				$results = $db->loadObjectList();					
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($results))
			{
				$this->_history[$ordergroup_id] = null;
			}
			else
			{
				$this->_history[$ordergroup_id] = $results;
			}
		}	

		return $this->_history[$ordergroup_id];
	}


	protected function saveHistory()
	{
		if(empty($this->_historyData))
		{
			$this->setError('No history data available for save.');
			return false;
		}
		
		$user = JFactory::getUser();
		$date = JFactory::getDate();
		
		// Prepare save data object
		$this->_historyData->order_history_id = property_exists($this->_historyData, 'order_history_id') ? $this->_historyData->order_history_id : 0;
		$this->_historyData->comments = $this->getComments();
		$this->_historyData->created_by = $user->get('id');
		$this->_historyData->created_on	= $date->toSQL();		
		
		try
		{
			$db = $this->getDbo();
			if($this->_historyData->order_history_id > 0)
			{
				$result = $db->updateObject('#__qazap_order_history', $this->_historyData, array('order_history_id'), false);
			}
			else
			{
				$result = $db->insertObject('#__qazap_order_history', $this->_historyData, 'order_history_id');	
			}					
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
		}

		return $result;		
	}	

	/**
	* Method to get a order group with its all orders and order items by ordergroup ID
	* 
	* @param integer $ordergroup_id	Order group id
	* 
	* @return mixed (object/false)	QazapTableOrdergroup object or false in failure
	* @since	1.0.0
	*/	
	public function getOrderGroupByID($ordergroup_id = null)
	{
		$ordergroup_id = $ordergroup_id ? $ordergroup_id : $this->getState($this->getName() . '.id');
		
		if(!isset($this->_orderGroupIDs[$ordergroup_id]))
		{
			$table = $this->getTable('Ordergroup');
			// Attempt to load the row.
			$return = $table->load($ordergroup_id);
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
			
			$this->_orderGroupIDs[$ordergroup_id] = new QZCart($table);
			$this->_orderGroupIDs[$ordergroup_id]->decodeData();
		}

		return $this->_orderGroupIDs[$ordergroup_id];
	}	
	
	/**
	* Method to get a order group with its all orders and order items by ordergroup number.
	* 
	* @param string $ordergroup_number	Order group number
	* 
	* @return mixed (object/false)	QazapTableOrdergroup object or false in failure
	* @since	1.0.0
	*/	
	public function getOrderGroupByNumber($ordergroup_number)
	{		
		if (!isset($this->_orderGroupNumbers[$ordergroup_number]))
		{
			$table = $this->getTable('Ordergroup');
			// Attempt to load the row.
			$return = $table->load(array('ordergroup_number' => $ordergroup_number));
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
			
			$this->_orderGroupNumbers[$ordergroup_number] = new QZCart($table);
			$this->_orderGroupNumbers[$ordergroup_number]->decodeData();
			
			// Also save in the internal cache with ordergroup_id
			$ordergroup_id = $this->_orderGroupNumbers[$ordergroup_number]->ordergroup_id;
			
			if(!empty($ordergroup_id))
			{
				$this->_orderGroupIDs[$ordergroup_id] = &$this->_orderGroupNumbers[$ordergroup_number];
			}			
		}
		
		return $this->_orderGroupNumbers[$ordergroup_number];
	}	
	
	/**
	* Method to save multiple orders from QZCart object
	* 
	* @param array $cart	Cart object data
	* 
	* @return boolean
	* @since	1.0.0
	*/	
	public function saveOrderGroup($data, $skipLoad = false)
	{		
		if(is_object($data))
		{
			$data = JArrayHelper::fromObject($data);
		}
		
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		$config = QZApp::getConfig();
		
		try
		{
			if($pk > 0)
			{
				if(!$skipLoad)
				{
					$table->load($pk);
				}				
				$isNew = false;
			}
			
			if($isNew)
			{
				$this->setComment(JText::_('COM_QAZAP_ORDERGROUP_NEW_ORDER_PLACED'));
			}
			
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}
			
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}
			
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			
			if(!$table->updateStocks())
			{
				$this->setError($table->getError());
				return false;			
			}
			
			$ordergroup = new QZCart($table);
			$ordergroup->decodeData();
			$comment = $this->getComments();
			
			if($config->get('downloadable'))
			{
				$fileModel = QZApp::getModel('File', array('ignore_request'=>true));
				if(!$fileModel->updateDownloadableFile($ordergroup))
				{
					$this->setError($fileModel->getError());
					return false;
				}
			}
			
			if(empty($comment))
			{
				$comment = JText::_('COM_QAZAP_ORDERGROUP_UPDATED');
			}

			$ordergroup->set('special_comments', $comment);
			
			// Save the memberships / subscriptions
			$memberModel = QZApp::getModel('Member', array('ignore_request' => true));
			$orderItems = $ordergroup->getProducts();
			if(!$memberModel->saveMembers($orderItems, $ordergroup->user_id))
			{
				$this->setError($memberModel->getError());
				return false;				
			}			
			
			// Preset history data
			$this->_historyData->ordergroup_id = $ordergroup->ordergroup_id;
			$this->_historyData->mail_to_buyer = 0;
			$this->_historyData->mail_to_vendor = 0;
			$this->_historyData->order_status = $ordergroup->order_status;	

			// Initiate mail function
			$this->sendMail($ordergroup, $isNew);		
			
			// Save ordergroup history
			if(!$this->saveHistory())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			$this->cleanCache();	
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) 
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		return $ordergroup;
	}	
		
	
	public function sendMail(QZCart $ordergroup, $isNew)
	{
		$config = QZApp::getConfig();
		$ordergroup = clone($ordergroup);
		$statuses = $ordergroup->getStatuses(true);
		$data = array();
		$app = JFactory::getApplication();
		$display_message = $app->isAdmin() ? true : false;
		$mailModel = QZApp::getModel('Mail', array('ignore_request' => true, 'display_message' => $display_message));

		$buyerMailStatuses = $config->get('order_states_shopperemail', array());
		$sendBuyerMail = array_intersect($buyerMailStatuses, $statuses);
		$sendBuyerMail = !empty($sendBuyerMail);
		
		$vendorMailStatuses = $config->get('order_states_vendoremail', array());
		$sendVendorMail = array_intersect($vendorMailStatuses, $statuses);
		$sendVendorMail = !empty($sendVendorMail);	
		
		$adminMailStatuses = $config->get('send_mail_to_admin', array());
		$sendAdminMail = array_intersect($adminMailStatuses, $statuses);
		$sendAdminMail = !empty($sendAdminMail);
		
		$invoiceStatuses = $config->get('order_states_invoice', array());	
		$sendInvoice = array_intersect($invoiceStatuses, $statuses);
		$sendInvoice = !empty($sendInvoice);
		
		if($sendBuyerMail)
		{
			$data['variables'] = $ordergroup;
			$data['invoice'] = $sendInvoice;
			if(isset($ordergroup->billing_address['email']) && $ordergroup->billing_address['email'])
			{
				$data['email'] = $ordergroup->billing_address['email'];
				if(!$mailModel->send('ordergroup', $data, $isNew))
				{
					JError::raiseWarning (1, $mailModel->getError());
				}
				else
				{
					$this->_historyData->mail_to_buyer = 1;
				}				
			}
		}
		
		if($sendAdminMail)
		{
			$data['variables'] = $ordergroup;
			$data['invoice'] = $sendInvoice;
			$data['email'] = $app->getCfg('mailfrom');
			if(!$mailModel->send('ordergroup', $data, $isNew))
			{
				JError::raiseWarning (1, $mailModel->getError());
			}				
		}
					
		if($sendVendorMail && !empty($ordergroup->vendor_carts))
		{
			if(!$this->setVendorEmails(array_keys($ordergroup->vendor_carts)))
			{
				JError::raiseWarning (1, $this->getError());
				return;				
			}
			
			foreach($ordergroup->vendor_carts as $vendor_id => $order)
			{
				$data['variables'] = array('ordergroup' => $ordergroup, 'order' => $order);
				$data['invoice'] = $sendInvoice;
				$data['email'] = $this->getVendorEmail($vendor_id); 
				
				if(!$mailModel->send('order', $data, $isNew))
				{
					JError::raiseWarning (1, $mailModel->getError());
				}
				else
				{
					$this->_historyData->mail_to_vendor = 1;
				}						
			}	
		}		

	}

	protected function setVendorEmails($vendor_ids = array())
	{
		$vendor_ids = (array) $vendor_ids;
		$vendor_ids = array_filter(array_map('intval', $vendor_ids));
		
		if(empty($vendor_ids))
		{
			$this->setError('No Vendor found');
			return false;
		}

		
		if(!empty($this->_vendor_emails))
		{
			foreach($vendor_ids as $key => &$vendor_id)
			{
				if(isset($this->_vendor_emails[$vendor_id]))
				{
					unset($vendor_ids[$key]);
				}
			}			
		}
		
		if(!empty($vendor_ids))
		{			
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select('id, email')
						->from('#__qazap_vendor');
			if(count($vendor_ids) == 1)
			{
				$query->where('id = ' . $vendor_ids[0]);
			}
			else
			{
				$query->where('id IN (' . implode(',', $vendor_ids[0]) . ')');
			}
			
			try
			{
				$db->setQuery($query);
				$result = $db->loadObjectList('id');
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
					
			$this->_vendor_emails = $this->_vendor_emails + $result;
				
		}
		
		return true;
	}
	
	protected function getVendorEmail($vendor_id)
	{
		if(isset($this->_vendor_emails[$vendor_id]))
		{
			return $this->_vendor_emails[$vendor_id]->email;
		}
		
		return null;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}
		
		$done = false;
		
		foreach($pks as $pk)
		{
			$commands['ordergroup_id'] = $pk;
			
			if(!$this->updateOrdergroupStatus($commands))
			{
				$this->setError($this->getError());
				return false;
			}
			
			$done = true;
		}
		
		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;		
	}
	
	public function getPaymentMethod($paymentmethod_id = null)
	{
		if(empty($paymentmethod_id))
		{
			$ordergroup = $this->getOrderGroupByID();
			if(empty($ordergroup) || (!isset($ordergroup->cart_payment_method_id) || empty($ordergroup->cart_payment_method_id)))
			{
				$this->setError('Invalid payment method.');
				return false;
			}
			
			$paymentmethod_id = $ordergroup->cart_payment_method_id;
		}
		
		if(empty($paymentmethod_id))
		{
			$this->setError('Invalid payment method.');
			return false;
		}
		
		if(!isset($this->_paymentMethods[$paymentmethod_id]))
		{
			$db = $this->getDbo();
			$sql = $db->getQuery(true)
					->select('a.id, a.ordering, a.state, a.payment_name, a.payment_description, a.payment_method, '.
										'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
					->from('#__qazap_payment_methods AS a')
					->select('b.element AS plugin')				
					->join('INNER', '#__extensions AS b ON a.payment_method = b.extension_id')
					->where('a.state = 1')
					->where('b.enabled = 1')
					->where('a.id = '. (int) $paymentmethod_id);	
			
			try 
			{
				$db->setQuery($sql);
				$method = $db->loadObject();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($method))
			{
				$this->_paymentMethods[$paymentmethod_id] = null;
			}
			else
			{
				$tmp = new JRegistry;
				$tmp->loadString($method->params);
				$method->params = $tmp;

				if(!empty($method->countries) && is_string($method->countries))
				{
					$method->countries = json_decode($method->countries);
				}
								
				if(!empty($method->user_group) && is_string($method->user_group))
				{
					$method->user_group = json_decode($method->user_group);
				}				
						
				$this->_paymentMethods[$paymentmethod_id] = $method;
			}						
		}
		
		return $this->_paymentMethods[$paymentmethod_id];
	}	
	
	public function getOnAdminOrderDisplay()
	{
		$ordergroup = $this->getOrderGroupByID();		
		
		if(empty($ordergroup))
		{
			return null;
		}
				
		$method = $this->getPaymentMethod();
		
		if($method === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		if($method === null)
		{
			$this->setError('Invalid payment method id passed');
			return false;
		}
		
		// set a blank html and pass that to the plugin
		$html = null;

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
				
		$results = $dispatcher->trigger('onAdminOrderDisplay', array($method, $ordergroup, &$html));
		
		if(in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
			return false;
		}
	
		return $html;		
	}
	
	/**
	 * Custom clean the cache of com_qazap, qazap modules and access control plugin cache
	 *
	 * @since   1.0.0
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_qazap');
		parent::cleanCache('com_qazap_membership_access');
		parent::cleanCache('mod_qazap_categories');
		parent::cleanCache('mod_qazap_search');
		parent::cleanCache('mod_qazap_filters');		
	}	
}