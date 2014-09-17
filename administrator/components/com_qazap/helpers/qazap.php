<?php
/**
 * qazap.php
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
jimport( 'joomla.utilities.simplexml' );
/**
 * Qazap helper.
 */
abstract class QazapHelper
{	
	protected $_getUserinfo;	
	protected static $_plugins = array();
	protected static $_cache = array();
	protected static $_currencies = array();
	protected static $_userfields = array();
	protected static $_productPrices = array();	
	protected static $_manufactuers = array();
	protected static $_categories = array();
	
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{
		
		QZApp::loadSidebarScripts();
		
		QZHtmlSidebar::addParent('home', JText::_('COM_QAZAP_SIDEBAR_HOME'), 'index.php?option=com_qazap', $vName == 'home');
		
		QZHtmlSidebar::addParent('product', JText::_('COM_QAZAP_SIDEBAR_PRODUCT'), '');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-file-plus2', JText::_('COM_QAZAP_SIDEBAR_ADD_NEW_PRODUCT'), $link = 'index.php?option=com_qazap&task=product.add', $vName == 'product');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-stack', JText::_('COM_QAZAP_SIDEBAR_PRODUCT_MANAGER'), $link = 'index.php?option=com_qazap&view=products', $vName == 'products');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-tree4', JText::_('COM_QAZAP_SIDEBAR_CATEGORY_MANAGER'), $link = 'index.php?option=com_qazap&view=categories', $vName == 'categories');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-stack-plus', JText::_('COM_QAZAP_SIDEBAR_ATTRIBUTE_MANAGER'), $link = 'index.php?option=com_qazap&view=cartattributestypes', $vName == 'cartattributestypes');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-stack-list', JText::_('COM_QAZAP_SIDEBAR_CUSTOM_FIELD_MANAGER'), $link = 'index.php?option=com_qazap&view=customfieldtypes', $vName == 'customfieldtypes');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-calculate2', JText::_('COM_QAZAP_SIDEBAR_TAX_RULES'), $link = 'index.php?option=com_qazap&view=taxes', $vName == 'taxes');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-gift2', JText::_('COM_QAZAP_SIDEBAR_COUPONS'), $link = 'index.php?option=com_qazap&view=coupons', $vName == 'coupons');
		
		QZHtmlSidebar::addEntry('product', 'qzicon-pencil2', JText::_('COM_QAZAP_SIDEBAR_REVIEWS'), $link = 'index.php?option=com_qazap&view=reviews', $vName == 'reviews');
		
		QZHtmlSidebar::addParent('manufacturer', JText::_('COM_QAZAP_SIDEBAR_MANUFACTURER'), '');
		
		QZHtmlSidebar::addEntry('manufacturer', 'qzicon-factory', JText::_('COM_QAZAP_SIDEBAR_MANUFACTURER_MANAGER'), $link = 'index.php?option=com_qazap&view=manufacturers', $vName == 'manufacturers');
		
		QZHtmlSidebar::addEntry('manufacturer', 'qzicon-folder6', JText::_('COM_QAZAP_SIDEBAR_MANUFACTURER_CATEGORIES'), $link = 'index.php?option=com_qazap&view=manufacturercategories', $vName == 'manufacturercategories');
		
		QZHtmlSidebar::addParent('membership', JText::_('COM_QAZAP_SIDEBAR_MEMBERSHIP'), '');
		
		QZHtmlSidebar::addEntry('membership', 'qzicon-starburst', JText::_('COM_QAZAP_SIDEBAR_MEMBERSHIP_MANAGER'), $link = 'index.php?option=com_qazap&view=memberships', $vName == 'memberships');
		
		QZHtmlSidebar::addEntry('membership', 'qzicon-user7', JText::_('COM_QAZAP_SIDEBAR_MEMBER_MANAGER'), $link = 'index.php?option=com_qazap&view=members', $vName == 'members');
		
		QZHtmlSidebar::addParent('order', JText::_('COM_QAZAP_SIDEBAR_ORDER'), '');

		QZHtmlSidebar::addEntry('order', 'qzicon-paste2', JText::_('COM_QAZAP_SIDEBAR_ORDER_MANAGER'), $link = 'index.php?option=com_qazap&view=orders', $vName == 'orders');
		
		QZHtmlSidebar::addParent('users', JText::_('COM_QAZAP_SIDEBAR_USERS'), '');

		QZHtmlSidebar::addEntry('users', 'qzicon-user6', JText::_('COM_QAZAP_SIDEBAR_USER_MANAGER'), $link = 'index.php?option=com_qazap&view=userinfos', $vName == 'userinfos');		
		
		QZHtmlSidebar::addEntry('users', 'icon-heart', JText::_('COM_QAZAP_SIDEBAR_WISHLIST'), $link = 'index.php?option=com_qazap&view=wishlist', $vName == 'wishlist');
		
		QZHtmlSidebar::addEntry('users', 'qzicon-pushpin', JText::_('COM_QAZAP_SIDEBAR_WAITINGLIST'), $link = 'index.php?option=com_qazap&view=waitinglist', $vName == 'waitinglist');

		QZHtmlSidebar::addEntry('users', 'qzicon-file', JText::_('COM_QAZAP_SIDEBAR_USER_FIELD_MANAGERS'), $link = 'index.php?option=com_qazap&view=userfields', $vName == 'userfields');
		
		QZHtmlSidebar::addParent('vendor', JText::_('COM_QAZAP_SIDEBAR_VENDOR'), '');
		
		QZHtmlSidebar::addEntry('vendor', 'qzicon-brain', JText::_('COM_QAZAP_SIDEBAR_VENDOR_MANAGER'), $link = 'index.php?option=com_qazap&view=vendors', $vName == 'vendors');
		
	
		QZHtmlSidebar::addEntry('vendor', 'qzicon-certificate', JText::_('COM_QAZAP_SIDEBAR_VENDOR_GROUPS'), $link = 'index.php?option=com_qazap&view=vendor_groups', $vName == 'vendor_groups');

		QZHtmlSidebar::addEntry('vendor', 'qzicon-profile', JText::_('COM_QAZAP_SIDEBAR_VENDOR_FIELD_MANAGERS'), $link = 'index.php?option=com_qazap&view=vendorfields', $vName == 'vendorfields');
		
		QZHtmlSidebar::addEntry('vendor', 'qzicon-coin', JText::_('COM_QAZAP_SIDEBAR_PAYMENT_MANAGER'), $link = 'index.php?option=com_qazap&view=payments', $vName == 'payments');
		
		QZHtmlSidebar::addParent('configuration', JText::_('COM_QAZAP_SIDEBAR_CONFIGURATION'), '');
		
		QZHtmlSidebar::addEntry('configuration', 'qzicon-settings', JText::_('COM_QAZAP_SIDEBAR_GLOBAL_CONFIGURATION'), $link = 'index.php?option=com_config&view=component&component=com_qazap', $vName == '');
			
		QZHtmlSidebar::addEntry('configuration', 'qzicon-home12', JText::_('COM_QAZAP_SIDEBAR_SHOP_MANAGER'), $link = 'index.php?option=com_qazap&task=shops.show', $vName == 'shop');
				
		QZHtmlSidebar::addEntry('configuration', 'qzicon-bubble-forward2', JText::_('COM_QAZAP_SIDEBAR_SHIPMENT_METHODS'), $link = 'index.php?option=com_qazap&view=shipmentmethods', $vName == 'shipmentmethods');
		
		QZHtmlSidebar::addEntry('configuration', 'qzicon-credit2', JText::_('COM_QAZAP_SIDEBAR_PAYMENT_METHODS'), $link = 'index.php?option=com_qazap&view=paymentmethods', $vName == 'paymentmethods');
		
		QZHtmlSidebar::addEntry('configuration', 'qzicon-envelop2', JText::_('COM_QAZAP_SIDEBAR_EMAIL_TEMPLATES'), $link = 'index.php?option=com_qazap&view=emailtemplates', $vName == 'emailtemplates');
		
		QZHtmlSidebar::addParent('advanced', JText::_('COM_QAZAP_SIDEBAR_ADVANCED_OPTIONS'), '');
		
		QZHtmlSidebar::addEntry('advanced', 'qzicon-coins', JText::_('COM_QAZAP_SIDEBAR_CURRENCY_MANAGER'), $link = 'index.php?option=com_qazap&view=currencies', $vName == 'currencies');

		QZHtmlSidebar::addEntry('advanced', 'qzicon-screwdriver2', JText::_('COM_QAZAP_SIDEBAR_UNIT MANAGER'), $link = 'index.php?option=com_qazap&view=productuoms', $vName == 'productuoms');
		
		QZHtmlSidebar::addEntry('advanced', 'qzicon-cog3', JText::_('COM_QAZAP_SIDEBAR_COUNTRY_MANAGER'), $link = 'index.php?option=com_qazap&view=countries', $vName == 'countries');
		
		QZHtmlSidebar::addEntry('advanced', 'icon-tree', JText::_('COM_QAZAP_SIDEBAR_STATES_MANAGER'), $link = 'index.php?option=com_qazap&view=states', $vName == 'states');
		
		QZHtmlSidebar::addEntry('advanced', 'qzicon-health', JText::_('COM_QAZAP_SIDEBAR_ORDER_STATUS_MANAGER'), $link = 'index.php?option=com_qazap&view=orderstatuses', $vName == 'orderstatuses');
		
		QZHtmlSidebar::addEntry('advanced', 'qzicon-tools', JText::_('COM_QAZAP_SIDEBAR_REINSTALL_REPAIR'), $link = 'index.php?option=com_qazap&view=install', $vName == 'install');		

	}
	
	public static function sortArrayByArray($toSort = array(), $sortByValuesAsKeys = array()) 
	{
		$toSort = (array) $toSort;
		$commonKeysInOrder = array_intersect_key(array_flip($sortByValuesAsKeys), $toSort);
		$sorted = array_replace($commonKeysInOrder, $toSort);

		return $sorted;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_qazap';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) 
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
	
	public static function getManufacturerName($id)
	{
		$id = (int) $id;
		
		if(empty($id))
		{
			return null;
		}
		
		if(!isset(static::$_manufactuers[$id]))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
				  	->select('m.manufacturer_name')
						->from('#__qazap_manufacturers as m')
						->where('m.id = ' . $id);
			$db->setQuery($query);
			$manufacturer_name = $db->loadResult();
			
			if(empty($manufacturer_name))
			{
				static::$_manufactuers[$id] = null;
			}
			else
			{
				static::$_manufactuers[$id] = $manufacturer_name;
			}
		}

		return static::$_manufactuers[$id];
	}
	
	public static function getCategoryName($id)
	{
		$id = (int) $id;
		
		if(empty($id))
		{
			return null;
		}
		
		if(!isset(static::$_categories[$id]))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
				  	->select('c.category_name')
						->from('#__qazap_productcategories as c')
						->where('c.id = ' . $id);
						
			$db->setQuery($query);
			$category_name = $db->loadResult();
			
			if(empty($category_name))
			{
				static::$_categories[$id] = null;
			}
			else
			{
				static::$_categories[$id] = $category_name;
			}						
		}
		
		return static::$_categories[$id];
	}
	
	public static function searchFilter($values,$name,$instance)
	{
		foreach($values as $value=>$opt_name) 
		{
			$options[] = JHtml::_('select.option', (string) $value, JText::_($opt_name));
		}
		
		$html = JHtml::_('select.genericlist', $options, $name, '', 'value', 'text', $instance, $name);
		return $html;
	}
	
	public static function currencyFormat($value, $currency = null)
	{
		$value 		= (float) $value;
		$params		= QZApp::getConfig();	
		$currency = $currency ? $currency : (int) $params->get('default_currency');
		$currency = self::getCurrency($currency);		
		return number_format($value, $currency->decimals);			
	}
	
	public static function setDisplayCurrency($currency_id)
	{
		$app = JFactory::getApplication();
		$app->setUserState('com_qazap.currency.display.currency_id', $currency_id);
	}
	
	public static function getDisplayCurrency()
	{
		$app = JFactory::getApplication();
		$config = QZApp::getConfig();
		$shopCurrency = (int) $config->get('default_currency', 111);		
		$displayCurrency = (int) $app->getUserState('com_qazap.currency.display.currency_id', $shopCurrency);
		
		return $displayCurrency;
	}
	
	public static function currencyDisplay($value, $round = false, $showZero = false) 
	{
		$value = (float) $value;
		$config = QZApp::getConfig();
		$shopCurrency = (int) $config->get('default_currency', 111);
		$userCurrency = self::getDisplayCurrency();		
		
		if(!$value && !$showZero)
		{
			return null;
		}
		
		if($userCurrency != $shopCurrency) 
		{
			$baseCurrency = self::getCurrency($shopCurrency);
			$newCurrency = self::getCurrency($userCurrency);
			
			if(empty($baseCurrency) || empty($newCurrency))
			{
				JError::raiseWarning(1, 'Could not fetch currency information from database');
				return $value;
			}
			
			$decimals = $round ? 0 : $newCurrency->decimals;
			
			if(!$value)
			{
				$value = number_format($value, $decimals, $newCurrency->decimals_symbol, $newCurrency->thousand_separator);
				return str_replace('{value}', $value, str_replace('{symbol}', $newCurrency->currency_symbol, $newCurrency->format));	
			}
			
			if($baseCurrency->exchange_rate > 0 && $newCurrency->exchange_rate > 0)
			{
				$value = ($value * $newCurrency->exchange_rate) / $baseCurrency->exchange_rate;
			}
			elseif($newCurrency->exchange_rate > 0)
			{
				$value = ($value * $newCurrency->exchange_rate);
			}
			else
			{
				$exchange = QZExchange::getInstance();
				$value = $exchange->convert($value, $baseCurrency->code3letters, $newCurrency->code3letters);
				
				if(!$value)
				{
					if($exchange->getError())
					{
						JError::raiseWarning(1, $exchange->getError());
					}					
					return false;
				}
			}			
		
			$value = number_format($value, $decimals, $newCurrency->decimals_symbol, $newCurrency->thousand_separator);	
			return str_replace('{value}', $value, str_replace('{symbol}', $newCurrency->currency_symbol, $newCurrency->format));		
		}		
		else
		{
			$orgCurrency = self::getCurrency($shopCurrency);
			
			if(empty($orgCurrency))
			{
				JError::raiseWarning(1, 'Could not fetch currency information from database');
				return $value;
			}
			
			$decimals = $round ? 0 : $orgCurrency->decimals;
			
			if(!$value)
			{
				$value = number_format($value, $decimals, $orgCurrency->decimals_symbol, $orgCurrency->thousand_separator);
				return str_replace('{value}', $value, str_replace('{symbol}', $orgCurrency->currency_symbol, $orgCurrency->format));	
			}			
					
			$value = number_format($value, $decimals, $orgCurrency->decimals_symbol, $orgCurrency->thousand_separator);
			return str_replace('{value}', $value, str_replace('{symbol}', $orgCurrency->currency_symbol, $orgCurrency->format));			
		} 

		return $value;		
	}

	
	public static function orderCurrencyDisplay($value, $orderCurrency, $userCurrency = null, $exchange_rate = null)
	{
		$value 		= (float) $value;
		$params		= QZApp::getConfig();
		
		if(!$value)
		{
			return null;
		}		
		
		if($userCurrency === null || ($orderCurrency == $userCurrency))
		{
			$orgCurrency = self::getCurrency($orderCurrency);	
			$value = number_format($value, $orgCurrency->decimals, $orgCurrency->decimals_symbol, $orgCurrency->thousand_separator);
			$format = $orgCurrency->format;				
			$value = str_replace('{value}', $value, str_replace('{symbol}', $orgCurrency->currency_symbol, $format));			
		}		
		else 
		{		
			$newCurrency = self::getCurrency($userCurrency);			
			$original_value = $value;			
			$value = ($original_value * $exchange_rate);			
			$exchange = $original_value / $value;				
			$value = number_format($value, $newCurrency->decimals, $newCurrency->decimals_symbol, $newCurrency->thousand_separator);	
			$format = $newCurrency->format;			
			$value = str_replace('{value}', $value, str_replace('{symbol}', $newCurrency->currency_symbol, $format));			
		}		

		return $value;			
	}
	
	public static function getCurrencyInfo()
	{
		$displayedCurrency_id = self::getDisplayCurrency();
		$currency = self::getCurrency($displayedCurrency_id);
		$currency->exchange_rate = self::getExchangeRate();
		
		return $currency;		
	}
	
	public static function getExchangeRate($fromCurrency_id = null, $toCurrency_id = null)
	{
		$config = QZApp::getConfig();
		$fromCurrency_id = $fromCurrency_id ? (int) $fromCurrency_id : (int) $config->get('default_currency', 111);
		$toCurrency_id = $toCurrency_id ? $toCurrency_id : self::getDisplayCurrency();
		
		if($fromCurrency_id == $toCurrency_id)
		{
			return 1.0;
		}
					
		$exchange = QZExchange::getInstance();
		$fromCurrency = self::getCurrency($fromCurrency_id);			
		$toCurrency = self::getCurrency($toCurrency_id);
		
		if($fromCurrency->exchange_rate > 0 && $toCurrency->exchange_rate > 0)
		{
			$rate = ($toCurrency->exchange_rate / $fromCurrency->exchange_rate);
		}
		elseif($toCurrency->exchange_rate > 0)
		{
			$rate = $toCurrency->exchange_rate;
		}
		elseif(!$rate = $exchange->getExchange($fromCurrency->code3letters, $toCurrency->code3letters))
		{
			JError::raiseWarning(1, $exchange->getError());
			return false;
		}
		
		return $rate;
	}


	protected static function getCurrency($currency_id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		if(!isset(self::$_cache['currency.sql']))
		{
			// Build the currency sql query
			$query->clear();
			$query->select('id, ordering, state, currency, exchange_rate, currency_symbol, code3letters, numeric_code, decimals, '.
											'decimals_symbol, format, thousand_separator');
			$query->from('`#__qazap_currencies`');
			$query->where('`id` = %d');
			self::$_cache['currency.sql'] = $query;
		}
		
		if(!isset(self::$_currencies[$currency_id]))
		{
			$db->setQuery(sprintf(self::$_cache['currency.sql'], (int) $currency_id));
			$currency = $db->loadObject();
			
			if(empty($currency))
			{
				JError::raiseWarning(1, 'Could not fetch currency against currency code ' . $currency_id);
				return false;
			}
			
			self::$_currencies[$currency_id] = $currency;
		}
		
		return self::$_currencies[$currency_id];			
	}

	
	public static function orderStatusNameByCode($status_code)
	{
		static $cache = array();
		
		if(!isset($cache[$status_code]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('status_name')
						->from('#__qazap_order_status')
						->where('status_code = ' . $db->quote($status_code));
			$db->setQuery($query);
			$result = $db->loadResult();
			
			if(empty($result))
			{
				$cache[$status_code] = $status_code;
			}
			else
			{
				$cache[$status_code] = $result;
			}
		}
				
		return $cache[$status_code];
	}	
	
	/**
	* Method to display date with respective timezome offset
	* 
	* @param	mixed		(integer/string)	$value	Date or "NOW" for present time
	* @param	string 										$filter USER / SERVER
	* @param	string										$format	Date display format
	* 
	* @return string
	* @since	1.0
	*/
	public static function displayDate($value, $format = 'Y-m-d H:i:s', $filter = 'USER')
	{
		// Handle the special case for "now".
		if (strtoupper($value) == 'NOW')
		{
			$value = strftime($format);
		}	
		
		// Get some system objects.
		$config = JFactory::getConfig();
		$user = JFactory::getUser();

		// If a known filter is given use it.
		switch (strtoupper($filter))
		{
			case 'SERVER':
				// Convert a date to UTC based on the server timezone.
				if ((int) $value)
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($value, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					// Transform the date string.
					$value = $date->format($format, true, false);
				}

				break;

			case 'USER':
				// Convert a date to UTC based on the user timezone.
				if ((int) $value)
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($value, 'UTC');

					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$value = $date->format($format, true, false);
				}

				break;
		}	

		return $value;		
	}	
	
	public static function getUserFields($type = null) 
	{		
		$app = JFactory::getApplication();		
		$addressType = ($type === null) ? $app->getUserState('com_qazap.userinfo.address_type', 'bt') : $type;
		
		$addressType = strtolower($addressType);
		
		if(!in_array($addressType, array('bt', 'st')))
		{
			return false;
		}
		
		if(!isset(self::$_userfields[$addressType]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
						
			if(!isset(self::$_cache['userfield.sql']))
			{
				$select = array('id', 'field_name', 'max_length', 'field_title', 'description', 'field_type', 'values', 'required', 'show_in_userbilling_form', 'show_in_shipment_form', 'read_only');
				$select = array_map(function($val) { return '`'.$val.'`';}, $select);
				
				$query->clear();
				$query->select($select);
				$query->from('#__qazap_userfields');
				$query->where('state = 1');
				$query->where('%s = 1');
				$query->group($select);
				$query->order('ordering ASC');
				self::$_cache['userfield.sql'] = (string) $query;
			}
			
			if($addressType == 'bt')
			{
				$db->setQuery(sprintf(self::$_cache['userfield.sql'], (string) 'show_in_userbilling_form'));
				$fields = $db->loadObjectList();					
			}
			else
			{
				$db->setQuery(sprintf(self::$_cache['userfield.sql'], (string) 'show_in_shipment_form'));
				$fields = $db->loadObjectList();
			}
			
			if(count($fields))
			{
				$fieldsets = array();
				$element = '';
							
				foreach($fields as $key=>$field) 
				{
					if(strpos($field->field_type, ':') !== false) 
					{
						$type = explode(':', $field->field_type);
						$field_type = $type[0];
						$field_attr = $type[1];
					} 
					else 
					{
						$field_type = $field->field_type;
						$field_attr = '';				
					}
					
					$label = 'COM_QAZAP_USERFIELD_'.strtoupper($field->field_title);
					$description = htmlspecialchars(trim($field->description));	
					$name = $field->field_title;
					$column_name = $field->field_title;
					$readonly = $field->read_only ? 'readonly="readonly"' : '';
					$required = $field->required ? 'required="required"' : '';
					$attr = $readonly.' '.$required;
					
					if($field_type == 'text' or $field_type == 'textarea' or $field_type == 'email' or $field_type == 'url') 
					{
						$attr = $attr.' size="' . (int) $field->max_length.'"';
					}
					
					if($field_attr !== '') 
					{
						$attr = $attr . ' ' . $field_attr . '="' . $field_attr . '"';
					}

					if($key == 0 && $field_type != 'fieldset') 
					{
						$element .= '<fieldset name="userinfos_fields">';			
					}
										
					if($field_type == 'list' or $field_type == 'radio')
					{ 
						$field->values = (string) $field->values;
						$options = array();
						
						if(strpos($field->values, ',') !== false)
						{							
							$field_values = (array) explode(',', trim($field->values));							
							if(strpos($field->values, '=>') !== false)
							{
								foreach($field_values as $field_value)
								{
									$keyValue = explode('=>', trim($field_value));
									$options[trim($keyValue[0])] = trim($keyValue[1]);
								}
							}
							elseif(strpos($field->values, '=') !== false)
							{
								foreach($field_values as $field_value)
								{
									$keyValue = explode('=', trim($field_value));
									$options[trim($keyValue[0])] = trim($keyValue[1]);
								}
							}
							else
							{
								$options = $field_values;						
							}							
						}
						
						$options = (array) $options;

						if($field_type == 'radio') 
						{
							$attr = $attr.' class="btn-group"';
							$element .=	'<field 
										name="' . $name . '" 
										type="' . $field_type . '" 
										label="' . $label . '" 
										description="' . $description . '" 
										default="' . $options[0] . '" 
										' . $attr . ' 
										>';					
						} 
						else 
						{
							$element .=	'<field 
										name="' . $name . '" 
										type="' . $field_type . '" 
										label="' . $label . '" 
										description="' . $description . '" 
										' . $attr . ' 
										>';					
						}
						foreach($options as $v=>$title) 
						{
							$element .= '<option value="' . (string) $v .'">' . JText::_((string) $title) . '</option>';
						}								
						$element .=	'</field>';				
					} 
					elseif($field_type == 'fieldset') 
					{
						if($key > 0) 
						{
							$element .= '</fieldset>';
							$fieldsets[] = new SimpleXMLElement($element);
							$element = '<fieldset name="'.$name.'" label="'.$label.'" description="' . $description . '" '.$attr.' >';
						} 
						else 
						{
							$element .= '<fieldset name="'.$name.'" label="'.$label.'" description="' . $description . '" '.$attr.' >';
						}
					}
					else 
					{
						$element .=	'<field 
									name="' . $name . '" 
									type="' . $field_type . '" 
									label="' . $label . '" 
									description="' . $description . '" 
									' . $attr . ' 
									></field>';				
					}			
				}
				
				$element .= '</fieldset>';	
				$fieldsets[] = new SimpleXMLElement($element);

				self::$_userfields[$addressType] = $fieldsets;					
			}
			else
			{
				self::$_userfields[$addressType] = null;
			}				
		}
		
		return self::$_userfields[$addressType];		
	}	
	
	// get Vendor Fields
	
	public static function getVendorFields() 
	{		
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
	
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			 ->select('v.*')
			 ->from('`#__qazap_vendorfields` as v')
			 ->where('v.state = 1');
		$sql->order('v.ordering ASC');
		$db->setQuery($sql);
		$fields = $db->loadObjectList();
		//$element = '<fieldset name="userinfos_fields">';
		$fieldsets = array();
		$element = '';
		foreach($fields as $key=>$field) 
		{			
			if(strpos($field->field_type, ':') !== false) 
			{
				$type = explode(':', $field->field_type);
				$field_type = $type[0];
				$field_attr = $type[1];
			} 
			else 
			{
				$field_type = $field->field_type;
				$field_attr = '';				
			}			
			
			$label = $field->field_title;
			$label = 'COM_QAZAP_VENDORFIELD_'.strtoupper($label);
			$description = htmlspecialchars(trim($field->description));		
			$name = $field->field_title;
			$column_name = $field->field_title;
			$readonly = $field->read_only ? 'readonly="readonly"' : '';
			$required = $field->required ? 'required="required"' : '';
			$attr = $readonly.' '.$required;
			if($field_type == 'text' or $field_type == 'textarea' or $field_type == 'email' or $field_type == 'url') 
			{
				$attr = $attr.' size="'.$field->max_length.'"';
			}
			if($field_attr !== '') 
			{
				$attr = $attr.' '.$field_attr.'="'.$field_attr.'"';
			}
			
			if($key == 0 && $field_type != 'fieldset') 
			{
				$element .= '<fieldset name="userinfos_fields">';
			}
			
			if($field_type == 'list' or $field_type == 'radio')
			{ 
				$field->values = (string) $field->values;
				$options = array();
				
				if(strpos($field->values, ',') !== false)
				{							
					$field_values = (array) explode(',', trim($field->values));							
					if(strpos($field->values, '=>') !== false)
					{
						foreach($field_values as $field_value)
						{
							$keyValue = explode('=>', trim($field_value));
							$options[trim($keyValue[0])] = trim($keyValue[1]);
						}
					}
					elseif(strpos($field->values, '=') !== false)
					{
						foreach($field_values as $field_value)
						{
							$keyValue = explode('=', trim($field_value));
							$options[trim($keyValue[0])] = trim($keyValue[1]);
						}
					}
					else
					{
						$options = $field_values;						
					}							
				}
				
				$options = (array) $options;

				if($field_type == 'radio') 
				{
					$attr = $attr.' class="btn-group"';
					$element .=	'<field 
								name="' . $name . '" 
								type="' . $field_type . '" 
								label="' . $label . '" 
								description="' . $description . '" 
								default="' . $options[0] . '" 
								' . $attr . ' 
								>';					
				} 
				else 
				{
					$element .=	'<field 
								name="'.$name.'" 
								type="'.$field_type.'" 
								label="'.$label.'" 
								description="'.$description.'" 
								'.$attr.' 
								>';					
				}
				foreach($options as $v=>$title) 
				{
					$element .= '<option value="' . (string) $v .'">' . JText::_((string) $title) . '</option>';
				}
												
				$element .=	'</field>';				
			} 
			elseif($field_type == 'fieldset') 
			{
				if($key > 0) 
				{
					$element .= '</fieldset>';
					$fieldsets[] = new SimpleXMLElement($element);
					$element = '<fieldset name="'.$name.'" label="'.$label.'" description="' . $description . '" '.$attr.' >';
				} 
				else 
				{
					$element .= '<fieldset name="'.$name.'" label="'.$label.'" description="' . $description . '" '.$attr.' >';
				}
			}
			elseif($field_type == 'media')
			{
				$element .=	'<field 
							name="'.$name.'" 
							type="qazapmedia" 
							label="'.$label.'" 
							filter="raw" 
							group="sellers"							
							manual="0"
							medium="0"
							imagesonly="1"
							description="' . $description . '" 
							'.$attr.' 
							></field>';				
			}			
			else 
			{
				$element .=	'<field 
							name="'.$name.'" 
							type="'.$field_type.'" 
							label="'.$label.'" 
							description="' . $description . '" 
							'.$attr.' 
							></field>';				
			}			
		}
		$element .= '</fieldset>';	
		$fieldsets[] = new SimpleXMLElement($element);

		return $fieldsets;		
	}


	// Get all possible prices	
	public static function getFinalPrice($product, $type = null, $forceload = false)
	{
		if(!is_object($product))
		{
			return null;
		}
		
		if(!isset(static::$_productPrices[$product->product_id]) || $forceload)
		{
			$prices = new QZPrices($product);
			static::$_productPrices[$product->product_id] = $prices->get();			
		}
		
		if($type === null)
		{
			return static::$_productPrices[$product->product_id];
		}
		
		$prices = static::$_productPrices[$product->product_id];
		
		if(property_exists($prices, $type))
		{
			return $prices->$type;
		}
		
		return null;
	}


	/**
	* Method to display fields with multiple languages in product and category edit form
	* @param Form data. $form
	* @param Field to be displayed. $field
	* @param Required field or not. $required
	* 
	* @return HTML for display
	*/
	public static function displayFieldByLang($form, $field, $required = false)
	{	
		$fieldsets = $form->getFieldsets($field);
		$fieldset = $fieldsets[$field.'_set'];
		$fields = $form->getFieldset($fieldset->name);
		$asterix = $required ? '<span class="star">&nbsp;*</span>' : '';
		
		list($label, $input, $multiLang) = self::getLangFieldHTML($fields);
		
		$data = $multiLang ? ' data-qazap="tab-set"' : '';
		$class = $multiLang ? ' qz-tab-set' : '';
		
		$html = '<div class="control-group'.$class.'"'.$data.'>'."\n";
		$html .= '	<div class="control-label">'."\n";
		$html .= 			$label;
		$html .= '	</div>'."\n";	
		$html .= '	<div class="controls">'."\n";
		$html .=			$input;
		$html .= '	</div>'."\n";
		$html .= '</div>'."\n";
		
		return $html;
	}
	
	
	/**
	* Prepare display HTML for displayFieldByLang()
	* @param Form fields $fields
	* 
	* @return HTML tab label and contents as Array.
	*/	
	protected static function getLangFieldHTML($fields)
	{
		$label = '';
		$input = '';		
		$setTitle = false;
		$multiLang = false;
		
		$lang = JLanguageHelper::getLanguages('lang_code');
		
		if(count($fields) > 1)
		{
			$multiLang = true;
		}
		
		$i = 0;
		foreach($fields as $field)	
		{
			if(!$multiLang)
			{
				$label .= $field->label;
				$input .= $field->input;
			}
			else
			{				
				if(!$setTitle)
				{
					$setTitle = '<span class="qz-inline-label">' . $field->label . '</span>';					
				}
				else
				{
					$setTitle .= '<span class="hide">' . $field->label . '</span>';	
				}
				
				$active = ($i == 0) ? ' active"' : '';
				
				$language = $lang[$field->getAttribute('language')];
				$label .= '<span class="qz-language-selector-group"><button type="button" class="btn btn-mini qz-language-selector'.$active.'" rel="#'.$field->id.'_qztab">'.JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true).'</button></span>';
				
				
				$input .= '		<div id="'.$field->id.'_qztab" class="qz-language-tab-pane'.$active.'">'."\n";
				$input .= '			'.$field->input;
				$input .= '		</div>'."\n";
				$i++;
			}
		}
		
		if($multiLang && $setTitle)
		{
			$label = $setTitle.$label; 
		}
		
		return array($label, $input, $multiLang);
	}

	public static function displayAddress($address, $skip_fields = array())
	{
		if(!$address)
		{
			return false;
		}

		$address = (array) $address;
		
		if(isset($address['country']) && !isset($address['country_name']) && is_numeric($address['country']))
		{
			$address['country_name'] = QZDisplay::getCountryNamebyID($address['country']);
		}
		
		if(isset($address['states_territory']) && !isset($address['state_name']) && is_numeric($address['states_territory']))
		{
			$address['state_name'] = QZDisplay::getStateNamebyID($address['states_territory']);
		}				
		
		$company = null;
		$name = array();
		$address_lines = array();
		$placewithzip = array();
		$countrywithstate = array();
		$others = array();

		foreach($address as $k => $v)
		{
			if(!in_array($k, $skip_fields))
			{
				if($v == '0')
				{
					$v = JText::_('COM_QAZAP_USERFIELD_'.strtoupper($k)) . ': ' . JText::_('JNO');
				}
				elseif($v == '1')
				{
					$v = JText::_('COM_QAZAP_USERFIELD_'.strtoupper($k)) . ': ' . JText::_('JYES');
				}
				else
				{
					$v = JText::_(trim($v));
				}				
				
				switch($k) 
				{
					case 'company':
						$company = $v;
						break;
					case 'title':
						$name[] = $v;
						break;							
					case 'first_name':
						$name[] = $v;
						break;	
					case 'middle_name':
						$name[] = $v;
						break;	
					case 'last_name':
						$name[] = $v;
						break;
					case 'city':	
						$placewithzip[] = $v;
						break;
					case 'zip':	
						$placewithzip[] = $v;
						break;
					case 'address_1':	
						$address_lines[] = $v;
						break;
					case 'address_2':	
						$address_lines[] = $v;
						break;
					case 'country_name':	
						$countrywithstate[] = $v;
						break;
					case 'state_name':	
						$countrywithstate[] = $v;
						break;
					default:
						$others[] = $v;																																													
				}
			}
		}
		
		$html = array();
		
		if($company)
		{
			$html[] = '<div>'.$company.'</div>';
		}
		if(count($name))
		{
			$html[] = '<div>'.implode(' ', $name).'</div>';
		}
		if(count($address_lines))
		{
			$html[] = '<div>'.implode('</div><div>', $address_lines).'</div>';	
		}
		if(count($placewithzip))
		{
			$html[] = '<div>'.implode(' ', $placewithzip).'</div>';	
		}		
		if(count($countrywithstate))
		{
			$html[] = '<div>'.implode(' ', $countrywithstate).'</div>';	
		}
		if(count($others))
		{
			$html[] = '<div>'.implode('</div><div>', $others).'</div>';	
		}				
			
		return implode($html);
	}
	
	/**
	* Method to retrive plugin name by @id
	* 
	* 
	*/
	public static function getPlugin($id)
	{
		$id = (int) $id;
		
		if(empty($id))
		{
			return null;
		}
		
		if(!(isset(static::$_plugins[$id])))
		{
			$db = JFactory::getDBO();
			$user = JFactory::getUser();
			$query = $db->getQuery(true)
						->select('*')
						->from('#__extensions')
						->where('extension_id = ' . $id)
						->where('type = '. $db->quote('plugin'))
						->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
						
			$db->setQuery($query);
			$plugin = $db->loadObject();
			
			if(empty($plugin))
			{
				static::$_plugins[$id] = null;
			}
			else
			{
				static::$_plugins[$id] = $plugin;
			}						
		}
		
		return static::$_plugins[$id];
	}
	
	public static function validateEmail($email)
	{
		if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))
		{
			return false; 
		}
		return true;
	}
	
	public static function displayFileSize($bytes)
	{
		$bytes = (float) $bytes;
		
		if ($bytes > 0)
		{
			$unit = intval(log($bytes, 1024));
			
			$units = array(
										'COM_QAZAP_FILE_SIZE_BYTE', 'COM_QAZAP_FILE_SIZE_KILOBYTE', 
										'COM_QAZAP_FILE_SIZE_MEGABYTE', 'COM_QAZAP_FILE_SIZE_GIGABYTE'
										);

			if(array_key_exists($unit, $units) === true)
			{
				return sprintf('%s %s', number_format($bytes / pow(1024, $unit), 2), JText::_($units[$unit]));
			}
		}

		return $bytes;
	}
	
	public static function showURL($url)
	{
		$url = (string) $url;
		$url = JRoute::_($url);
		
		if(strpos($url, 'https:') === 0 || strpos($url, 'http:') === 0)
		{
			return $url;
		}
		
		if(strpos($url, JUri::base(true)) === 0)
		{
			$url = preg_replace('/\\'.JUri::base(true).'\//', '', $url, 1);;
		}
		
		return JUri::base() . $url;
	}	
	
}
