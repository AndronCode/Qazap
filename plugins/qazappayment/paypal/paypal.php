<?php
/**
 * paypal.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazappayment Paypal Plugin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

if(!class_exists('QZApp'))
{
	// Setup Qazap for autload classes
	require(JPATH_ADMINISTRATOR . '/components/com_qazap/app.php');	
	QZApp::setup();
}

class PlgQazapPaymentPaypal extends QZPaymentPlugin
{
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}
	
	/**
	* Method create new table for the plugin
	* 
	* Primary key of the table is created automatically. Check $this->_tableKey
	*
	* @return	Mixed	Array of SQL fields or false
	* @since	1.0
	*/	
	protected function getTableFields()
	{
	 $tableFields = array(
			'ordergroup_id' => 'int(21) UNSIGNED',
			'ordergroup_number' => 'char(64)',
			'paymentmethod_id' => 'int(11) UNSIGNED',
			'payment_name' => 'varchar(5000)',
			'order_total' => 'float(12,6)',
			'payment_currency' => 'smallint(1)',
			'payment_price' => 'float(12,6)',
			'payment_tax' => 'float(12,6)',
			'payment_tax_calculation' => 'enum(\'p\', \'v\')',
			'payment_context' => 'varchar(255)',
			'paypal_response_payment_status' => 'char(50)',
			'paypal_fullresponse' => 'text'			
		);
		
		return $tableFields;
	}		
	
	/**
	* This event is called when a payment method is saved.
	* 
	* @param object  $method  Payment method object. html property need to be modified for the display.
	* @param array   $data    Payment method form data
	* @param boolean $isNew   If new payment method then true else false
	* 
	* @return boolean
	*/		
	public function onMethodBeforeSave($method, &$data, $isNew)
	{
		if($method->element != $this->_name)
		{
			return;
		}
		
		if(isset($data['params']))
		{
			if(isset($data['params']['min_order_value']))
			{
				$data['params']['min_order_value'] = $this->floatOrEmpty($data['params']['min_order_value']);
			}
			if(isset($data['params']['max_order_value']))
			{
				$data['params']['max_order_value'] = $this->floatOrEmpty($data['params']['max_order_value']);
			}
			if(isset($data['params']['min_weight']))
			{
				$data['params']['min_weight'] = $this->floatOrEmpty($data['params']['min_weight']);
			}
			
			if(isset($data['params']['sandbox']) && isset($data['params']['sandbox_merchant']) && isset($data['params']['merchant'])) 
			{
				if($data['params']['sandbox'] && empty($data['params']['sandbox_merchant']))
				{
					$this->_subject->setError(JText::_('PLG_QAZAPPAYMENT_PAYPAL_ERROR_SANDBOX_MERCHANT'));
					return false;					
				}
				
				if(empty($data['params']['sandbox']) && empty($data['params']['merchant']))
				{
					$this->_subject->setError(JText::_('PLG_QAZAPPAYMENT_PAYPAL_ERROR_MERCHANT'));
					return false;					
				}				
			}				
		}		
		
		return true;
	}
	
	/**
	* This event is called to display the available payment method options. 
	* 
	* @param object $method Payment method object. display property need to be modified for the display.
	* @param object $cart   QZCart object
	* 
	* @return boolean
	*/	
	public function onDisplayPaymentMethods(&$method, QZCart $cart)
	{
		if($method->plugin != $this->_name)
		{
			return;
		}
				
		// Params in plugin variable
		$this->setParams($method->params);
				
		$params = $this->params;

		if($params->get('min_order_value') && ($cart->cart_total < (float) $params->get('min_order_value')))
		{	
			return;
		}
		
		if($params->get('max_order_value') && ($cart->cart_total > (float) $params->get('max_order_value')))
		{	
			return;
		}
		
		if($params->get('accepted_zipcodes'))
		{
			$zipcodes = $params->get('accepted_zipcodes');
			
			if(strpos($zipcodes, ',') !== false)
			{
				$zipcodes = explode(',', $zipcodes);
			}

			$zipcodes = (array) $zipcodes;
			$tmp = new JRegistry;
			$tmp->loadArray($cart->shipping_address);
			$user_zipcode = $tmp->get('zip', '');
							
			if(!empty($zipcodes) && !in_array($user_zipcode, $zipcodes))
			{
				return;
			}
		}
		
		if($params->get('min_weight') && $params->get('weight_uom'))
		{
			$min_weight = (float) $params->get('min_weight');
			$total_weight = $cart->getTotalWeight($params->get('weight_uom'));
			
			if($total_weight === false)
			{
				$cart->setError($cart->getError());
				return false;
			}

			if($total_weight > $min_weight)
			{
				return;
			}			
		}
		
		$method->display = $method->payment_name;
		$method->price = (float) $method->price;
		$method->tax = (float) $method->tax;
		
		if(!empty($method->price) || !empty($method->tax))
		{
			$method->display .= ' (';
			
			if(!empty($method->price))
			{
				$method->display .= JText::_('COM_QAZAP_PRICE') . ': ' . QZHelper::currencyDisplay($method->price);
			}		
			
			if(!empty($method->tax))
			{
				if(!empty($method->price))
				{
					$method->display .= ' + ';
				}
							
				$method->display .= JText::_('COM_QAZAP_TAX') . ': ';
				
				if($method->tax_calculation)
				{
					$method->display .= QZHelper::currencyDisplay($method->tax);
				}
				else
				{
					$method->display .= floatval($method->tax) . '%';
				}
			}
			
			$method->display .= ')';				
		}	

		// If needed you can also pass additional HTML to display with payment selection like below:
		// $method->additional_html = '<div><input type="hidden" name="additional_field_name" value="11"/></div>';

		return true;		
	}
	
	/**
	* This event is called to display the selected payment method
	* 
	* @param object $method Payment method object. html property need to be modified for the display.
	* @param object $cart   QZCart object
	* 
	* @return boolean
	*/	
	public function onSelectPaymentMethod(&$method, QZCart $cart)
	{
		if($method->plugin != $this->_name)
		{
			return;
		}

		// Calculate Price
		$this->calculatePrice($method);	
		
		$method->html = $this->getSelectedMethodDisplay($method);

		return true;	
	}
	
	/**
	* This event is called by cart model when user confirms as order
	* 
	* @param	object	$ordergroup		QZCart object of present order for which the order confirmation is requested
	* @param	object	$carModel			QazapModelCart object of cart model
	* 
	* @return	boolean	Return false in case of any error. 
	* @note		Plugin can redirect to some other pages if needed. Eg. Payment gateways for payment processing
	*/
	public function onGetOrderConfirmation(QZCart $ordergroup, QazapModelCart $cartModel)
	{
		if($this->getName($ordergroup->cart_payment_method_id) != $this->_name)
		{
			return;
		}
		
		if(!$plugin = $this->getMethod($ordergroup->cart_payment_method_id, 'payment'))
		{
			$cartModel->setError($this->getError());
			return false;			
		}

		if(!$params = $this->getParams($ordergroup->cart_payment_method_id))
		{
			$cartModel->setError($this->getError());
			return false;
		}		
		
		// Params in plugin variable
		$this->setParams($params);
		
		// Set this ordergroup in plugin variable
		$this->setOrdergroup($ordergroup);
		
		// Set this shipping address in plugin variable
		$this->setAddress($this->ordergroup->shipping_address);
		
		$processing_currency = (string) $this->params->get('processing_currency', 'order');
		$payment_currency = strtolower($processing_currency) . '_currency';
		
		// Prepare data to save
		$data = array();
		$data['ordergroup_id'] = $this->ordergroup->ordergroup_id;
		$data['ordergroup_number'] = $this->ordergroup->ordergroup_number;
		$data['paymentmethod_id'] = $this->ordergroup->cart_payment_method_id;
		$data['payment_name'] = $this->ordergroup->cart_payment_method_name;
		$data['order_total'] = $this->getValueInCurrency($this->ordergroup->cart_total, $processing_currency);
		$data['payment_currency'] = $this->ordergroup->$payment_currency;
		$data['payment_price'] = $this->getValueInCurrency($this->ordergroup->cart_paymentPrice, $processing_currency);
		$data['payment_tax'] = $this->getValueInCurrency($this->ordergroup->cart_paymentTax, $processing_currency);
		$data['payment_tax_calculation'] = $plugin->tax_calculation;
		$data['payment_context'] = $this->context; 
		
		// Get the table of PayPal plugin
		$table = $this->getTable();
		
		if(!$table->save($data))
		{
			$cartModel->setError($table->getError());
			return false;
		}
		
		if($ordergroup->cart_total <= 0)
		{
			// Change the ordergroup status
			$ordergroup->setOrderStatus($this->params->get('success_status', 'C'));
			
			// Save modified ordergroup
			if(!$cartModel->saveOrderGroup($ordergroup))
			{
				$cartModel->setError($cartModel->getError());
				return false;
			}
			
			return true;	
		}

		if($ordergroup->order_status != $this->params->get('pending_status', 'P'))
		{
			$src = array();
			$src['ordergroup_id'] = $ordergroup->ordergroup_id;
			$src['order_status'] = $this->params->get('pending_status', 'P'); 
			$src['apply_to_all_orders'] = true;
			$src['comment'] = '';
						
			// Change the ordergroup status
			if(!$cartModel->updateOrdergroupStatus($src))
			{
				$this->_subject->setError($orderModel->getError());
				return false;
			}	
		}
		
		// Get the form layout for post
		$form = $this->renderLayout(array('url' => $this->getPaymentURL(), 'data' => $this->getPostData($ordergroup)));
		
		// Set the form to cart model
		$cartModel->setPaymentForm($form);
		
		return true;
	}	
	
	/**
	* This event is called when a payment callback notification is received
	* 
	* @param object $method Payment method object. 
	* @param array  $data   Combined array of HTTP requests and posts
	* 
	* @return boolean
	*/		
	public function onRecieveNotification($method, $data)
	{
		if($method->plugin != $this->_name)
		{
			return;
		}		
		
		// Params in plugin variable
		$this->setParams($method->params);
		
		if(!isset($data['ordergroup_id']))
		{
			$this->logDebug('No ordergroup id found in the data', 'onRecieveNotification ERROR');
			return false;
		}
		
		$orderModel = QZApp::getModel('order', array('ignore_request' => true));
		
		if(!isset($data['invoice'])) 
		{
			$this->logDebug('No PayPal Invoice Number.', 'onRecieveNotification');
			return false;
		}		

		$ordergroup_number = $data['invoice'];
		
		if (!($ordergroup = $orderModel->getOrderGroupByNumber($ordergroup_number))) 
		{
			$this->logDebug('Invalid PayPal Invoice. Ordergroup Number: ' . $ordergroup_number, 'onRecieveNotification ERROR');
			return false;
		}
		
		if($data['ordergroup_id'] != $ordergroup->ordergroup_id)
		{
			$this->logDebug('PayPal Invoice: ' . $ordergroup_number . ' does not match with Ordergroup ID: ' . $data['ordergroup_id'], 'onRecieveNotification ERROR');
			return false;			
		}

		$payments = $this->getDataByOrdergroupID($ordergroup->ordergroup_id);
		
		if($payments === false)
		{
			$this->logDebug('No payment data (getDataByOrdergroupID) found against ordergroup ID: ' . $ordergroup->ordergroup_id, 'onRecieveNotification ERROR');
			return false;
		}
		
		// Set this ordergroup in plugin variable
		$this->setOrdergroup($ordergroup);

		$result = $this->processIPN($data, $payments);
		
		if(empty($result))
		{
			$this->logDebug('IPN processing failed (processIPN)', 'onRecieveNotification ERROR');
			return false;
		}
		
		$payment = current($payments);
		$pk = $this->_tableKey;
		
		if(!$this->savePayment($payment->$pk, $ordergroup->ordergroup_id, $data))
		{
			$this->logDebug('Payment data save failed (savePayment). Error message: ' . $this->getError(), 'onRecieveNotification ERROR');
			return false;			
		}
		
		$src = array();
		$src['ordergroup_id'] = $ordergroup->ordergroup_id;
		$src['apply_to_all_orders'] = true;
		$src['order_status'] = $result['order_status'];
		$src['comment'] = $result['comment'];
				
		if(isset($result['payment_received']) && $result['payment_received'] !== null)
		{
			$src['payment_received'] = $result['payment_received'];
		}
		elseif(isset($result['payment_refunded']) && $result['payment_refunded'] !== null)
		{
			$src['payment_refunded'] = $result['payment_refunded'];
		}
		
		$this->logDebug($src, 'updateOrdergroupStatus save data');
		
		if(!$orderModel->updateOrdergroupStatus($src))
		{
			$this->logDebug(array('result' => $src, 'error_message' => $orderModel->getError()), 'updateOrdergroupStatus save failed ERROR');
			return false;				
		}
		
		// Clear the cart for this payment
		if(isset($data['custom'])) 
		{
			$cartModel = QZApp::getModel('cart', array('ignore_request' => true), false);
			$cartModel->emptyCart($data['custom']);
		}
		
		return true;
	}

	/**
	* This event is called when a payment callback response is received
	* 
	* @param object  $method      Payment method object. 
	* @param array   $data        Combined array of HTTP requests and posts
	* @param object  $ordergroup  QZCart order group object needs to be returned for cart confirmed page display.
	* @param boolean $success     Boolean true value needs to be returned if the order group was process against this reponse.
	* 
	* @return boolean
	*/		
	public function onRecieveResponse($method, $data, &$ordergroup, &$success)
	{
		if($method->plugin != $this->_name)
		{
			return;
		}	
		
		// Params in plugin variable
		$this->setParams($method->params);
		
		if(!isset($data['ordergroup_id']))
		{
			return;
		}
		
		$cartModel = QZApp::getModel('cart', array('ignore_request' => true), false);
		
		if(!$ordergroup = $cartModel->getOrderGroupByID($data['ordergroup_id']))
		{
			$this->_subject->setError($cartModel->getError());
			return false;
		}
		
		// Set this ordergroup in plugin variable
		$this->setOrdergroup($ordergroup);
				
		$payments = $this->getDataByOrdergroupID($ordergroup->ordergroup_id);
		
		if($payments === false)
		{
			$this->_subject->setError($this->getError());
			return false;
		}
		
		if(empty($payments))
		{
			return;
		}
		
		$payment = end($payments);
		
		// Check if this is the active session order
		if(strcmp($payment->payment_context, $this->context) !== 0) 
		{
			return;
		}
		
		if(!empty($payment->paypal_fullresponse) && is_string($payment->paypal_fullresponse))
		{
			$data = (object) json_decode($payment->paypal_fullresponse);
			$success = isset($data->payment_status) ? ($data->payment_status == 'Completed' || $data->payment_status == 'Pending') : false;
		}
		else
		{
			$success = false;
		}
		
		return true;
	}
	
	/**
	* This event is called when a payment cancel callback notification is received
	* 
	* @param object   $method Payment method object. 
	* @param array    $data   Combined array of HTTP requests and posts
	* 
	* @return boolean Return true if the order is successfully cancelled or false in case of error.
	*/
	public function onPaymentCancel($method, $data)
	{
		if($method->plugin != $this->_name)
		{
			return;
		}	
		
		// Params in plugin variable
		$this->setParams($method->params);
		
		if(!isset($data['ordergroup_id']))
		{
			return;
		}
		
		$cartModel = QZApp::getModel('cart', array('ignore_request' => true), false);
		
		if(!$ordergroup = $cartModel->getOrderGroupByID($data['ordergroup_id']))
		{
			$this->_subject->setError($cartModel->getError());
			return false;
		}
		
		// Set this ordergroup in plugin variable
		$this->setOrdergroup($ordergroup);
				
		$payments = $this->getDataByOrdergroupID($ordergroup->ordergroup_id);
		
		if($payments === false)
		{
			$this->_subject->setError($this->getError());
			return false;
		}
		
		if(empty($payments))
		{
			return;
		}
		
		$payment = end($payments);
		
		// Check if this is the active session order
		if(strcmp($payment->payment_context, $this->context) !== 0) 
		{
			return;
		}

		$src = array();
		$src['ordergroup_id'] = $ordergroup->ordergroup_id;
		$src['order_status'] = $this->params->get('cancel_status', 'X');
		$src['apply_to_all_orders'] = true;
		$src['comment'] = JText::_('PLG_QAZAPPAYMENT_PAYPAL_PAYMENT_CANCELLED_BY_BUYER');				
		 
		// Save modified ordergroup
		if(!$cartModel->updateOrdergroupStatus($src))
		{
			$this->_subject->setError($cartModel->getError());
			return false;
		}
		
		return true;
	}
	
	public function onAdminOrderDisplay($method, $ordergroup, &$html)
	{
		if($method->plugin != $this->_name)
		{
			return;
		}
		
		// Params in plugin variable
		$this->setParams($method->params);
		
		$payments = $this->getDataByOrdergroupID($ordergroup->ordergroup_id);
		
		if($payments === false)
		{
			$this->_subject->setError($this->getError());
			return false;
		}
		
		if(empty($payments))
		{
			return;
		}
		
		$payment = current($payments);
		$data = null;
		
		if(!empty($payment->paypal_fullresponse) && is_string($payment->paypal_fullresponse))
		{
			$payment->paypal_fullresponse = (object) json_decode($payment->paypal_fullresponse);
			$data = $payment->paypal_fullresponse;
		}
		
		if(empty($data))
		{
			return;
		}
		
		// Get the layout for display
		$html = $this->renderLayout(array('data' => $data, 'payment' => $payment, 'ordergroup' => $ordergroup), $layout = 'paymentinfo');
		
		return true;	
	}

	/**
	* PayPal plugin internal method to prepare post data
	* 
	* @param  object  $ordergroup QZCart object
	* 
	* @return array  Return payment form data array.
	*/	
	protected function getPostData($ordergroup)
	{
		$data = array();
		$processing_currency = (string) $this->params->get('processing_currency', 'order');
		
		$data['charset'] = 'utf-8';
		$data['cmd'] = '_ext-enter';
		$data['redirect_cmd'] = '_cart';
		$data['paymentaction'] = strtolower($this->params->get('payment_action', 'sale'));
		$data['upload'] = '1';
		$data['business'] = $this->getMerchant();
		//$data['receiver_email'] = $this->merchant_email; //Primary email address of the payment recipient (i.e., the merchant
		$data['order_number'] = $this->ordergroup->ordergroup_number;
		$data['invoice'] = $this->ordergroup->ordergroup_number;
		$data['custom'] = $this->context;
		$data['currency_code'] = $this->getCurrencyCode($processing_currency);
		
		if($this->address->get('first_name', ''))
		{
			$data['first_name'] = $this->address->get('first_name', '');
		}		
		
		if($this->address->get('last_name', ''))
		{
			$data['last_name'] = $this->address->get('last_name', '');
		}
		
		if($this->address->get('address_1', ''))
		{
			$data['address1'] = $this->address->get('address_1', '');
		}
		
		if($this->address->get('address_2', ''))
		{
			$data['address2'] = $this->address->get('address_2', '');
		}
		
		if($this->address->get('zip', ''))
		{
			$data['zip'] = $this->address->get('zip', '');
		}
		
		if($this->address->get('city', ''))
		{
			$data['city'] = $this->address->get('city', '');
		}			
		
		$country_id = $this->address->get('country', 0);
		$state_id = $this->address->get('states_territory', 0);
		
		if($state_id)
		{
			$data['state'] = QZHelper::getStateByID($state_id, 'state_2_code');
		}		
		
		if($country_id)
		{
			$data['country'] = QZHelper::getCountryByID($country_id, 'country_2_code');
		}		
		
		$data['email'] = $this->getBuyerEmail();
		
		if($this->address->get('phone', ''))
		{
			$data['night_phone_b'] = $this->address->get('phone', '');
		}		
		
		// Add the products
		$products = $ordergroup->getProducts();
		
		if(count($products))
		{
			$i = 1;
			
			foreach($products as $product)
			{
				$data['item_name_' . $i] = $this->getItemName($product->product_name);
				if ($product->product_sku) 
				{
					$data['item_number_' . $i] = $product->product_sku;
				}
				$data['amount_' . $i] = $this->getValueInCurrency($product->product_totalprice, $processing_currency);
				$data['quantity_' . $i] = $product->product_quantity;
				
				$i++;				
			}	
		}
		
		$taxAmount = $ordergroup->getTaxAmount();
		$data['tax_cart'] = $this->getValueInCurrency($taxAmount, $processing_currency);	
		
		$discountAmount = $ordergroup->getDiscountAmount();		
		$data['discount_amount_cart'] = $this->getValueInCurrency($discountAmount, $processing_currency);
		
		$handlingCost = $ordergroup->getHandlingCost();
		$data['handling_cart'] = $this->getValueInCurrency($handlingCost, $processing_currency);			

		$data['return'] =  $this->getURL('paymentresponse');
		$data['notify_url'] = $this->getURL('notify');
		$data['cancel_return'] = $this->getURL('paymentcancel');

		//$data['undefined_quantity'] = "0";
		$data['test_ipn'] = (string) $this->params->get('sandbox', 0);
		// the buyer’s browser is redirected to the return URL by using the POST method, and all payment variables are included
		$data['rm'] = '2'; 
		//$data['bn'] = self::BNCODE;

		$data['no_shipping'] = '1';
		$data['no_note'] = '1';

		if($this->params->get('cpp_header_image')) 
		{
			$data['cpp_header_image'] = $this->params->get('cpp_header_image');
		} 

		if ($this->params->get('cpp_headerborder_color')) 
		{
			$cpp_headerborder_color = $this->params->get('cpp_header_image');
			$data['cpp_headerborder_color'] = str_replace('#', '', strtoupper($cpp_headerborder_color));
		}
		
		if ($this->params->get('cpp_headerback_color'))
		{
			$cpp_headerback_color = $this->params->get('cpp_headerback_color');
			$data['cpp_headerback_color'] = str_replace('#', '', strtoupper($cpp_headerback_color));
		}		

		return $data;		
	}
	
	protected function getItemName($name)
	{
		return substr(strip_tags($name), 0, 127);
	}
	
	protected function getMerchant()
	{
		$sandbox = $this->params->get('sandbox', 0);
		
		if($sandbox)
		{
			$merchant = $this->params->get('sandbox_merchant');
		}
		else
		{
			$merchant = $this->params->get('merchant');
		}
		
		return trim($merchant);
	}
	
	protected function getURL($task)
	{
		$url  = JURI::base();
		$url .= 'index.php?option=com_qazap&view=callback';
		$url .= '&task=' . (string) $task;
		$url .= '&paymentmethod_id=' . $this->ordergroup->cart_payment_method_id;
		$url .= '&ordergroup_id=' . $this->ordergroup->ordergroup_id;
		$url .= '&Itemid=' . $this->app->input->getInt('Itemid');
		$url .= '&lang=' . $this->app->input->getCmd('lang');
		
		return $url;	
	}	
	
	/**
	 * Gets the form action URL for the payment
	 */
	protected function getPaymentURL()
	{
		$sandbox = $this->params->get('sandbox', 0);
		
		if($sandbox) 
		{
			return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		} 
		else 
		{
			return 'https://www.paypal.com/cgi-bin/webscr';
		}
	}

	protected function savePayment($pk, $ordergroup_id, $data)
	{
		$src = array();
		$src[$this->_tableKey] = $pk;		
		$data = (array) $data;
		
		if(!empty($data)) 
		{
			$src['paypal_fullresponse'] = json_encode($data);
		}
				
		if(isset($data['PAYMENTINFO_0_PAYMENTSTATUS'])) 
		{
			$src['paypal_response_payment_status'] = $data['PAYMENTINFO_0_PAYMENTSTATUS'];
		} 
		elseif(isset($data['PAYMENTSTATUS'])) 
		{
			$src['paypal_response_payment_status'] = $data['PAYMENTSTATUS'];
		} 
		elseif(isset($data['PROFILESTATUS'])) 
		{
			$src['paypal_response_payment_status'] = $data['PROFILESTATUS'];
		} 
		elseif(isset($data['STATUS'])) 
		{
			$src['paypal_response_payment_status'] = $data['STATUS'];
		}
		
		if(isset($data['invoice']))
		{
			$src['ordergroup_number'] = $data['invoice'];
		}
		
		if(isset($data['invoice']))
		{
			$src['paypal_response_invoice'] = $data['invoice'];
		}		
		
		if (isset($data['custom'])) 
		{
			$src['payment_context'] = $data['custom'];
		}
		
		$src['ordergroup_id'] = $ordergroup_id;
		
		return parent::save($src);
	}

	/**
	* Method to check and process PayPal IPN reponse
	* 
	* @param array $paypal_data  PayPal IPN data
	* @param array $payments     Respective internal payment data
	* 
	* @source https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
	* 
	* The status of the payment:
	* Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
	* Completed: The payment has been completed, and the funds have been added successfully to your account balance.
	* Created: A German ELV payment is made using Express Checkout.
	* Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
	* Expired: This authorization has expired and cannot be captured.
	* Failed: The payment has failed. This happens only if the payment was made from your customer’s bank account.
	* Pending: The payment is pending. See pending_reason for more information.
	* Refunded: You refunded the payment.
	* Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
	* Processed: A payment has been accepted.
	* Voided: This authorization has been voided.
	*/

	protected function processIPN($paypal_data, $payments)
	{
		if(!$this->checkIPAddress($paypal_data)) 
		{
			return false;
		}

		if(!$this->validateIPN($paypal_data)) 
		{
			return false;
		}
		
		$order = array();
		$order['order_status'] = null;
		$order['comment'] = null;
		$order['payment_refunded'] = null;
		$order['payment_received'] = null;
		
		if($paypal_data['txn_type'] == 'subscr_cancel') 
		{
			$order['order_status'] = $this->params->get('cancel_status', 'X');
		} 
		elseif($paypal_data['txn_type'] == 'mp_cancel') 
		{
			$order['order_status'] = $this->params->get('cancel_status', 'X');
		} 
		elseif ($paypal_data['txn_type'] == 'subscr_eot') 
		{
			$order['order_status'] = $this->params->get('cancel_status', 'X');
		} 
		elseif($paypal_data['txn_type'] == 'recurring_payment_expired') 
		{
			$order['order_status'] = $this->params->get('cancel_status', 'X');
		} 
		elseif($paypal_data['txn_type'] == 'subscr_signup') 
		{
			$order['order_status'] = $this->params->get('success_status', 'C');
		} 
		elseif($paypal_data['txn_type'] == 'recurring_payment_profile_created') 
		{
			if ($paypal_data['profile_status'] == 'Active') 
			{
				$order['order_status'] = $this->params->get('success_status', 'C');
			} 
			else 
			{
				$order['order_status'] = $this->params->get('cancel_status', 'X');
			}
		} 
		else 
		{
			if(strcmp($paypal_data['payment_status'], 'Completed') == 0) 
			{
				$this->logDebug('status: Completed', 'processIPN');
				
				if(!$this->checkIPN($paypal_data, $payments, $this->params->get('success_status', 'C')))
				{
					return false;
				}
				
				// Now we can process the payment
				if(strcmp($paypal_data['payment_status'], 'Authorization') == 0) 
				{
					$order['order_status'] = $this->params->get('pending_status', 'P');
				} 
				else 
				{
					$order['order_status'] = $this->params->get('success_status', 'C');
					$order['payment_received'] = $this->getAmountInOrderCurrency($paypal_data['mc_gross'], $this->params->get('processing_currency', 'order'));
				}
				
				$order['comment'] = JText::_('PLG_QAZAPPAYMENT_PAYPAL_PAYMENT_CONFIRMED_BY_PAYPAL');

			} 
			elseif(strcmp($paypal_data['payment_status'], 'Pending') == 0) 
			{
				if(!$this->checkIPN($paypal_data, $payments, $this->params->get('pending_status', 'P')))
				{
					return false;
				}
								
				$key = 'PLG_QAZAPPAYMENT_PAYPAL_PAYMENT_PENDING_REASON_' . strtoupper($paypal_data['pending_reason']);				
				$order['comment'] = JText::_($key);
				$order['order_status'] = $this->params->get('pending_status', 'P');
			} 
			elseif(strcmp($paypal_data['payment_status'], 'Refunded') == 0) 
			{
				$balance = $this->getBalanceAfterRefund($payments, $paypal_data);
				
				if(empty($balance)) 
				{
					if(!$this->checkIPN($paypal_data, $payments, $this->params->get('refund_status', 'R')))
					{
						return false;
					}
										
					$order['comment'] = JText::_('PLG_QAZAPPAYMENT_PAYPAL_PAYMENT_REFUNDED');
					$order['order_status'] = $this->params->get('refund_status', 'R');
					$order['payment_refunded'] = $this->getAmountInOrderCurrency($paypal_data['mc_gross'], $this->params->get('processing_currency', 'order')) * -1;
				} 
				else 
				{
					$order['comment'] = JText::sprintf('PLG_QAZAPPAYMENT_PAYPAL_PAYMENT_PARTIAL_REFUNDED');
					$order['order_status'] = $this->params->get('partial_refund_status', 'R');
					$order['payment_refunded'] = $this->getAmountInOrderCurrency($paypal_data['mc_gross'], $this->params->get('processing_currency', 'order')) * -1;
				}

			} 
			elseif(isset($paypal_data['payment_status'])) 
			{
				if(!$this->checkIPN($paypal_data, $payments, $this->params->get('cancel_status', 'X')))
				{
					return false;
				}
								
				$order['order_status'] = $this->params->get('cancel_status', 'X');
			} 
			else 
			{
				/*
				* A notification was received that concerns one of the payment (since $paypal_data['invoice'] is found in our table),
				* but the IPN notification has no $paypal_data['payment_status']
				* We just log the info in the order, and do not change the status, do not notify the customer
				*/
				$order['comment'] = JText::_('PLG_QAZAPPAYMENT_PAYPAL_IPN_NOTIFICATION_RECEIVED');
			}
		}
		
		return $order;			
	}
	
	protected function checkIPN($paypal_data, $payments, $order_status)
	{
		// Check that txn_id has not been previously processed for this order status
		if($this->isAlreadyProcessed($payments, $paypal_data['txn_id'], $order_status)) 
		{
			$this->logDebug($paypal_data['txn_id'] . ' is already processed', 'processIPN');
			return false;
		}
		
		// Check for valid merchant
		$merchant = $this->getMerchant();
		if(strcasecmp($paypal_data['business'], $merchant) != 0)
		{
			$error = array('paypal_data' => $paypal_data, 'merchant_email' => $this->merchant_email);
			$this->logDebug($error, 'processIPN wrong merchant ERROR');
			return false;			
		}		
		
		// Check amount is correct
		if($paypal_data['txn_type'] != 'recurring_payment' && !$this->checkPaymentAmount($payments, $paypal_data)) 
		{
			return false;
		}
		
		return true;
	}
	
	protected function isAlreadyProcessed($payments, $txn_id, $order_status)
	{
		if($this->ordergroup->order_status == $order_status) 
		{
			foreach($payments as $payment) 
			{
				if(!empty($payment->paypal_fullresponse) && is_string($payment->paypal_fullresponse))
				{
					$data = (object) json_decode($payment->paypal_fullresponse);
								
					if(isset($data->txn_id) && ($data->txn_id == $txn_id)) 
					{
						return true;
					}					
				}
			}
		}
		
		return false;		
	}
	
	protected function checkPaymentAmount($payments, $paypal_data)
	{
		$payment = current($payments);
		$currency = $this->getCurrencyCode($payment->payment_currency);
		$result = false;
		
		if($paypal_data['txn_type'] == 'cart') 
		{
			if(abs(($payments->order_total - $paypal_data['mc_gross']) < abs($paypal_data['mc_gross'] * 0.001)) && ($currency == $paypal_data['mc_currency'])) 
			{
				$result = true;
			}			
		}
		elseif (($payment->order_total == $paypal_data['mc_gross']) && ($currency == $paypal_data['mc_currency'])) 
		{
			$result = true;			
		}
		
		if(!$result) 
		{
			$error = array(
				"paypal_data" => $paypal_data,
				'payment_order_total' => $payment->order_total,
				'currency_code_3' => $currency
			);
			
			$this->logDebug($error, 'checkPaymentAmount invalid amount or currency ERROR');
		}
		
		return $result;
	}
	/**
	* Check for valid PayPal ID Address
	* @param array $paypal_data PayPal IPN data recieved
	* 
	* @return boolean true if valid
	*/
	protected function checkIPAddress($paypal_data) 
	{
		$sandbox = $this->params->get('sandbox', 0);
		$ordergroup_number = $paypal_data['invoice'];

		// Get the list of IP addresses for www.paypal.com and notify.paypal.com
		if ($sandbox) 
		{
			$paypal_iplist = gethostbynamel('ipn.sandbox.paypal.com');
			$paypal_iplist = (array) $paypal_iplist;
			$this->logDebug($paypal_iplist, 'checkIPAddress SANDBOX');
		} 
		else 
		{
			$paypal_iplist1 = gethostbynamel('www.paypal.com');
			$paypal_iplist2 = gethostbynamel('notify.paypal.com');
			$paypal_iplist3 = array('216.113.188.202', '216.113.188.203', '216.113.188.204', '66.211.170.66');
			$paypal_iplist = array_merge($paypal_iplist1, $paypal_iplist2, $paypal_iplist3);

			// Current PayPal IP Addresses
			
			//------------api.paypal.com---------
			$paypal_iplist_api = array(
				'173.0.88.66', '173.0.88.98', '173.0.84.66', '173.0.84.98', '173.0.80.00', 
				'173.0.80.01', '173.0.80.02', '173.0.80.03', '173.0.80.04', '173.0.80.05', 
				'173.0.80.06', '173.0.80.07', '173.0.80.08', '173.0.80.09', '173.0.80.10', 
				'173.0.80.11', '173.0.80.12', '173.0.80.13', '173.0.80.14', '173.0.80.15',
				'173.0.80.16', '173.0.80.17', '173.0.80.18', '173.0.80.19', '173.0.80.20'
			);
			
			//------------api-aa.paypal.com------------
			$paypal_iplist_api_aa = array(
				'173.0.88.67', '173.0.88.99', '173.0.84.99', '173.0.84.67'
			);
			
			//------------api-3t.paypal.com------------
			$paypal_iplist_api_3t_aa = array(
				'173.0.88.69', '173.0.88.101', '173.0.84.69', '173.0.84.101'
			);
			
			//------------api-aa-3t.paypal.com------------
			$paypal_iplist_api_aa_3t = array(
				'173.0.88.68', '173.0.88.100', '173.0.84.68', '173.0.84.100'
			);
			
			//------------notify.paypal.com (IPN delivery)------------
			$paypal_iplist_notify = array(
				'173.0.81.1', '173.0.81.33'
			);
			
			//-----------reports.paypal.com-----------
			$paypal_iplist_reports = array(
				'66.211.168.93', '173.0.84.161', '173.0.84.198', '173.0.88.161',
				'173.0.88.198'
			);
			
			//------------www.paypal.com------------
			// Starting September 12, 2012 www.paypal.com will start resolving to a dynamic list of IP addresses and as such should not be whitelisted.
			// For more information on IPNs please go here.
			
			//------------ipnpb.paypal.com------------
			$paypal_iplist_ipnb = array(
				'64.4.240.0', '64.4.240.1', '64.4.240.2', '64.4.240.3',
				'64.4.240.4', '64.4.240.5', '64.4.240.6', '64.4.240.7',
				'64.4.240.8', '64.4.240.9', '64.4.240.10', '64.4.240.11',
				'64.4.240.12', '64.4.240.13', '64.4.240.14', '64.4.240.15',
				'64.4.240.16', '64.4.240.17', '64.4.240.18', '64.4.240.19',
				'64.4.240.20', '118.214.15.186', '118.215.103.186', '118.215.119.186',
				'118.215.127.186', '118.215.15.186', '118.215.151.186', '118.215.159.186',
				'118.215.167.186', '118.215.199.186', '118.215.207.186', '118.215.215.186',
				'118.215.231.186', '118.215.255.186', '118.215.39.186', '118.215.63.186',
				'118.215.7.186', '118.215.79.186', '118.215.87.186', '118.215.95.186',
				'202.43.63.186', '69.192.31.186', '72.247.111.186', '88.221.43.186',
				'92.122.143.186', '92.123.151.186', '92.123.159.186', '92.123.163.186',
				'92.123.167.186', '92.123.179.186', '92.123.183.186'
			);

			$paypal_iplist = array_merge(
				$paypal_iplist, $paypal_iplist_api, $paypal_iplist_api_aa, 
				$paypal_iplist_api_3t_aa, $paypal_iplist_api_aa_3t, 
				$paypal_iplist_notify, $paypal_iplist_ipnb
			);

			$this->logDebug($paypal_iplist, 'checkIPAddress PRODUCTION');
		}
		
		$this->logDebug($_SERVER['REMOTE_ADDR'], 'checkIPAddress REMOTE ADDRESS');

		//  Test if the remote IP connected here is a valid IP address
		if (!in_array($_SERVER['REMOTE_ADDR'], $paypal_iplist)) 
		{
			$text = 
				"Error with REMOTE IP ADDRESS = " . $_SERVER['REMOTE_ADDR'] . 
				". The remote address of the script posting to this notify script ".
				"does not match a valid PayPal IP address\n".
        "These are the valid IP Addresses: " . implode(",", $paypal_iplist) . 
        "The Order Group Number received was: " . $ordergroup_number;
        
      $this->logDebug($text, 'checkIPAddress ERROR');
			return false;
		}

		return true;
	}
	
	/**
	* Validate PayPal IPN
	* 
	* @param array $paypal_data PayPal IPN data recieved
	* 
	* @return boolean true if valid
	*/
	protected function validateIPN($paypal_data) 
	{
		$test_ipn = (array_key_exists('test_ipn', $paypal_data)) ? $paypal_data['test_ipn'] : 0;
		
		$sandbox = $this->params->get('sandbox', 0);
		$hostname = $sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';
		
		// Paypal wants to open the socket in SSL
		$url = 'ssl://'.$hostname;
		$port = 443;
		
		// read the post from PayPal system and add 'cmd'
		$post = 'cmd=_notify-validate';
		
		if (function_exists('get_magic_quotes_gpc')) 
		{
			$get_magic_quotes_exists = true;
		}
		
/*		$internal_data = JRequest::get('GET', 2);
		$internal_keys = array_keys($internal_data);
		$paypal_data = JRequest::get('POST', 2);*/
		
		foreach($paypal_data as $key => &$value)
		{
			if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) 
			{
				$value = str_replace('\r\n', "QQLINEBREAKQQ", $value);
				$value = urlencode(stripslashes($value));
				$value = str_replace("QQLINEBREAKQQ", "\r\n", $value);
			} 
			else 
			{
				$value = urlencode($value);
			}
			
			$post .= "&$key=$value";
		}


		$header  = "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "User-Agent: PHP/" . phpversion() . "\r\n";
		$header .= "Referer: " . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . @$_SERVER['QUERY_STRING'] . "\r\n";
		$header .= "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\r\n";
		$header .= "Host: " . $hostname . ":" . $port . "\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($post) . "\r\n";
		$header .= "Connection: close\r\n\r\n";

		$fps = fsockopen($url, $port, $errno, $errstr, 30);
		$valid = false;
		
		if (!$fps) 
		{			
			$this->logDebug(array('error_message' => $errstr, 'error_number' => $errno), 'validateIPN ERROR');
		} 
		else 
		{
			$return = fputs($fps, $header . $post);
			
			if ($return === false) 
			{
				$this->logDebug(array('FPUTS' => 'false'), 'validateIPN ERROR');
				return false;
			}
			
			$res = '';
			while (!feof($fps)) 
			{
				$res .= fgets($fps, 1024);
			}
			
			fclose($fps);

			// Inspect IPN validation result and act accordingly
			$valid = stristr($res, 'VERIFIED');
			
			if (!$valid) 
			{
				if (stristr($res, 'INVALID')) 
				{
					$error = array('paypal_data' => $paypal_data, 'post' => $post, 'result' => $res);
					$this->logDebug($error, 'validateIPN Validation ERROR');
				} 
				else 
				{
					$this->logDebug('No reponse received from PayPal', 'validateIPN Validation ERROR');
				}
			}
		}
		
		$this->logDebug('Valid IPN: ' . ($valid === false) ? 'No' : 'Yes', 'validateIPN');
		
		return $valid;
	}
	
	/**
	* Method to log bebug information
	* 
	* @param string/array $data Data to be logged
	* @param string       $type Type of the Log
	* 
	* @return void
	*/
	protected function logDebug($data, $type = null)
	{
		if($this->params->get('debug', 0))
		{
			return parent::logDebug($data, $type);
		}
	}
}
