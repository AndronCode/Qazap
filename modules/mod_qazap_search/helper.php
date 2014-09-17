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
 * @subpackage Qazap Search Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_qazap/helpers/route.php';

abstract class ModQazapSearchHelper
{
	
	/**
	 * Get list of categories
	 *
	 * @param   JRegistry  &$params  module parameters
	 *
	 * @return array
	 */
	public static function getCategoryOptions(&$params)
	{
		$options = array();
		$options = JHtml::_('qazapcategory.options', array(1));
		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_QAZAP_ALL_CATEGORIES')));
		return $options;
	}
	
	public static function getSearchImage($button_text)
	{
		$img = JHtml::_('image', 'searchButton.gif', $button_text, null, true, true);

		return $img;
	}
	
}
