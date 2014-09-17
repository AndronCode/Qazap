<?php
/**
 * cash.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazappayment Cash Plugin
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
	require(JPATH_ADMINISTRATOR . '/components/com_qazap/app.php');
	// Setup Qazap for autload classes
	QZApp::setup();
}

class PlgQazapPaymentCash extends QZPaymentPlugin
{
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}
	
	/**
	* Method create new table for the plugin
	* 
	* $SQLfields = array(
			'qazap_order_id' => 'int(11) UNSIGNED',
			'order_number' => 'char(64)',
			'qazap_shipmentmethod_id' => 'mediumint(11) UNSIGNED'
		);
		return $SQLfields;
		* 
	* @return	Mixed	Array of SQL fields or false
	* @since	1.0
	*/	
	protected function getTableFields()
	{
	 $SQLfields = array(
			'qazap_order_id' => 'int(11) UNSIGNED',
			'order_number' => 'char(64)',
			'paymentmethod_id' => 'mediumint(11) UNSIGNED'
		);
		return $SQLfields;
	}		
	
	public function onDisplayPaymentMethods(&$method, QZCart $cart)
	{
		if($method->plugin != $this->_name)
		{
			return;
		}		

		// Params in plugin variable
		$this->setParams($method->params);
				
		$params = $method->params;

		if($params->get('min_order_value') && ($cart->cart_total < (float) $params->get('min_order_value')))
		{	
			return;
		}
		
		if($params->get('max_order_value') && ($cart->cart_total > (float) $params->get('max_order_value')))
		{	
			return;
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

		if(!$params = $this->getParams($ordergroup->cart_payment_method_id))
		{
			$cartModel->setError($this->getError());
			return false;
		}
		
		// Change the ordergroup status for immediate display.
		$ordergroup->setOrderStatus($params->get('confirm_order_status', 'C'));
		
		// Change the order group status for confirmation. 
		$data = array();
		$data['ordergroup_id'] = $ordergroup->ordergroup_id; 
		$data['order_status'] = $params->get('confirm_order_status', 'C');
		$data['apply_to_all_orders'] = true; 
		$data['comment'] = '';
		
		// Need to create a fresh instance of Order model to reset class properties.
		$orderModel = QZApp::getModel('order', array('ignore_request' => true));
		
		// Save modified ordergroup
		if(!$orderModel->updateOrdergroupStatus($data))
		{
			$cartModel->setError($cartModel->getError());
			return false;
		}
		
		return true;
	}	
}
