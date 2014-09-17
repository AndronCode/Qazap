<?php
/**
 * default.php
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

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');

$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
$skip_address_fields = array('id', 'address_type', 'address_name', 'country', 'states_territory', 'user_id');
//qzdump($this->cart);exit;
?>
<div class="cart-page">
	<div class="page-header">
		<h1><?php echo JText::_('COM_QAZAP_CART_OVERVIEW') ?></h1>
	</div>
	<?php if($this->isEmpty) : ?>
		<p class="cart-empty-title"><?php echo JText::_('COM_QAZAP_CART_EMPTY') ?></p>
		<p class="cart-empty-msg-1"><?php echo JText::_('COM_QAZAP_CART_EMPTY_MSG_1') ?></p>
		<p class="cart-empty-msg-2"><?php echo JText::_('COM_QAZAP_CART_EMPTY_MSG_2') ?></p>
		<p class="start-shopping"><a href="<?php echo JUri::base(true) ?>"><?php echo JText::_('COM_QAZAP_START_SHOPPING') ?></a></p>
	<?php else : ?>
		<?php if(!empty($this->cart->billing_address) || !empty($this->cart->shipping_address)) : ?>
		<div class="row-fluid qazap-selected-addresses">
			<div class="address-container span6">
				<div class="user-address">
					<div class="address-title">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_BILLING_ADDRESS') ?>
						<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'edit_billto'))) ?>" class="pull-right">
							<?php echo JText::_('COM_QAZAP_CART_EDIT_ADDRESS') ?>
						</a>				
					</div>
					<div class="address">
						<?php if(!empty($this->cart->billing_address)) : ?>
						<?php echo QZHelper::displayAddress($this->cart->billing_address, $skip_address_fields) ?>
						<?php endif; ?>
					</div>			
				</div>		
			</div>
			<?php if(!$this->params->get('intangible') && !$this->params->get('downloadable')) : ?>
			<div class="address-container span6">
				<div class="user-address">
					<div class="address-title">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_SHIPPING_ADDRESS');
						if($this->user->guest)
						{
							$url = JRoute::_(QazapHelperRoute::getCartRoute(array('layout' => 'edit_shipto', 'id' => $this->cart->shipping_address['id'])));
						}
						else
						{
							$url = JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'select_shipto')));
						}				
						?>
						<a href="<?php echo $url ?>" class="pull-right">
							<?php echo JText::_('COM_QAZAP_CART_EDIT_ADDRESS') ?>
						</a>				
					</div>
					<div class="address">
						<?php if(!empty($this->cart->shipping_address)) : ?>
						<?php echo QZHelper::displayAddress($this->cart->shipping_address, $skip_address_fields) ?>
						<?php endif; ?>
					</div>			
				</div>		
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="row-fluid">
			<div class="span12">
				<h2 class="pull-right"><?php echo JText::_('COM_QAZAP_CART_TOTAL') .':&nbsp;'.QZHelper::currencyDisplay($this->cart->cart_total) ?></h2>
			</div>
		</div>
		<!--Display Products in Cart-->
		<?php echo $this->loadTemplate('products'); ?>
		<div class="row-fluid">
			<div class="span12">
				<h2 class="pull-right"><?php echo JText::_('COM_QAZAP_CART_TOTAL') .':&nbsp;'.QZHelper::currencyDisplay($this->cart->cart_total) ?></h2>
			</div>
		</div>
		<!--Display Coupon Field-->
		<?php echo $this->loadTemplate('coupon'); ?>
		<!--Display Checkout Options-->
		<?php echo $this->loadTemplate('checkout'); ?>
	<?php endif; ?>
</div>