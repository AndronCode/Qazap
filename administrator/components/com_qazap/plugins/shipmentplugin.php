<?php
/**
 * shipmentplugin.php
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
defined('_JEXEC') or die();


abstract class QZShipmentPlugin extends QZPlugin
{	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
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
		return;
	}
	
	public function onOrderDisplayShipmentMethod(&$method)
	{
		return;
	}	
	
	/**
	* Method to get the plugin name by method id
	* 
	* @param integer $method_id
	* 
	* @return mixed	(string/false) Plugin name or false
	*/	
	protected function getName($method_id)
	{
		if(!$method_id)
		{
			$this->setError(JText::_('COM_QAZAP_PLUGIN_ERROR_INVALID_SHIPMENT_METHOD_id'));
			return false;
		}
		
		if(!$method = $this->getMethod($method_id, 'shipment'))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return $method->plugin;
	}
	
	/**
	* Method to get the method params by method id
	* 
	* @param integer $method_id
	* 
	* @return mixed	(object/false) JRegistry params object or false in case of failure
	*/	
	protected function getParams($method_id)
	{
		if(!$method_id)
		{
			$this->setError(JText::_('COM_QAZAP_PLUGIN_ERROR_INVALID_SHIPMENT_METHOD_ID_TO_GET_PARAMS'));
			return false;
		}
		
		if(!$method = $this->getMethod($method_id, 'shipment'))
		{
			$this->setError($this->getError());
			return false;
		}

		if ($method->params instanceof JRegistry)
		{
			return $method->params;
		}
		else
		{
			$tmp = new JRegistry;
			$tmp->loadString($method->params);
			return $tmp;
		}
	}		

	/**
	* Method to calculate shipping method price
	* 
	* @param	object $method stdClass object of method
	* 
	* @return	void
	* @since	1.0
	*/	
	protected function calculatePrice(&$method)
	{
		$tax = 0;
		
		if($method->tax)
		{
			// If percent
			if($method->tax_calculation == 'p')
			{
				$tax = ($method->price * $method->tax) / 100;
			}
			else
			{
				$tax = $method->tax;
			}
		}
		
		$method->tax = $tax;		
		$method->total_price = ($method->price + $method->tax);
	}

	protected function getSelectedMethodDisplay($method)
	{
		$layoutPath = QZPATH_LAYOUT . DS . 'cart';
		// Arrange to display the method with layout file.
		$layout = new JLayoutFile('selected_shipping', $layoutPath);
		return $layout->render($method);		
	}

}