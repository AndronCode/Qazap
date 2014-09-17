<?php
/**
 * cart.php
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

class QZCart extends QZObject
{	
	public $ordergroup_id = 0;
	public $ordergroup_number = null;
	public $vendor_carts = array();

	public $order_currency = null;
	public $user_currency = null;
	public $currency_exchange_rate = null;
	
	public $cart_payment_method_id = null;
	public $cart_payment_method_html = null;
	public $cart_payment_method_name = null;
	public $cart_paymentTax = 0;
	public $cart_paymentPrice = 0;		
	
	public $cart_shipment_method_id = null;
	public $cart_shipment_method_html = null;
	public $cart_shipment_method_name = null;
	public $cart_shipmentTax = 0;
	public $cart_shipmentPrice = 0;
	
	public $coupon_discount = null;
	public $coupon_code = null;
	
	public $cart_total = null;	
	public $payment_received = 0;
	public $payment_refunded = 0;
	public $order_status = null;
	public $customer_note = null;
	public $special_comments = null;
	public $ip_address = null;
	public $billing_address = null;
	public $shipping_address = null;
	public $user_id = null;
	public $access_key = null;
	public $tos_accept = null;
	public $language = null;
	public $created_on = null;
	public $created_by = null;
	public $modified_on = null;
	public $modified_by = null;
	
		
	protected $_tax = null;
	protected $_discount = null;
	protected $_handling = null;
	protected $_commission = null;
	protected $_item_count = null;
	
		
	public function __construct($properties = null) 
	{
		parent::__construct($properties);
		
		if ($properties)
		{
			$this->setProperties($properties);
		}
		
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as &$vendor_cart)
			{
				$vendor_cart = new QZOrder($vendor_cart);
			}
		}
		
		$this->setCartStates();
	}
	
	public function setCartStates()
	{
		$app = JFactory::getApplication();
		$config = QZApp::getConfig();		
		
		if(!$this->ordergroup_id)
		{
			$user = JFactory::getUser();
			$this->user_id = (int) $user->get('id');
			
			$this->order_status = $config->get('default_order_status', 'P');		
			$this->ip_address = QZApp::getUserIP();
			$this->order_currency = $config->get('default_currency', 111);
			$this->user_currency = QZHelper::getDisplayCurrency();
			$this->currency_exchange_rate = QZHelper::getExchangeRate($this->order_currency, $this->user_currency);	
			
			if(count($this->vendor_carts))
			{
				foreach($this->vendor_carts as $vendor_cart)
				{
					$vendor_cart->set('user_id', $this->user_id);
					$vendor_cart->set('order_status', $this->order_status);
				}
			}					
		}			
	}
	
	public function createOrderGroup($forceCreate = false)
	{
		if(empty($this->ordergroup_number) || $forceCreate)
		{
			$today = date("Ymd-His");
			$rand = strtoupper(substr(uniqid(sha1(time())), 0, 6));
			$this->ordergroup_number = $today . '-' . $rand;
		}
	}
	
	public function createAccessKey($forceCreate = false)
	{
		if(empty($this->access_key) || $forceCreate)
		{
			if(!empty($this->billing_address) && isset($this->billing_address['email']))
			{
				$this->access_key = md5(uniqid($this->billing_address['email'], true));
			}
			else
			{
				$this->access_key = md5(uniqid(rand(), true));
			}
		}
	}
	
	public function setLanguage($language = null)
	{
		$lang = JFactory::getLanguage();
		$this->language = !empty($language) ? $language : $lang->getTag();
	}
	
	public function addProduct($product)
	{		
		if(!$product)
		{
			$this->setError(JText::_('No product data available'));
			return false;
		}
		
		if(!isset($this->vendor_carts[$product->vendor]))
		{
			$this->vendor_carts[$product->vendor] = new QZOrder();
			$this->vendor_carts[$product->vendor]->set('vendor', $product->vendor);
			$this->vendor_carts[$product->vendor]->set('shop_name', $product->shop_name);
			$this->vendor_carts[$product->vendor]->set('user_id', $product->user_id);
		}
		
		if(!$this->vendor_carts[$product->vendor]->setProduct($product))
		{
			$this->setError($this->vendor_carts[$product->vendor]->getError());
			return false;
		}

		return true;
	}
	
	public function removeProduct($vendor, $group_id)
	{
		if(isset($this->vendor_carts[$vendor]) && !$this->vendor_carts[$vendor]->removeProduct($group_id))
		{
			$this->setError($this->vendor_carts[$vendor]->getError());
			return false;			
		}
		
		if(!count($this->vendor_carts[$vendor]->products))
		{
			unset($this->vendor_carts[$vendor]);
		}
		
		if(!count($this->vendor_carts))
		{
			$this->cart_payment_method_id = NULL;
			$this->cart_payment_method_html = NULL;
			$this->cart_payment_method_name = NULL;
			$this->cart_paymentPrice = 0;
			$this->cart_paymentTax = 0;
			$this->cart_shipment_method_id = NULL;
			$this->cart_shipment_method_html = NULL;
			$this->cart_shipment_method_name = NULL;
			$this->cart_shipmentPrice = 0;
			$this->cart_shipmentTax = 0;
			$this->cart_total = 0;
		}
		
		return true;
	}	

	
	public function getProduct($group_id)
	{
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $vendor_cart)
			{
				if($product = $vendor_cart->getProduct($group_id))
				{
					return $product;
				}
			}
		}
		
		return false;
	}

	public function setBTAddress($data)
	{
		$data = (array) $data;
		
		if(isset($data['address_type'])) 
		{
			unset($data['address_type']);
		}	
				
		$this->billing_address = $data;
		
		return true;
	}	
	
	public function setSTAddress($data)
	{
		$data = (array) $data;
		
		if(isset($data['address_type'])) 
		{
			unset($data['address_type']);
		}	
		
		$this->shipping_address = $data;
		
		return true;
	}
	
	/**
	* Method to set new shipping method to cart
	* 
	* @param object $method stdClass object of shipping method
	* 
	* @return boolean
	* @since	1.0
	*/	
	public function setShippingMethod($method)
	{
		$this->cart_shipment_method_id = $method->id;
		$this->cart_shipment_method_name = $method->shipment_name;
		$this->cart_shipment_method_html = $method->html;
		$this->cart_shipmentPrice = $method->price;
		$this->cart_shipmentTax = $method->tax;
		
		if(!$this->calculateSP())
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}
	
	/**
	* Method to unset selected shipping method from cart
	* 
	* @return boolean
	* @since	1.0
	*/	
	public function unsetShippingMethod()
	{
		$this->cart_shipment_method_id = null;
		$this->cart_shipment_method_name = null;
		$this->cart_shipment_method_html = null;
		$this->cart_shipmentPrice = 0;
		$this->cart_shipmentTax = 0;

		if(count($this->vendor_carts))
		{	
			$this->cart_total = 0;
			
			foreach($this->vendor_carts as $vendor_cart)
			{
				if(!$vendor_cart->unsetShippingMethod())
				{
					$this->setError($vendor_cart->getError());
					return false;
				}							
	
				$this->cart_total += $vendor_cart->Total;
			}
		}
		
		return true;		
	}
	
	/**
	* Method to set new payment method to cart
	* 
	* @param object $method stdClass object of payment method
	* 
	* @return boolean
	* @since	1.0
	*/	
	public function setPaymentMethod($method)
	{
		$this->cart_payment_method_id = $method->id;
		$this->cart_payment_method_name = $method->payment_name;
		$this->cart_payment_method_html = $method->html;
		$this->cart_paymentPrice = $method->price;
		$this->cart_paymentTax = $method->tax;
		
		if(!$this->calculateSP())
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}
	
	/**
	* Method to unset selected payment method from cart
	* 
	* @return boolean
	* @since	1.0
	*/	
	public function unsetPaymentMethod()
	{
		$this->cart_payment_method_id = null;
		$this->cart_payment_method_name = null;
		$this->cart_payment_method_html = null;
		$this->cart_paymentPrice = 0;
		$this->cart_paymentTax = 0;

		if(count($this->vendor_carts))
		{	
			$this->cart_total = 0;
			
			foreach($this->vendor_carts as $vendor_cart)
			{
				if(!$vendor_cart->unsetPaymentMethod())
				{
					$this->setError($vendor_cart->getError());
					return false;
				}							
	
				$this->cart_total += $vendor_cart->Total;
			}
		}
		
		return true;		
	}		

	public function calculateCart($setOrderStatus = false)
	{	
		$this->coupon_discount = 0;
		$this->cart_total = 0;		
			
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $vendor_cart)
			{				
				if(!$vendor_cart->processOrderData($this, $setOrderStatus))
				{
					$this->setError($vendor_cart->getError());
					return false;
				}
				
				$this->coupon_discount += $vendor_cart->coupon_discount;
				$this->cart_total += $vendor_cart->Total;
			}
		}
		
		if(!$this->calculateSP())
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}
	
	public function recalculate()
	{		
		$user = JFactory::getUser();
		$this->user_id = $user->get('id');
		
		$this->coupon_discount = 0;
		$this->cart_total = 0;
		
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $vendor_cart)
			{	
				$vendor_cart->recalculate();				
									
				if(!$vendor_cart->processOrderData($this))
				{
					$this->setError($vendor_cart->getError());
					return false;
				}
				
				$this->coupon_discount += $vendor_cart->coupon_discount;
				$this->cart_total += $vendor_cart->Total;
			}
		}
		
		if(!$this->calculateSP())
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}
	
	public function calculateSP()
	{		
		if(count($this->vendor_carts))
		{
			// Store the existing cart total in temporary variable before reset
			$old_cart_total = $this->cart_total;
			
			$this->cart_total = 0;
			
			foreach($this->vendor_carts as $vendor_cart)
			{
				// Set shipment method in vendor cart
				$tmp = new stdClass;
				$tmp->id = $this->cart_shipment_method_id;		
				$tmp->price = $this->getProportionateValue($this->cart_shipmentPrice, $vendor_cart->Total, $old_cart_total);
				$tmp->tax = $this->getProportionateValue($this->cart_shipmentTax, $vendor_cart->Total, $old_cart_total);

				if(!$vendor_cart->setShippingMethod($tmp, $this))
				{
					$this->setError($vendor_cart->getError());
					return false;
				}							

				// Set payment method in vendor cart
				$tmp = new stdClass;
				$tmp->id = $this->cart_payment_method_id;		
				$tmp->price = $this->getProportionateValue($this->cart_paymentPrice, $vendor_cart->Total, $old_cart_total);
				$tmp->tax = $this->getProportionateValue($this->cart_paymentTax, $vendor_cart->Total, $old_cart_total);
				
				if(!$vendor_cart->setPaymentMethod($tmp, $this))
				{
					$this->setError($vendor_cart->getError());
					return false;
				}
				
				$this->cart_total += $vendor_cart->Total;
			}
		}
		
		return true;		
	}
	
	public function getCommission()
	{
		if($this->_commission === null)
		{
			$this->_commission = 0;
			
			if(!empty($this->vendor_carts))
			{
				foreach($this->vendor_carts as $order)
				{
					$this->_commission += $order->getCommission(); 
				}
			}
		}
		
		return $this->_commission;
	}
	
	
	protected function getProportionateValue($value, $vendor_cart_total, $old_cart_total)
	{
		
		$proportionateValue = ($value * $vendor_cart_total) / $old_cart_total;
		
		//echo 'Input:'.$value .', Vendor Cart Total:'. $vendor_cart_total . ', Cart Total:' . $this->cart_total . ', Output: '. $proportionateValue;exit;
		
		return round($proportionateValue, 6);
	}
	
	/**
	* Method to set order status to cart
	* 
	* @param undefined $status
	* @param undefined $product_group_id
	* 
	* @return void
	*/	
	public function setOrderStatus($status, $product_group_id = null)
	{
		if($product_group_id === null)
		{
			$this->order_status = $status;
		}
		
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $order)
			{
				$order->setOrderStatus($status, $product_group_id);
			}
		}
	}
	
	/**
	* Method to decode json encodes data in the cart object
	* 
	* @return void
	*/
	public function decodeData()
	{
		$encodesFields = array(
											'billing_address',
											'shipping_address'
											);
											
		foreach($encodesFields as $field)
		{
			if($this->$field && is_string($this->$field))
			{
				$this->$field = json_decode($this->$field, true);
			}
		}
		
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $order)
			{
				$order->decodeData();
			}
		}
	}
	
	public function setCoupon($data)
	{
		if(!$data || !is_object($data))
		{
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_INVALID_COUPON_DATA'));
			return false;
		}
		
		$this->coupon_code = $data->coupon_code;
		
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $vendor_cart)
			{
				$couponData = clone $data;
				
				if($data->math_operation == 'v')
				{
					$couponData->coupon_value = $this->getProportionateValue($data->coupon_value, $vendor_cart->Total, $old_cart_total);
				}				
				
				if(!$vendor_cart->setCoupon($couponData))
				{
					$this->setError($vendor_cart->getError());
					return false;
				}
			}
		}
		
		if(!$this->calculateCart())
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}
	
	/**
	* Method to validate cart data before order confirmation
	* 
	* @return boolean
	* @since	1.0
	*/	
	public function validate($returnMisssingValue = false)
	{
		$config = QZApp::getConfig();
		
		if(empty($this->vendor_carts))
		{
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_CART_IS_EMPTY'));
			return false;			
		}
		
		if(empty($this->ordergroup_number))
		{
			$this->createOrderGroup(true);
		}
		
		if(empty($this->access_key))
		{
			$this->createAccessKey(true);
		}		
		
		if(empty($this->billing_address))
		{
			if($returnMisssingValue)
			{
				return 'billing_address';
			}			
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_NO_BILLING_ADDRESS_SET'));
			return false;
		}
		
		if(empty($this->shipping_address) && !$config->get('intangible', 0) && !$config->get('downloadable', 0))
		{
			if($returnMisssingValue)
			{
				return 'shipping_address';
			}			
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_NO_SHIPPING_ADDRESS_SET'));
			return false;
		}	
		
		if(!$this->cart_shipment_method_id && !$config->get('intangible', 0) && !$config->get('downloadable', 0))
		{
			if($returnMisssingValue)
			{
				return 'shipment_method';
			}			
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_NO_SHIPPING_METHOD_SELECTED'));
			return false;
		}
		
		if(!$this->cart_payment_method_id && $this->cart_total > 0)
		{
			if($returnMisssingValue)
			{
				return 'payment_method';
			}				
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_NO_PAYMENT_METHOD_SELECTED'));
			return false;
		}			
		
		return true;				
	}
	

	protected function _calculateTaxDiscountHandling()
	{
		$this->_tax = 0;
		$this->_discount = 0;
		$this->_handling = 0;
		
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $vendor_cart)
			{	
				$this->_tax += $vendor_cart->getTax();		
				$this->_discount += $vendor_cart->getDiscount();
			}			
		}	
		
		$this->_tax += ($this->cart_paymentTax + $this->cart_shipmentTax);

		if($this->coupon_discount) 
		{
			$this->_discount += $this->coupon_discount;
		}
		
		$this->_handling = ($this->cart_paymentPrice + $this->cart_shipmentPrice);
	}	
	/**
	* Method to get handling cost of the order group / cart 
	* 
	* @return float
	* @since	1.0
	*/
	public function getHandlingCost()
	{
		if($this->_handling === null)
		{
			$this->_calculateTaxDiscountHandling();
		}
		
		return $this->_handling;
	}
	
	public function getTaxAmount()
	{
		if($this->_tax === null)
		{
			$this->_calculateTaxDiscountHandling();
		}
		
		return $this->_tax;		
	}

	/**
	* Method to get handling cost of the order group / cart 
	* 
	* @return float
	* @since	1.0
	*/
	public function getDiscountAmount()
	{
		if($this->_discount === null)
		{
			$this->_calculateTaxDiscountHandling();
		}
		
		return $this->_discount;	
	}
	
	public function setCustomerNote($note)
	{
		$this->customer_note = trim($note);
		
		return true;
	}
	
	public function setTOSAccept($acceptance)
	{
		$this->tos_accept = (int) $acceptance;
	
		return true;
	}	
	
	public function getOrderByOrderID($order_id)
	{
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as $order)
			{
				if($order->order_id == $order_id)
				{
					return $order;
				}
			}
		}
		
		return false;
	}
	
	public function getStatuses($recheck = false)
	{
		static $cache = array();
		
		if(!isset($cache[$this->ordergroup_id]) || $recheck)
		{
			$statuses = array();
			$statuses[] = $this->order_status;
			
			if(count($this->vendor_carts))
			{
				foreach($this->vendor_carts as $order)
				{
					if($orderStatuses = $order->getStatuses($recheck))
					{
						$statuses = ($statuses + (array) $orderStatuses);
					}
				}
			}
			
			$cache[$this->ordergroup_id] = array_filter(array_unique($statuses));
		}
		
		return $cache[$this->ordergroup_id];
	}

	/**
	* Method to get all products in the cart / ordergroup
	* 
	* @return	mixed	(array/boolean)	Array of products or false if no products exists
	* @since	1.0
	*/	
	public function getProducts($recheck = false)
	{
		static $return = null;
		
		if(($return === null) || $recheck)
		{
			$return = array();
			$this->_item_count = 0;
			
			if(!empty($this->vendor_carts))
			{
				foreach($this->vendor_carts as $order)
				{
					$products = $order->get('products', array());
					$return = array_merge($return, $products);
					
					if(!empty($products))
					{
						foreach($products as $product)
						{
							$this->_item_count += $product->product_quantity;
						}
					}					
				}
			}			
		}
		
		return $return;
	}
	
	/**
	* Method to get total product / item count in the cart
	* 
	* @param boolean $recheck If true the function will always iterate through the order object to calculate the count
	* 
	* @return interger
	*/
	public function getItemCount($recheck = false)
	{		
		if($this->_item_count === null || $recheck)
		{
			$this->_item_count = 0;
			
			if(!empty($this->vendor_carts))
			{
				foreach($this->vendor_carts as $order)
				{
					$products = $order->get('products', array());
					
					if(!empty($products))
					{
						foreach($products as $product)
						{
							$this->_item_count += $product->product_quantity;
						}
					}				
				}
			}			
		}	
		
		return (int) $this->_item_count;	
	}
	
	public function getTotalWeight($uom_id)
	{
		$total = 0;
		
		if(!empty($this->vendor_carts))
		{
			foreach($this->vendor_carts as $order)
			{
				$weight = $order->getTotalWeight($uom_id);
				
				if($weight === false)
				{
					$this->setError($order->getError());
					return false;
				}
				
				$total += floatval($weight);
			}
		}
		
		return $total;			
	}

	public function setItemsTable($items_table)
	{
		$this->items_table = $items_table;
	}
	
	public function prepareMailData()
	{
		if(isset($this->billing_address['country']) && is_numeric($this->billing_address['country']))
		{
			$this->billing_address['country'] = QZDisplay::getCountryNamebyID($this->billing_address['country']);
		}
		
		if(isset($this->shipping_address['country']) && is_numeric($this->shipping_address['country']))
		{
			$this->shipping_address['country'] = QZDisplay::getCountryNamebyID($this->shipping_address['country']);
		}	
		
		if(isset($this->billing_address['states_territory']) && is_numeric($this->billing_address['states_territory']))
		{
			$this->billing_address['states_territory'] = QZDisplay::getStateNamebyID($this->billing_address['states_territory']);
		}		
		
		if(isset($this->shipping_address['states_territory']) && is_numeric($this->shipping_address['states_territory']))
		{
			$this->shipping_address['states_territory'] = QZDisplay::getStateNamebyID($this->shipping_address['states_territory']);
		}
		
		if($this->order_status)
		{
			$this->order_status = QazapHelper::orderStatusNameByCode($this->order_status);
		}		

	}
}