<?php
/**
 * selected_coupon.php
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
defined('JPATH_BASE') or die;

// Store the data in a local variable before display
$coupon = $displayData;

if($coupon->math_operation == 'v')
{
	$amount = QZHelper::currencyDisplay($coupon->coupon_value);
}
else
{
	$amount = (float) $coupon->coupon_value . '%';
}

$html  = '<span class="qazap-coupon-title">' . JText::_('COM_QAZAP_CART_SELECTED_COUPON') . ':</span>';
$html .= ' <span class="qazap-coupon-code">' . $coupon->coupon_code . '</span>';
$html .= ' <span class="qazap-coupon-value">(' . $amount . ')</span>';

echo $html;
?>