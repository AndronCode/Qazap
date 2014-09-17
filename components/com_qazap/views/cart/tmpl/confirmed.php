<?php
/**
 * confirmed.php
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

QZApp::loadCSS();			
QZApp::loadJS();
?>
<div class="order-confirm-page">
	<div class="page-header">
		<h1><?php echo JText::_('COM_QAZAP_CART_ORDER_PLACED') ?></h1>
	</div>
	<p class="order-confirm-msg"><?php echo JText::_('COM_QAZAP_CART_ORDER_PLACED_MSG') ?></p>
	<div class="order-details-box">
		<dl class="dl-horizontal">
			<dt><?php echo JText::_('COM_QAZAP_ORDERGROUP_NUMBER_LABEL') ?></dt>
			<dd><?php echo $this->escape($this->confirmedCart->ordergroup_number); ?></dd>
			<dt><?php echo JText::_('COM_QAZAP_ORDERS_ORDER_STATUS') ?></dt>
			<dd><?php echo QZHelper::orderStatusNameByCode($this->confirmedCart->order_status); ?></dd>
			<dt><?php echo JText::_('COM_QAZAP_ORDER_TOTAL') ?></dt>			
			<dd><?php echo QZHelper::orderCurrencyDisplay($this->confirmedCart->cart_total, $this->confirmedCart->order_currency, $this->confirmedCart->user_currency, $this->confirmedCart->currency_exchange_rate) ?></dd>			
		</dl>
	</div>
	<div class="other-links">
		<a href="<?php echo $this->continue_link ?>" class="btn"><?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?></a>
		<a href="<?php echo JRoute::_(QazapHelperRoute::getOrderdetailsRoute($this->confirmedCart->ordergroup_id)) ?>" class="btn"><?php echo JText::_('COM_QAZAP_CART_VIEW_THIS_ORDER') ?></a>
	</div>
</div>