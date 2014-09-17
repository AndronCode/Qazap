<?php
/**
 * qzproductobject.php
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
defined('JPATH_BASE') or die;

/**
 * Utility class for categories
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.5
 */
abstract class JHtmlQZProductObject
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  1.5
	 */
	protected static $options = null;

	
	public static function options()
	{
		if(static::$options == null)
		{
			static::$options = array(
														'product_name' => JText::_('COM_QAZAP_PRODUCT_NAME'),			
														'images' => JText::_('COM_QAZAP_IMAGES'),
														'prices' => JText::_('COM_QAZAP_PRICE'),
														'category_name' => JText::_('COM_QAZAP_CATEGORY_NAME'),
														'shop_name' => JText::_('COM_QAZAP_PRODUCT_VENDOR_NAME'),
														'manufacturer_name' => JText::_('COM_QAZAP_MANUFACTURER_NAME'),
														'short_description'=> JText::_('COM_QAZAP_SHORT_DESCRIPTION'),
														'product_sku' => JText::_('COM_QAZAP_PRODUCT_SKU'),
														'rating' => JText::_('COM_QAZAP_RATING'),
														'featured' => JText::_('COM_QAZAP_FEATURED'),
														'availability' => JText::_('COM_QAZAP_AVAILABILITY'),
														'product_description' => JText::_('COM_QAZAP_PRODUCT_DESCRIPTION'),
														'custom_fields' => JText::_('COM_QAZAP_CUSTOM_FIELDS'),
														'attributes' => JText::_('COM_QAZAP_ATTRIBUTES'),
														'membership' => JText::_('COM_QAZAP_MEMBERSHIP'),
														'product_length' => JText::_('COM_QAZAP_PRODUCT_LENGTH'),
														'product_width' => JText::_('COM_QAZAP_PRODUCT_WIDTH'),
														'product_height' => JText::_('COM_QAZAP_PRODUCT_HEIGHT'),
														'product_weight' => JText::_('COM_QAZAP_PRODUCT_WEIGHT'),
														'units_in_box' => JText::_('COM_QAZAP_UNITS_IN_BOX')						
												);			
		}
		
		return static::$options;	
	}
	
	public static function name($tag)
	{
		$tag = (string) $tag;
		$options = self::options();
		
		if(array_key_exists($tag, $options))
		{
			return $options[$tag];
		}
		
		return JText::_('COM_QAZAP_' . strtoupper($tag));
	}

}
