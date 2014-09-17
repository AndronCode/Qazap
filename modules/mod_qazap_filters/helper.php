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
 * @subpackage Qazap Filters Module
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
 * Helper for mod_qazap_filters
 *
 * @package     Qazap.Site
 * @subpackage  mod_qazap_filters
 *
 * @since       1.0.0
 */
abstract class ModQazapFiltersHelper
{
	public static function getAttributes()
	{
		$filters = QZFilters::getInstance();
		$attributes = $filters->getAttributes();
		$error = $filters->getError();
		
		if($attributes === false && !empty($error))
		{
			JFactory::getApplication()->enqueueMessage($error);
		}
		
		return $attributes;
	}
	
	public static function getBrands()
	{
		$filters = QZFilters::getInstance();
		$brands = $filters->getBrands();
		$error = $filters->getError();
		
		if($brands === false && !empty($error))
		{
			JFactory::getApplication()->enqueueMessage($error);
		}
		
		return $brands;
	}	
	
	public static function getPrices()
	{
		$filters = QZFilters::getInstance();
		$prices = $filters->getPrices();
		$error = $filters->getError();
		
		if($prices === false && !empty($error))
		{
			JFactory::getApplication()->enqueueMessage($error);
		}
		
		if(empty($prices->min_price) && empty($prices->max_price))
		{
			$prices = false;
		}
		elseif($prices->min_price == $prices->max_price)
		{
			$prices = false;
		}
		
		return $prices;
	}
	
}
