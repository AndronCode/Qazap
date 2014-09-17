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
 * @subpackage Qazap Currencies Module
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
/**
 * Helper for mod_languages
 *
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @since       1.6.0
 */
abstract class ModQazapCurrenciesHelper
{
	/**
	 * Gets a list of available currencies
	 *
	 * @param   JRegistry  &$params  module params
	 *
	 * @return  array
	 */
	public static function getCurrencies()
	{
		static $cache = null;
		
		if($cache == null)
		{
			$app	= JFactory::getApplication();
			$db = JFactory::getDbo();
			$config = QZApp::getConfig();
			$default = $config->get('default_currency', 111);
			$accepted = $config->get('accepted_currencies', array($default));
			$accepted = array_map('intval', $accepted);
			
			$query = $db->getQuery(true)
							->select('id AS currency_id, currency AS name, currency_symbol AS symbol, code3letters AS code')
							->from('`#__qazap_currencies`')
							->where('id IN (' . implode(',', $accepted) . ')')
							->order('ordering ASC');
							
			$db->setQuery($query);
			$cache = $db->loadObjectList();		
		}
		
		return $cache;		
	}
	
	public static function getOptions(&$params)
	{
		$list = self::getCurrencies();
		$options = array();
		
		if(!empty($list))
		{
			foreach($list as $currency)
			{
				$name = $params->get('full_name', 1) ? $currency->name . ' - ' . $currency->code : $currency->code;
				$options[] = JHtml::_('select.option', (int) $currency->currency_id, $name);
			}
		}
		
		return $options;
	}
	
	public static function getList(&$params)
	{
		$options = self::getOptions($params);
		$active = QZHelper::getDisplayCurrency();
		
		return JHtml::_('select.genericlist', $options, 'qazap_currency_id', 'onchange="this.form.submit();"', 'value', 'text', $active, 'qazap-currency-id', true);
	}
}
