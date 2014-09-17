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

class QZOrder extends QZObject
{
	public $order_id = 0;
	public $ordergroup_id = 0;

	public $user_id = null;
	public $vendor = null;
	public $shop_name = null;
	public $order_number = null;
	
	public $product_count = 0;
	public $products = array();
	
	public $productTotalTax = 0;
	public $productTotalDiscount = 0;
	public $totalProductPrice = 0;
	
	public $shipmentTax = 0;
	public $shipmentPrice = 0;
	public $paymentTax = 0;
	public $paymentPrice = 0;
	
	public $CartDiscountBeforeTax = 0;
	public $CartDiscountBeforeTaxInfo = array();
	
	public $CartTax = 0;
	public $CartTaxInfo = array();
	
	public $CartDiscountAfterTax = 0;
	public $CartDiscountAfterTaxInfo = array();
	
	public $coupon_discount = 0;
	public $coupon_code = null;
	public $coupon_data = null;
	public $TotalTax = 0;
	public $TotalDiscount = 0;
	public $Total = 0;
	
	//public $order_currency = null;
	//public $user_currency = null;
	//public $currency_exchange_rate = null;
	
	public $payment_method_id = null;
	public $shipment_method_id = null;
	
	public $order_status = null;
	public $created_on = null;
	public $created_by = null;
	public $modified_on = null;
	public $modified_by = null;
	
	protected $_commission = null;
	//public $customer_note = null;
	//public $ip_address = null;
	//public $billing_address = null;
	//public $shipping_address = null;
	

	public function __construct($properties = null) 
	{
		parent::__construct($properties);
		
		if($properties)
		{
			$this->setProperties($properties);
		}		
	}	
	
	public function getProduct($group_id)
	{
		if(isset($this->products[$group_id]))
		{
			return $this->products[$group_id];
		}
		
		return false;
	}
	
	public function setProduct($product)
	{		
		if(empty($product->order_status) || !$product->order_status)
		{
			$product->set('order_status', $this->order_status);
		}

		$this->products[$product->group_id] = $product;
		return true;
	}
	
	public function removeProduct($group_id)
	{
		if(isset($this->products[$group_id]))
		{
			unset($this->products[$group_id]);
		}
		
		return true;
	}
	
	public function deleteProduct($group_id)
	{
		if(isset($this->products[$group_id]))
		{
			$this->products[$group_id]->set('deleted', 1);
			// Set order status as deleted
			$this->products[$group_id]->set('order_status', 'D');
		}
		
		return true;
	}	
	
	public function setShippingMethod($method, $cart)
	{		
		$this->shipment_method_id = $method->id;
		$this->shipmentPrice = $method->price;
		$this->shipmentTax = $method->tax;
		
		if(!$this->processOrderData($cart))
		{
			$this->setError($this->getError());
			return false;
		}

		$this->TotalTax += $this->shipmentTax;
		$this->Total += ($this->shipmentTax + $this->shipmentPrice);	

		return true;
	}
	
	public function unsetShippingMethod()
	{
		$this->TotalTax -= $this->shipmentTax;
		$this->Total -= ($this->shipmentTax + $this->shipmentPrice);		
		$this->shipment_method_id = null;
		$this->shipmentPrice = 0;
		$this->shipmentTax = 0;
		
		return true;
	}
	
	public function setPaymentMethod($method, $cart)
	{
		$this->payment_method_id = $method->id;
		$this->paymentPrice = $method->price;
		$this->paymentTax = $method->tax;
		
		if(!$this->processOrderData($cart))
		{
			$this->setError($this->getError());
			return false;
		}
		
		$this->TotalTax += $this->paymentTax;
		$this->Total += ($this->paymentTax + $this->paymentPrice);	
		
		return true;		
	}
	
	public function unsetPaymentMethod()
	{
		$this->TotalTax -= $this->paymentTax;
		$this->Total -= ($this->paymentPrice + $this->paymentTax);		
		$this->payment_method_id = null;
		$this->paymentPrice = 0;
		$this->paymentTax = 0;
		
		return true;
	}	
	
