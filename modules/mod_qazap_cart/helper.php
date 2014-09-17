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
 * @subpackage Qazap Cart Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_qazap/helpers/route.php';

/**
 * Helper for mod_articles_categories
 *
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @since       1.5.0
 */
abstract class ModQazapCartHelper
{
	/**
	 * Get list of articles
	 *
	 * @param   JRegistry  &$params  module parameters
	 *
	 * @return array
	 */
	public static function getCart(&$params)
	{
		$model = QZApp::getModel('Cart', array('ignore_request' => true), false);
		$cart = $model->getCart();
		
		if($cart === false)
		{
			JFactory::getApplication()->enqueueMessage($cart->getError(), 'error');
			return false;
		}
		
		return $cart;
	}
}
