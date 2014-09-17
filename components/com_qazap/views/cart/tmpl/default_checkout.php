<?php
/**
 * default_checkout.php
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
$tos = JHTML::link('#qazap-cart-tos-popup', JText::_('COM_QAZAP_TOS'), array('target' => '_blank', 'class' => 'fancybox-popup'));

?>
<?php if($this->confirm) : ?>
<form id="qazap-checkout-form" method="post" action="<?php echo JRoute::_(QazapHelperRoute::getCartRoute()) ?>">	
	<div class="row-fluid">
		<div class="qazap-custom-note-title"><?php echo JText::_('COM_QAZAP_CART_CUSTOMER_NOTES') ?></div>
		<textarea name="<?php echo $this->cnFieldName ?>" id="qazap-customer-note" class="span12"><?php echo $this->cart->customer_note ?></textarea>
	</div>
	<?php if($this->params->get('tos_acceptance', 1)) : ?>
	<div class="controls">
		<label class="checkbox">
		  <input type="checkbox" name="<?php echo $this->tosFieldName ?>" />
		  <span><?php echo JText::sprintf('COM_QAZAP_CART_ACCEPT_TOS', $tos) ?></span>
		</label>
		<div class="qazap-hidden-items">
			<div id="qazap-cart-tos-popup">
				<div class="qazap-popup">
					<div class="qazap-popup-title large">
						<button type="button" class="qazap-popup-close inline" title="<?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?>">×</button>						
						<h3><?php echo JText::_('COM_QAZAP_TOS') ?></h3>
					</div>	
					<div class="qazap-popup-content cart-tos">
						<?php echo $this->tos ?>
					</div>
				</div>
			</div>	
		</div>			
	</div>
	<?php endif; ?>	
	<div class="cart-continue-area">
		<div class="row-fluid">
			<div class="span6">
				<a href="<?php echo $this->continue_link ?>" class="cart-continue-shopping-button btn btn-large" title="<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>">
					<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>
				</a>	
			</div>	
			<div class="span6">
				<?php echo $this->confirmButton ?>
			</div>
		</div>
	</div>	
</form>
<?php elseif($this->user->guest) : ?>
<!--Display Login and Registration Options-->
<div class="row-fluid checkout-userbox">
	<div class="span6">
		<div class="inner">
			<?php if($this->params->get('guest_checkout', 1)) : ?>
			<h3><?php echo JText::_('COM_QAZAP_CART_REGISTER_OR_GUEST_CHECKOUT') ?></h3>
			<div class="checkout-userbox-contents">					
				<h4 class="checkout-userbox-subtitle"><?php echo JText::_('COM_QAZAP_CART_REGISTRATION_REQUEST') ?></h4>			
				<label class="checkout-userbox-switch radio">
					<input type="radio" name="qzcheckout_method" value="qzguest" <?php echo ($this->checkoutMethod == 'guest') ? 'checked' : ''; ?>/> 
					<?php echo JText::_('COM_QAZAP_CART_CHECKOUT_AS_GUEST') ?>						
				</label>						
				<div class="checkout-userbox-guest-form<?php echo ($this->checkoutMethod == 'guest') ? '' : ' hide'; ?>" id="qzguest">
					<div class="switch-inner row-fluid">
						<div class="span12">
							<form id="qazap-checkout-form" method="post" action="<?php echo JRoute::_(QazapHelperRoute::getCartRoute()) ?>">
								<?php  
								$this->confirmButtonClass = 'btn btn-primary pull-right'; 
								$this->_prepareButtonVars();	
								?>
								<?php echo $this->confirmButton ?>
							</form>
						</div>
					</div>
				</div>						
				<label class="checkout-userbox-switch radio">
					<input type="radio" name="qzcheckout_method" value="qzregister" <?php echo ($this->checkoutMethod == 'register') ? 'checked' : ''; ?>/>
					<?php echo JText::_('COM_QAZAP_CART_REGISTER') ?>						
				</label>
				<div class="checkout-userbox-reg-form<?php echo ($this->checkoutMethod == 'register') ? '' : ' hide'; ?>" id="qzregister">
					<div class="switch-inner">
						<?php echo $this->loadTemplate('registration'); ?>
					</div>
				</div>				
				<div id="reg-advantages"<?php echo ($this->checkoutMethod == 'guest') ? '' : ' class="hide"'; ?>>
					<?php echo JText::_('COM_QAZAP_CART_REGISTRATION_MSG') ?>					
				</div>
			</div>				
			<?php else : ?>
			<h3><?php echo JText::_('COM_QAZAP_CART_REGISTER_AND_CHECKOUT') ?></h3>
			<div class="checkout-userbox-contents">
				<?php echo $this->loadTemplate('registration'); ?>
			</div>
			<?php endif; ?>
		</div>	
	</div>
	<div class="span6">
		<div class="inner">
			<h3><?php echo JText::_('COM_QAZAP_CART_LOGIN') ?></h3>
			<div class="checkout-userbox-contents">
				<h4><?php echo JText::_('COM_QAZAP_CART_LOGIN_MSG') ?>:</h4>
				<?php echo QZLogin::getForm('cart', array('hide_registration' => 1)); ?>
			</div>
		</div>		
	</div>
</div>
<div class="cart-continue-area">
	<div class="row-fluid">
		<div class="span12">
			<a href="<?php echo $this->continue_link ?>" class="cart-continue-shopping-button btn btn-large" title="<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>">
				<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>
			</a>	
		</div>	
	</div>
</div>
<?php else : ?>
<form id="qazap-checkout-form" method="post" action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart') ?>">	
	<div class="cart-continue-area">
		<div class="row-fluid">
			<div class="span6">
				<a href="<?php echo $this->continue_link ?>" class="cart-continue-shopping-button btn btn-large" title="<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>">
					<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>
				</a>	
			</div>	
			<div class="span6">
				<?php echo $this->confirmButton ?>
			</div>
		</div>
	</div>	
</form>
<?php endif; ?>