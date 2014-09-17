<?php

/**
 * uom.php
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

abstract class QZUom 
{
	protected static $cache = array();
	protected static $error = null;

	public static function convert($value, $fromUnit, $toUnit, $type = null)
	{
		$conversion = self::getConversion($fromUnit, $toUnit, $type);
		
		if($conversion === false)
		{
			JFactory::getApplication()->enqueueMessage($error, 'error');
			return false;
		}
		
		$value = (float) ($value * $conversion);

		return $value;
	}	
	
	public static function getConversion($fromUnit, $toUnit, $type = null)
	{
		static $cache = array();
		$hash = md5('From:' . $fromUnit . '.To:' . $toUnit . '.Type:' . $type);
		
		if(!isset(static::$cache[$hash]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('id, exchange_rate')
						->from('#__qazap_product_uom AS a')
						->where('a.id IN (' . (int) $fromUnit . ',' . (int) $toUnit . ')');
						
			if(!empty($type))
			{
				$query->where('a.product_attributes = '. $db->quote($type));
			}						
			
			try
			{
				$db->setQuery($query);
				$exchangeRate = $db->loadObjectList('id');
			} 
			catch (Exception $e) 
			{
				static::$error = $e->getMessage();
				return false;				
			}
			
			if(count($exchangeRate) != 2)
			{
				static::$error = JText::_('COM_QAZAP_INVALID_PRODUCT_UOM');
				return false;
			}

			$value = ($exchangeRate[$toUnit]->exchange_rate) / ($exchangeRate[$fromUnit]->exchange_rate);
			static::$cache[$hash] = $value;
		}
		
		return static::$cache[$hash];
	}
}