<?php

/**
 * display.php
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

class QZDisplay extends QZObject 
{
	protected static $_shipment_methods = array();
	protected static $_payment_methods = array();
	protected static $_countries = array();
	protected static $_states = array();
	protected static $_memberships = array();

	public static function getShipmentMethodNameByID($shipment_method_id, $link = true)
	{
		$shipment_method_id = (int) $shipment_method_id;
		
		if(!$shipment_method_id)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_MSG_INVALID_SHIPPING_METHOD'));
			return null;
		}		
	
		if(!isset(self::$_shipment_methods[$shipment_method_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('a.id, a.ordering, a.state, a.shipment_name, a.shipment_description, a.shipment_method, '.
										'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
						->from('#__qazap_shipment_methods AS a')
						->select('b.element AS plugin')				
						->leftjoin('#__extensions AS b ON a.shipment_method = b.extension_id')
						->where('a.id = '. (int) $shipment_method_id);
			$db->setQuery($query);
			$result = $db->loadObject();
			
			if(empty($result))
			{
				self::$_shipment_methods[$shipment_method_id] = $shipment_method_id;
			}
			else
			{
				self::$_shipment_methods[$shipment_method_id] = $result;
			}
		}
		
		$method = self::$_shipment_methods[$shipment_method_id];
		
		if(is_numeric($method))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_MSG_INVALID_SHIPPING_METHOD'));
			return $method;
		}		
		
		$tmp = new JRegistry;
		$tmp->loadString($method->params);
		$method->params = $tmp;
		
		$method->html = $method->shipment_name;

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapshipment');
				
		$result = $dispatcher->trigger('onOrderDisplayShipmentMethod', array(&$method));

		if (in_array(false, $result, true))
		{
			JFactory::getApplication()->enqueueMessage($dispatcher->getError());
		}
	
		if($link)
		{
			$replace = JHTML::link(JRoute::_('index.php?option=com_qazap&task=shipmentmethod.edit&id='. (int) $method->id), $method->shipment_name, array('target'=>'_blank', 'title'=>$method->shipment_name));
			$method->html = str_replace($method->shipment_name, $replace, $method->html);
		}
				
		return $method->html;		
	}
	
	public static function getPaymentMethodNameByID($payment_method_id, $link = true)
	{
		$payment_method_id = (int) $payment_method_id;
		
		if(!$payment_method_id)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_MSG_INVALID_PAYMENT_METHOD'));
			return null;
		}		
	
		if(!isset(self::$_payment_methods[$payment_method_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('a.id, a.ordering, a.state, a.payment_name, a.payment_description, a.payment_method, '.
											'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
						->from('#__qazap_payment_methods AS a')
						->select('b.element AS plugin')				
						->leftjoin('#__extensions AS b ON a.payment_method = b.extension_id')
						->where('a.id = '. (int) $payment_method_id);
			$db->setQuery($query);
			$result = $db->loadObject();
			
			if(empty($result))
			{
				self::$_payment_methods[$payment_method_id] = $payment_method_id;
			}
			else
			{
				self::$_payment_methods[$payment_method_id] = $result;
			}
		}
		
		$method = self::$_payment_methods[$payment_method_id];
		
		if(is_numeric($method))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_MSG_INVALID_PAYMENT_METHOD'));
			return $method;
		}		
		
		$tmp = new JRegistry;
		$tmp->loadString($method->params);
		$method->params = $tmp;
		
		$method->html = $method->payment_name;

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
				
		$result = $dispatcher->trigger('onOrderDisplayPaymentMethod', array(&$method));

		if (in_array(false, $result, true))
		{
			JFactory::getApplication()->enqueueMessage($dispatcher->getError());
		}
	
		if($link)
		{
			$replace = JHTML::link(JRoute::_('index.php?option=com_qazap&task=paymentmethod.edit&id='. (int) $method->id), $method->payment_name, array('target'=>'_blank', 'title'=>$method->payment_name));
			$method->html = str_replace($method->payment_name, $replace, $method->html);
		}
				
		return $method->html;		
	}	
	
	public static function getCountryNamebyID($country_id)
	{
		$country_id = (int) $country_id;
		
		if(!$country_id)
		{
			return null;
		}
		
		if(!isset(self::$_countries[$country_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('country_name')
						->from('#__qazap_countries')
						->where('id = ' . $country_id);
			$db->setQuery($query);
			self::$_countries[$country_id] = $db->loadResult();
		}
		
		return self::$_countries[$country_id];
	}
	
	public static function getStateNamebyID($state_id)
	{
		$state_id = (int) $state_id;
		
		if(!$state_id)
		{
			return null;
		}
		
		if(!isset(self::$_states[$state_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('state_name')
						->from('#__qazap_states')
						->where('id = ' . $state_id);
			$db->setQuery($query);
			self::$_states[$state_id] = $db->loadResult();
		}
		
		return self::$_states[$state_id];
	}	
	
	public static function getMembershipNameByID($membership_id)
	{
		$membership_id = (int) $membership_id;
		
		if(!$membership_id)
		{
			return null;
		}	
		
		if(!isset(self::$_memberships[$membership_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('plan_name')
						->from('#__qazap_memberships')
						->where('id = ' . $membership_id);
			$db->setQuery($query);
			self::$_memberships[$membership_id] = $db->loadResult();
		}
		
		return self::$_memberships[$membership_id];			
	}

}