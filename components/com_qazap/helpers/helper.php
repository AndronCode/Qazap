<?php
/**
 * helper.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

JLoader::register('QazapHelper', JPATH_ADMINISTRATOR . '/components/com_qazap/helpers/qazap.php');

class QZHelper extends QazapHelper
{

	public static function displayPrice($property = 'base_price', $name = 'COM_QAZAP_BASE_PRICE', $prices, $element = 'span')
	{
		if(!is_object($prices))
		{
			JError::raiseWarning(1, 'QZHelper::displayPrice() says this, Invalid Prices Object. Prices must be an instance of QZPrices');
			return false;
		}
		
		if(!$prices || !isset($prices->$property) || !$prices->$property)
		{
			return false;
		}
			
		if($name)
		{
			$name = '<span class="'.$property.'_title">' . JText::_($name) . ': </span>';
		}
		
		$value = '<span class="' . $property . '_value">' . parent::currencyDisplay($prices->$property) . '</span>';
		
		$element = trim($element);
		return '<' . $element . ' class="' . $property . '">' . $name . $value . '</' . $element .'>';		
	}
	
	public static function getCountryByID($country_id, $field = null)
	{
		if(!$country_id)
		{
			return null;	
		}
		
		static $cache = array();
		
		if(!isset($cache[$country_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
							->select('id, country_name, country_3_code, country_2_code')
							->from('#__qazap_countries')
							->where('id = ' . (int) $country_id);
			$db->setQuery($query);
			$country = $db->loadObject();
			
			if(empty($country))
			{
				$cache[$country_id] = false;
			}
			else
			{
				$cache[$country_id] = $country;
			}
		}
		
		if($field === null)
		{
			return $cache[$country_id];
		}
		
		if(isset($cache[$country_id]->$field))
		{
			return $cache[$country_id]->$field;
		}
		
		return null;
	}
	
	public static function getStateByID($state_id, $field = null)
	{
		if(!$state_id)
		{
			return null;
		}
		
		static $cache = array();
		
		if(!isset($cache[$state_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
							->select('id, country_id, state_name, state_3_code, state_2_code')
							->from('#__qazap_states')
							->where('id = ' . (int) $state_id);
							
			$db->setQuery($query);
			$state = $db->loadObject();
			
			if(empty($state))
			{
				$cache[$state_id] = false;
			}
			else
			{
				$cache[$state_id] = $state;
			}
		}
		
		if($field === null)
		{
			return $cache[$state_id];
		}
		
		if(isset($cache[$state_id]->$field))
		{
			return $cache[$state_id]->$field;
		}
		
		return null;
	}	
	
	public static function getLayoutFile($layout = 'default', $view = null)
	{
		$app = JFactory::getApplication();
		$view = !empty($view) ? $view : $app->input->getCmd('view', null);
		
		if($view === null)
		{
			return null;
		}
		
		jimport('joomla.filesystem.folder');
		$view = strtolower((string) $view);
		$layout = strtolower((string) $layout);		
		
		$template = $app->getTemplate(true)->template;
		$templateLayoutPath = JPATH_SITE . '/templates/' . $template . 'html/com_qazap/' . $view . '/' . $layout . '.php';
		$templateLayoutPath = JPath::clean($templateLayoutPath);
		$coreLayoutPath = QAZAP_SITE . '/views/' . $view . '/tmpl/' . $layout . '.php';
		$coreLayoutPath = JPath::clean($coreLayoutPath);
		
		if(is_file($templateLayoutPath))
		{
			return $templateLayoutPath;
		}
		elseif(is_file($coreLayoutPath))
		{
			return $coreLayoutPath;
		}
		
		return null;		
	}
	
	public static function getLayoutFileNames($view = null)
	{
		static $cache = array();
		
		if(isset($cache[$view]))
		{
			return $cache[$view];
		}
		
		jimport('joomla.filesystem.folder');

		$app = JFactory::getApplication();
		$view = !empty($view) ? $view : $app->input->getCmd('view', null);
		
		if($view === null)
		{
			return null;
		}
		
		$view = strtolower((string) $view);
		
		$template = $app->getTemplate(true)->template;
		$templateLayoutPath = JPATH_SITE . '/templates/' . $template . 'html/com_qazap/' . $view;
		$templateLayoutPath = JPath::clean($templateLayoutPath);
		$coreLayoutPath = QAZAP_SITE . '/views/' . $view . '/tmpl';
		$coreLayoutPath = JPath::clean($coreLayoutPath);
		
		if(is_dir($templateLayoutPath) && ($template_layouts = JFolder::files($templateLayoutPath, '^.+\.php$')))
		{			
			$cache[$view] = $template_layouts;
		}
		elseif(is_dir($coreLayoutPath) && ($core_layouts = JFolder::files($coreLayoutPath, '^.+\.php$')))
		{	
			$cache[$view] = $core_layouts;
		}
		else
		{
			$cache[$view] = null;
		}
		
		return $cache[$view];		
	}	
	
	
	public static function addMenu($vName = '', $lName = '')
	{
		$app = JFactory::getApplication();
		$config = QZApp::getConfig();
		$user = QZUser::get();
		$address_type = $app->input->get('type', 'bt', 'string');
		$id = $app->input->getInt('id', 0);
		
		JHtml::_('qzmenu.addParent', 'profile', JText::_('COM_QAZAP_MENU_BUYER_ACCOUNT'), $link = QazapHelperRoute::getProfileRoute(), $vName == 'profile');
		
		JHtml::_('qzmenu.addEntry', 'profile', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_BUYER_HOME'), $link = QazapHelperRoute::getProfileRoute(), ($vName == 'profile' && $lName == 'default'));
		
		JHtml::_('qzmenu.addEntry', 'profile', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_BUYER_ORDER_LIST'), $link = QazapHelperRoute::getProfileRoute('orderlist'), ($vName == 'profile' && ($lName == 'orderlist' || $lName == 'order')));
		JHtml::_('qzmenu.addEntry', 'profile', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_BUYER_WISHLIST'), $link = QazapHelperRoute::getProfileRoute('wishlist'), ($vName == 'profile' && ($lName == 'wishlist')));
		JHtml::_('qzmenu.addEntry', 'profile', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_BUYER_WAITING_LIST'), $link = QazapHelperRoute::getProfileRoute('waitinglist'), ($vName == 'profile' && ($lName == 'waitinglist')));

		
		if($lName == 'edit')
		{
			if(strtolower($address_type) == 'st')
			{
				if($id > 0)
				{
					JHtml::_('qzmenu.addEntry', 'profile', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_EDIT_SHIPPING_ADDRESS'), $link = '', ($vName == 'profile' && $lName == 'edit'));
				}
				else
				{
					JHtml::_('qzmenu.addEntry', 'profile', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_ADD_SHIPPING_ADDRESS'), $link = '', ($vName == 'profile' && $lName == 'edit'));
				}
			}				
			else
			{
				JHtml::_('qzmenu.addEntry', 'profile', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_EDIT_BILLING_ADDRESS'), $link = '', ($vName == 'profile' && $lName == 'edit'));
			}			
		}
		
		
		if($config->get('enable_fe_vendor_account', 1) && $user->isVendor)
		{
			JHtml::_('qzmenu.addParent', 'seller', JText::_('COM_QAZAP_MENU_SELLER_ACCOUNT'), $link = QazapHelperRoute::getSellerRoute(), $vName == 'seller');
			JHtml::_('qzmenu.addEntry', 'seller', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_SELLER_HOME'), $link = QazapHelperRoute::getSellerRoute(), ($vName == 'seller' && $lName == 'default'));
			JHtml::_('qzmenu.addEntry', 'seller', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_SELLER_BILLING_ADDRESS'), $link = 'index.php?option=com_qazap&view=seller&task=seller.edit', ($vName == 'sellerform' && $lName == 'edit'));
			JHtml::_('qzmenu.addEntry', 'seller', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_SELLER_PRODUCT_LIST'), $link = QazapHelperRoute::getSellerRoute('productlist'), ($vName == 'seller' && $lName == 'productlist'));
			JHtml::_('qzmenu.addEntry', 'seller', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_SELLER_ORDER_LIST'), $link = QazapHelperRoute::getSellerRoute('orderlist'), ($vName == 'seller' && ($lName == 'orderlist' || $lName == 'order')));
			JHtml::_('qzmenu.addEntry', 'seller', 'qzicon-pencil4', JText::_('COM_QAZAP_MENU_SELLER_PAYMENT_LIST'), $link = QazapHelperRoute::getSellerRoute('paymentlist'), ($vName == 'seller' && ($lName == 'paymentlist' || $lName == 'paymentdetails')));			
		}
		elseif($config->get('enable_vendor_registration', 1))
		{
			JHtml::_('qzmenu.addParent', 'seller', JText::_('COM_QAZAP_MENU_REGISTER_SELLER'), $link = 'index.php?option=com_qazap&view=seller&task=seller.add', $vName == 'sellerform');
		}

	}	
	
	public static function unique_id($l = 8) 
	{
		return substr(md5(uniqid(mt_rand(), true)), 0, $l);
	}
	
}