	public function recalculate()
	{
		$user = JFactory::getUser();
		$this->user_id = $user->get('id');
		
		if(count($this->products))
		{
			foreach($this->products as $product)
			{
				$product->recalculate();
			}
		}
		
		return true;
	}
	
	
	// Calculate Order Totals //
	public function processOrderData($cart, $setOrderStatus = false)
	{
		static $checked = array();
		
		if(isset($checked[$this->vendor]))
		{
			return true;
		}
		
		// Reset old values before recaculating prices
		$this->resetBeforeCalculation();
		$validProducts = false;
				
		// Calculate product totals and set order status if asked
		if(count($this->products))
		{
			if(!$negative_statuses = $this->getNegativeOrderStates())
			{
				$this->setError($this->getError());
				return false;
			}
							
			foreach($this->products AS $product)
			{
				if($setOrderStatus)
				{
					$product_status = array();
					
					if(!in_array($product->order_status, $product_status))
					{
						$product_status[] = $product->order_status;
					}					
				}
				
				if(in_array($product->order_status,  $negative_statuses))
				{
					continue;
				}				
				
				$validProducts = true;
				
				$this->totalProductPrice += $product->product_totalprice;
				$this->productTotalTax += $product->total_tax;
				$this->productTotalDiscount += $product->total_discount;
				$this->_commission += $product->commission;				
				
				// Clean some unnecessary QZCarItemNode object properties
				$product->cleanProperties();
			}
			
			// If set order status and all products are having save status
			if($setOrderStatus && count($product_status) == 1)
			{
				$this->order_status = $product_status[0];
			}
		}
		
		
		if($validProducts)
		{
			// Calculation for coupon discount
			if($this->coupon_code && is_object($this->coupon_data))
			{
				if($this->coupon_data->math_operation == 'v')
				{
					$this->coupon_discount = $this->coupon_data->coupon_value;
				}
				else
				{
					$this->coupon_discount = ($this->totalProductPrice * $this->coupon_data->coupon_value) / 100;
				}
				
				$layout = new JLayoutFile('selected_coupon', $basePath = QZPATH_LAYOUT . DS . 'cart');
				$this->coupon_data->html = $layout->render($this->coupon_data);
			}
			
			// So far this is the order total
			$this->Total = ($this->totalProductPrice - $this->coupon_discount);

			// Lets get the cart calculation rules
			$rules = $this->_getRules($cart);
			
			if($rules === false)
			{
				$this->setError($this->getError());
				return false;
			}		

			// If rules exists we need to effect those rules in the cart/order.
			if(!empty($rules))
			{
				foreach($rules AS $rule)
				{
					// No values set for the rule. No need to consider the same.
					if(!$rule->value)
					{
						continue;
					}
					// Discount Before Tax rules
					elseif($rule->operation == 5)
					{
						if($rule->calculation == 'percent')
						{
							$rule->total = (($this->Total * $rule->value) / 100);
						}
						else
						{
							$rule->total = $rule->value;
						}
						
						$this->CartDiscountBeforeTax += $rule->total;
						$this->CartDiscountBeforeTaxInfo[] = $rule;					
						$this->Total -= $rule->total;
					}

					// Tax rules
					elseif($rule->operation == 6)
					{
						if($rule->calculation == 'percent')
						{
							$rule->total = (($this->Total * $rule->value) / 100);
						}
						else
						{
							$rule->total = $rule->value;
						}
						
						$this->CartTax += $rule->total;
						$this->CartTaxInfo[] = $rule;					
						$this->Total += $rule->total;
					}

					// Discount After Tax rules
					elseif($rule->operation == 4)
					{
						if($rule->calculation == 'percent')
						{
							$rule->total = (($this->Total * $rule->value) / 100);
						}
						else
						{
							$rule->total = $rule->value;
						}
						
						$this->CartDiscountAfterTax += $rule->total;
						$this->CartDiscountAfterTaxInfo[] = $rule;
						$this->Total -= $rule->total;					
					}
				}					
			}
			
			$this->TotalDiscount = ($this->productTotalDiscount + $this->coupon_discount + $this->CartDiscountBeforeTax + $this->CartDiscountAfterTax);
			$this->TotalTax = ($this->productTotalTax + $this->CartTax);			
		}
		
		// Set this vendor cart as checked
		$checked[$this->vendor] = true;
		
		return true;
	}
	
	
	public function getCommission()
	{
		if($this->_commission === null && !empty($this->products))
		{
			$this->_commission = 0;
			
			if(!$negative_statuses = $this->getNegativeOrderStates())
			{
				$this->setError($this->getError());
				return false;
			}
						
			foreach($this->products as $product)
			{				
				if(in_array($product->order_status,  $negative_statuses))
				{
					continue;
				}	

				$this->_commission += $product->commission;
			}
		}
		
		return $this->_commission;			
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
	
	protected function _getRules($cart)
	{
		static $cache = null;
		
		if($cache === null)
		{
			$db = JFactory::getDbo();
			$sql = $db->getQuery(true)
						->select('id, state, calculation_rule_name AS name, description, type_of_arithmatic_operation AS operation, '.
											'math_operation AS calculation, value, countries, zipcodes')
						->from('#__qazap_taxes')
						->where('type_of_arithmatic_operation IN (4,5,6)')
						->where('state = 1')
						->order('type_of_arithmatic_operation ASC');

			try
			{
				$db->setQuery($sql);
				$rules = $db->loadObjectList();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(!empty($rules))
			{
				$tmp = new JRegistry;
				$tmp->loadArray($cart->shipping_address);
				$shipping_address = $tmp;
				$user_country = $shipping_address->get('country', 0);
				$user_zip = $shipping_address->get('zip', '');
										
				foreach($rules as $key => $rule)
				{
					if(is_string($rule->countries) && $rule->countries)
					{						
						$rule->countries = (array) json_decode($rule->countries);
						$rule->countries = array_map('intval', $rule->countries);
						if(count($rule->countries) && !in_array($user_country, $rule->countries) && !in_array(0, $rule->countries))
						{
							unset($rules[$key]);
						}
					}
					if(is_string($rule->zipcodes) && $rule->zipcodes)
					{
						$rule->zipcodes = array_map('trim', explode(',', $rule->zipcodes));
						if(!in_array(trim($user_zip), $rule->zipcodes))
						{
							unset($rules[$key]);	
						}					
					}								
				}			
			}
			
			$cache = $rules;			
		}

		return $cache;			
	}
	
	protected function resetBeforeCalculation()
	{
		$this->product_count = 0;
		$this->productTotalTax = 0;
		$this->productTotalDiscount = 0;
		$this->totalProductPrice = 0;
	
		$this->CartDiscountBeforeTax = 0;
		$this->CartDiscountBeforeTaxInfo = array();
	
		$this->CartTax = 0;
		$this->CartTaxInfo = array();
	
		$this->CartDiscountAfterTax = 0;
		$this->CartDiscountAfterTaxInfo = array();
	
		$this->coupon_discount = 0;
		
		$this->TotalTax = 0;
		$this->TotalDiscount = 0;
		$this->Total = 0;	
		$this->_commission = 0;
	}
	
	public function setOrderStatus($status, $product_group_id = null)
	{
		if($product_group_id === null)
		{
			$this->order_status = $status;
		}
		
		if(count($this->products) && $product_group_id === null)
		{
			foreach($this->products as $orderItem)
			{
				if(!$orderItem->deleted)
				{
					$orderItem->set('order_status', $status);
				}				
			}
		}
		elseif(isset($this->products[$product_group_id]))
		{
			$this->products[$product_group_id]->set('order_status', $status);
		}
	}
	
	/**
	* @method Method to set new coupon in the order
	* 
	* @param object $data	stdClass obkect of coupon data
	* 
	* @return boolean
	* @since	1.0
	*/
	public function setCoupon($data)
	{
		$this->coupon_code = $data->coupon_code;
		$this->coupon_data = $data;
		
		return true;
	}
	
	
	/**
	* Method decode jsonencoded field data
	* 
	* @return void
	*/
	public function decodeData()
	{
		$encodesFields = array(
											'CartDiscountBeforeTaxInfo',
											'CartTaxInfo',
											'CartDiscountAfterTaxInfo',
											'coupon_data'
											);
											
		foreach($encodesFields as $field)
		{
			if(isset($this->$field) && $this->$field && is_string($this->$field))
			{
				$this->$field = json_decode($this->$field);		
			}
		}
		
		if(count($this->products))
		{
			foreach($this->products as $orderItem)
			{
				$orderItem->decodeData();
			}
		}
	}	
	

	public function getTax()
	{
		return (float) $this->CartTax;
	}
	
	public function getDiscount()
	{
		$discount  = 0;
		$discount += (float) $this->CartDiscountBeforeTax;
		$discount += (float) $this->CartDiscountAfterTax;
		
		return $discount;
	}	
	
	public function getStatuses($recheck = false)
	{
		static $cache = array();
		
		if(!isset($cache[$this->order_id]) || $recheck)
		{
			$statuses = array();
			$statuses[] = $this->order_status;
			
			if(!empty($this->products))
			{
				foreach($this->products as $product)
				{
					$statuses[] = $product->order_status;
				}
			}
			
			$cache[$this->order_id] = array_filter(array_unique($statuses));
		}
		
		return $cache[$this->order_id];		
	}
	
	public function setItemsTable($items_table)
	{
		$this->items_table = $items_table;
	}	
	
	public function getTotalWeight($uom_id)
	{
		$total = 0;
		
		if(!empty($this->products))
		{
			foreach($this->products as $product)
			{
				$weight = $product->getWeight($uom_id);
				
				if($weight === false)
				{
					$this->setError(JText::_('COM_QAZAP_CART_ERROR_PRODUCT_WEIGHT'));
					return false;
				}
				
				$total += floatval($weight);
			}
		}
		
		return $total;	
	}

}
