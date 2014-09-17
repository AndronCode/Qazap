<?php
/**
 * selectattributes.php
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
?>
<?php if(!empty($this->errors)) : ?>
<div class="qazap-ajax-popup-wrap">
	<div class="qazap-ajax-message"><span><?php echo JText::_($this->errors); ?></span></div>
	<div class="buttons-wrap row-fluid">
		<a class="btn pull-left" href="<?php echo $this->continue_url ?>" title="<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>">
			<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>
		</a>
	</div>
</div>
<?php else : 
$qty_step = (int) $this->product->params->get('purchase_quantity_steps', 1);
$min_qty = (int) $this->product->params->get('minimum_purchase_quantity', 1);
$max_qty = (int) $this->product->params->get('maximum_purchase_quantity', 100);
?>
<script type="text/javascript">
// Method to update product quantity field
function updateQZQuantity(action) {
	var qty_field = jQ('#qazap-qty');
	var value = parseInt(qty_field.val());
	var newValue = value;
	
	if(action == 'plus') {
		newValue = value + <?php echo $qty_step ?>;
	} else {
		newValue = value - <?php echo $qty_step ?>;
	}
	
	if(newValue > <?php echo $max_qty ?>) {
		alert();
	} else if(newValue < <?php echo $min_qty ?>) {
		alert();
	} else {
		qty_field.val(newValue).trigger('change');
	}
		
	return false;
}
// Method to validate product quantity	
function validateQZQuantity() {		
	var qty_field = jQ('#qazap-qty');
	var value = parseInt(qty_field.val());
	var oldValue = parseInt(qty_field.data('lastvalue'));
	var devision = ((value - oldValue) / <?php echo $qty_step ?>);
	
	if(value != <?php echo $min_qty ?> && (Math.floor(devision) != devision || !jQ.isNumeric(devision))) {
		alert();
		qty_field.val(oldValue);		
	}	else if(value > <?php echo $max_qty ?>) {
		alert();
		qty_field.val(oldValue);		
	} else if(value < <?php echo $min_qty ?>) {
		alert();
		qty_field.val(oldValue);		
	} else {
		qty_field.attr('data-lastvalue', value);
	}		
}
// End of product quantity scripts	
</script>
<div class="qazap-select-attr-popup">
	<div class="qazap-popup">
		<div class="qazap-popup-title">
			<h3><?php echo $this->escape($this->product->product_name) ?></h3>
		</div>	
		<form class="form-validate form-vertical qazap-addtocart-popup-form" action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart')?>" method="post">
			<div class="qazap-popup-content">

				<div class="alert alert-info hide qazap-product-info-<?php echo $this->product->product_id ?>">
				  <button type="button" class="close">&times;</button>
				  <span class="info-msg"></span>
				</div>

				<?php if(!empty($this->product->membership)) : ?>
					<div class="qazap-membership-group control-group">
						<div class="qazap-membership-title">
			        <?php if(count($this->product->membership->data) > 1 || !$this->product->params->get('membership_hidden', 0)) : 
			        $class = $this->product->params->get('membership_required', 1) ? 'class="qazap-membership-label control-label required"' : 'class="qazap-membership-label control-label"';
			        ?>
							<label for="<?php echo $this->product->membership->field_id ?>" id="qazap-membership-lbl" <?php echo $class ?>>
			          <?php $title = $this->escape('<strong>' . JText::_('COM_QAZAP_MEMBERSHIP') . ':</strong><br/>' . JText::_('QAZAP_SELECT_MEMBERSHIP_PLAN')); ?>
								<span class="hasTooltip" title="<?php echo $title ?>">
									<?php echo JText::_('COM_QAZAP_MEMBERSHIP') ?>:
			            <?php if($this->product->params->get('membership_required', 1)) : ?>
			            <span class="star">&nbsp;*</span>
			            <?php endif; ?>
			            &nbsp;						
								</span>
							</label>
			        <?php endif; ?>
						</div>
						<div class="qazap-membership-field controls">
							<?php echo $this->product->membership->display; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if(!empty($this->product->attributes)) : ?>
					<?php foreach($this->product->attributes as $attributes) : ?>
						<?php if(!$attributes->hidden) :?>
							<div class="qazap-attribute-group attribute-type-<?php echo $attributes->plugin ?> control-group">
								<?php if($attributes->show_title) : ?>
									<div class="qazap-attribute-title">
										<?php
										$class = '';
										$title = '';
										if($attributes->tooltip)
										{
											$title = $this->escape('<strong>' . $attributes->title . ':</strong><br/>' . $attributes->tooltip);
										}?>
										<label for="<?php echo $attributes->field_id ?>" id="<?php echo $attributes->field_id ?>-lbl" class="qazap-attribute-label control-label">
											<span class="hasTooltip" title="<?php echo $class.$title ?>">
			                  <?php echo $attributes->title ?>:
			                  <?php if($this->product->params->get('attribute_required', 1)) : ?>
			                  <span class="star">&nbsp;*</span>
			                  <?php endif; ?>              
			                  &nbsp;
			                </span>
										</label>
									</div>
								<?php endif; ?>
								<div class="qazap-attribute-field controls">
									<?php echo $attributes->display ?>
								</div>
								<?php if(!empty($attributes->description)) : ?>
									<div class="qazap-attribute-description">
										<?php echo $this->escape($attributes->description) ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if($this->product->params->get('catalogue_only', 0) != true && $this->product->prices->product_salesprice) : ?>
				<div class="qazap-addtocart-container">
					<?php if($this->product->buy_action == 'addtocart') : ?>
					<!-- Show Add To Cart Button -->	
					<div class="qazap-quantity-input pull-left">
						<input type="text" name="qzform[quantity]" id="qazap-qty" class="qazap-qty-inputbox" value="<?php echo $min_qty ?>" data-lastvalue="<?php echo $min_qty ?>" onchange="validateQZQuantity();" />
						<div class="qazap-qty-button-wrap">
						    <button type="button" class="qazap-qty-control-button plus" onclick="updateQZQuantity('plus');">
						        <i class="icon-chevron-up"></i>
						    </button>
						    <button type="button" class="qazap-qty-control-button minus" onclick="updateQZQuantity('minus');">
						        <i class="icon-chevron-down"></i>
						    </button>
						</div>
					</div>
					<button type="submit" class="qazap-add-to-cart-button validate">
						<span><span><i class="icon-cart"></i>&nbsp;&nbsp;<?php echo JText::_('COM_QAZAP_ADD_TO_CART') ?></span</span>
					</button>		
					<?php elseif($this->product->buy_action == 'notify') : ?>
					<!-- Show Notify Button -->
					<a class="qazap-notify-button fancybox-popup" href="#qazap-notify-popup">
						<span><span><i class="icon-notification"></i>&nbsp;&nbsp;<?php echo JText::_('COM_QAZAP_NOTIFY_ME') ?></span</span>
					</a>		
					<?php endif; ?>
				</div>
				<?php endif; ?>
		  </div>  
			<input type="hidden" name="option" value="com_qazap"/>
			<input type="hidden" name="task" value="cart.add" />
			<input type="hidden" name="return" value="<?php echo base64_encode(QazapHelperRoute::getProductRoute($this->product->product_id, $this->product->category_id)) ?> "/>
			<input type="hidden" name="product_name" value="<?php echo base64_encode($this->product->product_name) ?> "/>
			<input type="hidden" name="qzform[product_id]" value="<?php echo $this->product->product_id ?>" />
		</form>
	</div>
</div>	

<?php endif; ?>