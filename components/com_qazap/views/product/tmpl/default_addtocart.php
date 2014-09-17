<?php
/**
 * default_addtocart.php
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

$params						= $this->item->params;
$qty_step					= (int) $params->get('purchase_quantity_steps', 1);
$min_qty					= (int) $params->get('minimum_purchase_quantity', 1);
$max_qty					= (int) $params->get('maximum_purchase_quantity', 100);
$now							= JFactory::getDate();
$available_from		= $params->get('available_from', null);
$available_from		= !empty($available_from) ? JFactory::getDate($available_from) : null;
$available_end		= $params->get('available_end', null);
$available_end		= !empty($available_end) ? JFactory::getDate($available_end) : null;
$remaining_time		= null;
if(!empty($available_end))
{
	$remaining_time = $now->diff($available_end);
}

$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
// Method to update product quantity field
function updateQZQuantity(action) {
	var qty_field = jQ('#qazap-qty');
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
function validateQZQuantity() {		
	var qty_field = jQ('#qazap-qty');
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
?>
<div class="alert alert-info hide qazap-product-info-<?php echo $this->item->product_id ?>">
  <button type="button" class="close">&times;</button>
  <span class="info-msg"></span>
</div>
<form class="form-validate form-vertical qazap-addtocart-form" action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart')?>" method="post">

	<?php if(!empty($this->item->membership)) : ?>
		<div class="qazap-membership-group control-group">
			<div class="qazap-membership-title">
        <?php if(count($this->item->membership->data) > 1 || !$params->get('membership_hidden', 0)) : 
        $class = $params->get('membership_required', 1) ? 'class="qazap-membership-label control-label required"' : 'class="qazap-membership-label control-label"';
        ?>
				<label for="<?php echo $this->item->membership->field_id ?>" id="qazap-membership-lbl" <?php echo $class ?>>
          <?php $title = $this->escape('<strong>' . JText::_('COM_QAZAP_MEMBERSHIP') . ':</strong><br/>' . JText::_('QAZAP_SELECT_MEMBERSHIP_PLAN')); ?>
					<span class="hasTooltip" title="<?php echo $title ?>">
						<?php echo JText::_('COM_QAZAP_MEMBERSHIP') ?>:
            <?php if($params->get('membership_required', 1)) : ?>
            <span class="star">&nbsp;*</span>
            <?php endif; ?>
            &nbsp;						
					</span>
				</label>
        <?php endif; ?>		
			</div>
			<div class="qazap-membership-field controls">
				<?php echo $this->item->membership->display; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if(!empty($this->item->attributes)) : ?>
		<?php foreach($this->item->attributes as $attributes) : ?>
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
                  <?php if($params->get('attribute_required', 1)) : ?>
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
	
	<?php if(!$params->get('catalogue_only', 0) && $this->item->prices->product_salesprice) : ?>
		<?php if(!empty($available_end) && ($now > $available_end)) : ?>
			<?php if($params->get('availability_day_info', 1)) : ?>
				<div class="alert alert-top-margin">
					<?php echo JText::_('COM_QAZAP_PRODUCT_ORDER_ENDED') ?> 
				</div>
			<?php endif; ?>
		<?php elseif(!empty($available_from) && ($available_from > $now)) : ?>
			<?php if($params->get('availability_day_info', 1)) : ?>
				<div class="alert alert-info alert-top-margin">
					<?php echo JText::sprintf('COM_QAZAP_PRODUCT_ORDER_STARTS_FROM', JHtml::_('date', $available_from, JText::_('DATE_FORMAT_LC3'))); ?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="qazap-addtocart-container">
				<?php if($this->item->buy_action == 'addtocart') : ?>
					<!-- Show Add To Cart Button -->	
					<?php if($params->get('show_quantity', 1)) : ?>
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
					<?php else : ?>
						<input type="hidden" name="qzform[quantity]" id="qazap-qty" class="qazap-qty-inputbox" value="<?php echo $min_qty ?>" data-lastvalue="<?php echo $min_qty ?>" onchange="validateQZQuantity();" />
					<?php endif; ?>
					<button type="submit" class="qazap-add-to-cart-button validate">
						<span><span><i class="icon-cart"></i>&nbsp;&nbsp;<?php echo JText::_('COM_QAZAP_ADD_TO_CART') ?></span</span>
					</button>
					<?php if(!empty($remaining_time)) : ?>
					<div class="alert alert-info alert-top-margin">
						<?php echo JText::sprintf('COM_QAZAP_PRODUCT_ORDER_CLOSES_IN', JHtml::_('qzdate.diff', $remaining_time, $hideSeconds = true)); ?>
					</div>
					<?php endif; ?>
				<?php elseif($this->item->buy_action == 'notify') : ?>
					<!-- Show Notify Button -->
					<a class="qazap-notify-button fancybox-popup" href="#qazap-notify-popup">
						<span><span><i class="icon-notification"></i>&nbsp;&nbsp;<?php echo JText::_('COM_QAZAP_NOTIFY_ME') ?></span</span>
					</a>		
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_qazap"/>
	<input type="hidden" name="task" value="cart.add" />
	<input type="hidden" name="return" value="<?php echo base64_encode($this->product_url) ?> "/>
	<input type="hidden" name="product_name" value="<?php echo base64_encode($this->item->product_name) ?> "/>
	<input type="hidden" name="qzform[product_id]" value="<?php echo $this->item->product_id ?>" />
</form>

<?php if($this->item->buy_action == 'notify') : ?>
<div class="qazap-hidden-items">
	<div id="qazap-notify-popup">
		<?php echo $this->loadTemplate('notify'); ?>
	</div>	
</div>
<?php endif; ?>