<?php
/**
 * addtocart.php
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

JHtml::_('formbehavior.chosen', 'select');

$product					= $displayData['product'];
$params						= $displayData['params'];	
$buy_action				= $displayData['buy_action'];
$url							= $displayData['url'];
$user							= JFactory::getUser();
$now							= JFactory::getDate();
$qty_step					= (int) $params->get('purchase_quantity_steps', 1);
$min_qty					= (int) $params->get('minimum_purchase_quantity', 1);
$max_qty					= (int) $params->get('maximum_purchase_quantity', 100);
$product_id				= $product->product_id;
$available_from		= $params->get('available_from', null);
$available_from		= !empty($available_from) ? JFactory::getDate($available_from) : null;
$available_end		= $params->get('available_end', null);
$available_end		= !empty($available_end) ? JFactory::getDate($available_end) : null;
$remaining_time		= null;
$unique_id 				= $product->product_id . '-' . QZHelper::unique_id(); 
// Force hide quantity selector for product listing pages 
// as we do not have enough space to show it.
// You can change this in your template override if need.
$params->set('show_quantity', 0);

if(!empty($available_end))
{
	$remaining_time = $now->diff($available_end);
}

if($params->get('show_quantity', 1)) 
{
	// This is the JavaScript for Quantity selector
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("
	// Method to update product quantity field
	function updateQZQuantity{$product_id}(action) {
		var qty_field = jQ('#qazap-qty-{$product_id}');
		var value = parseInt(qty_field.val());
		var newValue = value;
		
		if(action == 'plus') {
			newValue = value + $qty_step;
		} else {
			newValue = value - $qty_step;
		}
		
		if(newValue > $max_qty) {
			alert();
		} else if(newValue < $min_qty) {
			alert();
		} else {
			qty_field.val(newValue).trigger('change');
		}
			
		return false;
	}
	// Method to validate product quantity	
	function validateQZQuantity{$product_id}() {		
		var qty_field = jQ('#qazap-qty-{$product_id}');
		var value = parseInt(qty_field.val());
		var oldValue = parseInt(qty_field.data('lastvalue'));
		var devision = ((value - oldValue) / $qty_step);
		
		if(value != $min_qty && (Math.floor(devision) != devision || !jQ.isNumeric(devision))) {
			alert();
			qty_field.val(oldValue);		
		}	else if(value > $max_qty) {
			alert();
			qty_field.val(oldValue);		
		} else if(value < $min_qty) {
			alert();
			qty_field.val(oldValue);		
		} else {
			qty_field.attr('data-lastvalue', value);
		}		
	}
	// End of product quantity scripts
	");	
}

?>
<form class="form-validate form-vertical qazap-addtocart-form-list" action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart')?>" method="post">

	<?php if($params->get('catalogue_only', 0) != true && $product->prices->product_salesprice) : ?>
		<?php if(!empty($available_end) && ($now > $available_end)) : ?>
			<div class="qazap-addtocart-container">
				<button type="button" disabled="disabled" class="btn btn-disabled hasTooltip" title="<?php echo JText::_('COM_QAZAP_PRODUCT_ORDER_UNAVAILABLE') ?>">
					<?php echo JText::_('COM_QAZAP_PRODUCT_ORDER_UNAVAILABLE') ?>					
				</button>
			</div>
		<?php elseif(!empty($available_from) && ($available_from > $now)) : ?>
			<div class="qazap-addtocart-container">
				<button type="button" disabled="disabled" class="btn btn-disabled hasTooltip" title="<?php echo JText::_('COM_QAZAP_PRODUCT_ORDER_UNAVAILABLE') ?>">
					<?php echo JText::_('COM_QAZAP_PRODUCT_ORDER_UNAVAILABLE') ?>					
				</button>
			</div>
		<?php else : ?>	
			<div class="qazap-addtocart-container">
				<?php if($buy_action == 'addtocart') : ?>
				<!-- Show Add To Cart Button -->
				<?php if($params->get('show_quantity', 1)) : ?>
				<div class="qazap-quantity-input pull-left">
					<input type="text" name="qzform[quantity]" id="qazap-qty-<?php echo $product->product_id ?>" class="qazap-qty-inputbox" value="<?php echo $min_qty ?>" data-lastvalue="<?php echo $min_qty ?>" onchange="validateQZQuantity<?php echo $product->product_id ?>();" />
					<div class="qazap-qty-button-wrap">
					    <button type="button" class="qazap-qty-control-button plus" onclick="updateQZQuantity<?php echo $product->product_id ?>('plus');">
					        <i class="icon-chevron-up"></i>
					    </button>
					    <button type="button" class="qazap-qty-control-button minus" onclick="updateQZQuantity<?php echo $product->product_id ?>('minus');">
					        <i class="icon-chevron-down"></i>
					    </button>
					</div>
				</div>
				<?php else : ?>
					<input type="hidden" name="qzform[quantity]" id="qazap-qty-<?php echo $product->product_id ?>" class="qazap-qty-inputbox" value="<?php echo $min_qty ?>" data-lastvalue="<?php echo $min_qty ?>" onchange="validateQZQuantity<?php echo $product->product_id ?>();" />
				<?php endif; ?>
				<button type="submit" class="qazap-add-to-cart-button validate list-to-cart-button btn btn-success hasTooltip" title="<?php echo JText::_('COM_QAZAP_ADD_TO_CART') ?>">
					<?php echo JText::_('COM_QAZAP_ADD_TO_CART') ?>
				</button>		
				<?php elseif($buy_action == 'notify') : ?>
				
				<!-- Show Notify Button -->
				<a class="qazap-notify-button list-to-notify btn btn-info fancybox-popup" href="#qazap-notify-popup-<?php echo $unique_id ?>">
					<?php echo JText::_('COM_QAZAP_NOTIFY_ME') ?>
				</a>		
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_qazap"/>
	<input type="hidden" name="task" value="cart.add" />
	<input type="hidden" name="return" value="<?php echo base64_encode($url) ?> "/>
	<input type="hidden" name="product_name" value="<?php echo base64_encode($product->product_name) ?> "/>
	<input type="hidden" name="qzform[product_id]" value="<?php echo $product->product_id ?>" />
	<input type="hidden" name="qzform[fromlist]" value="1" />
</form>

<?php if($buy_action == 'notify') : ?>
<div class="qazap-hidden-items">
	<div id="qazap-notify-popup-<?php echo $unique_id ?>">
		<div class="qazap-popup">
			<div class="qazap-popup-title">
				<h3><?php echo JText::_('COM_QAZAP_NOTIFY_ME') ?></h3>
			</div>	
			<form name="qazap-notify-form" class="form-inline" action="<?php echo JRoute::_($url)?>" method="post">
				<div class="qazap-popup-content">
					<div class="input-prepend">
						<span class="add-on">@</span>
				  	<input type="email" name="qzform[user_email]" id="qzform_user_email" value="<?php echo $user->email?>" placeholder="<?php echo JText::_('JGLOBAL_EMAIL') ?>" required="true" />
				  </div>
			  </div>
				<div class="qazap-popup-footer">
					<button type="button" class="qazap-popup-close btn"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?></button>	
					<button type="submit" class="btn btn-primary"><?php echo JText::_('JSUBMIT') ?></button>					
			  </div> 	  
				<input type="hidden" name="option" value="com_qazap"/>
				<input type="hidden" name="task" value="product.notify" />
				<input type="hidden" name="return" value="<?php echo base64_encode($url) ?> "/>
				<input type="hidden" name="qzform[product_id]" value="<?php echo $product->product_id ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>	
</div>
<?php endif; ?>