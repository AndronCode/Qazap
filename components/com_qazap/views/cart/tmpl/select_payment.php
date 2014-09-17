<?php
/**
 * select_payment.php
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

QZApp::loadJS();
QZApp::loadCSS();
?>
<div class="payment-method-page">
	<div class="page-header">
		<h1><?php echo JText::_('COM_QAZAP_CART_SELECT_PAYMENT_METHOD') ?></h1>
	</div>
	<?php if(empty($this->paymentMethods)) : ?>
		<div class="alert alert-error alert-empty">
			<p><?php echo JText::_('COM_QAZAP_MSG_NO_PAYMENT_METHOD_FOUND') ?></p>
		</div>
	<?php else : ?>
		<form id="qazap-payment-form" class="form-horizontal" action="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'select_payment'))) ?>" method="POST" name="qazap_payment_form">	
			<?php foreach($this->paymentMethods as $method) : ?>
				<?php echo $method->html ?>
			<?php endforeach; ?>
			<div class="cart-continue-area">
				<div class="row-fluid">
					<div class="span6">
						<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute()) ?>" class="cart-continue-button btn btn-large">
							<?php echo JText::_('COM_QAZAP_CART_GO_BACK') ?>
						</a>	
					</div>	
					<div class="span6">
						<?php echo $this->confirmButton ?>
					</div>
				</div>
			</div>
		</form>
	<?php endif; ?>
</div>