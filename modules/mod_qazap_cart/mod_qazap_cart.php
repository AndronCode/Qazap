<?php
/**
 * mod_qazap_cart.php
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

// Include the helper functions only once
require_once dirname(__FILE__) . '/helper.php';

$cart 		= ModQazapCartHelper::getCart($params);
$products	= !empty($cart) ? $cart->getProducts(true) : array();
$count		= !empty($cart) ? $cart->getItemCount() : 0;
$cart_url	= QazapHelperRoute::getCartRoute();
$isEmpty	= !empty($products) ? false : true;
$config		= QZApp::getConfig();
$menuid 	= (int) $module->menuid;
$document	= JFactory::getDocument();

if(!empty($cart))
{
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
	require JModuleHelper::getLayoutPath('mod_qazap_cart', $params->get('layout', 'default'));
}
