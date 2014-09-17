<?php
/**
 * edit.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
// Order Group Scripts
if(typeof jq === 'undefined') {
	var jq = jQuery.noConflict();
}

");

?>
<script type="text/javascript">  
    Joomla.submitbutton = function(task)
    {
        if(task == 'order.cancel'){
            Joomla.submitform(task, document.getElementById('order-form'));
        }
        else{            
            if (task != 'order.cancel' && document.formvalidator.isValid(document.id('order-form'))) {
                
                Joomla.submitform(task, document.getElementById('order-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>
<div id="qazap-ordergroup">
	<?php echo JHtml::_('bootstrap.startTabSet', 'ordergroupTab', array('active' => 'general')); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'ordergroupTab', 'general', JText::_('COM_QAZAP_PRODUCT_FIELDSET_GENERAL', true)); ?>
	<div class="ordergroup-header">
		<div class="row-fluid">
			<div class="span6">
				<div class="header-inner form-horizontal text-form">
					<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDERGROUP_SUMMERY') ?></div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('ordergroup_id') ?></div>
						<div class="controls"><?php echo $this->ordergroup->ordergroup_id ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('ordergroup_number') ?></div>
						<div class="controls"><?php echo $this->ordergroup->ordergroup_number ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created_on') ?></div>
						<div class="controls"><?php echo QazapHelper::displayDate($this->ordergroup->created_on) ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('order_count') ?></div>
						<div class="controls"><?php echo count($this->ordergroup->vendor_carts) ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('user_id') ?></div>
						<div class="controls"><?php echo $this->username ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('total_in_order_currency') ?></div>
						<div class="controls"><?php echo QazapHelper::orderCurrencyDisplay($this->ordergroup->cart_total, $this->ordergroup->order_currency) ?></div>
					</div>
					<?php if($this->ordergroup->order_currency != $this->ordergroup->user_currency)	: ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('total_in_user_currency') ?></div>
						<div class="controls"><?php echo QazapHelper::orderCurrencyDisplay($this->ordergroup->cart_total, $this->ordergroup->order_currency, $this->ordergroup->user_currency,  $this->ordergroup->currency_exchange_rate) ?></div>
					</div>					
					<?php endif; ?>
					<div class="control-group">
						<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDERGROUP_COMMISSION') ?></div>
						<div class="controls"><?php echo QazapHelper::orderCurrencyDisplay($this->ordergroup->getCommission(), $this->ordergroup->order_currency); ?>
						</div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('order_status') ?></div>
						<div class="controls">
							<?php echo QazapHelper::orderStatusNameByCode($this->ordergroup->order_status) ?>
							<span>&nbsp;<a href="#update-group-status" class="btn btn-small btn-success pull-right fancybox-popup"><i class="qzicon-pencil2"></i>&nbsp;<?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_EDIT')?></a></span>
						</div>
					</div>
					<?php if(!$this->params->get('downloadable') && !$this->params->get('intangible')) : ?>	
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('cart_shipment_method_name') ?></div>
						<div class="controls"><?php echo QZDisplay::getShipmentMethodNameByID($this->ordergroup->cart_shipment_method_id) ?></div>
					</div>
					<?php endif; ?>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('cart_payment_method_name') ?></div>
						<div class="controls"><?php echo QZDisplay::getPaymentMethodNameByID($this->ordergroup->cart_payment_method_id) ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access_key') ?></div>
						<div class="controls"><?php echo $this->ordergroup->access_key ?></div>
					</div>	
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('ip_address') ?></div>
						<div class="controls"><?php echo $this->ordergroup->ip_address ?></div>
					</div>											
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('customer_note') ?></div>
						<div class="controls"><?php echo $this->form->getInput('customer_note') ?></div>
					</div>																															
				</div>
				<div class="header-inner form-horizontal condensed">
					<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDERGROUP_BILLING_ADDRESS') ?></div>
					<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $this->ordergroup->ordergroup_id); ?>" method="post" name="adminForm" id="bt-address-form" class="form-validate">
					<?php if(count($this->ordergroup->billing_address)) : ?>
						<div class="top-save-area">
							<button type="submit" class="btn btn-small btn-success validate"><span class="icon-apply icon-white"></span> <?php echo JText::_('JAPPLY') ?></button>
							<button type="reset" class="btn btn-small qazap-reset-button"><?php echo JText::_('COM_QAZAP_RESET') ?></button>
						</div>					
						<?php 
						$fieldSets = $this->form->getFieldsets('billing_address');
						foreach ($fieldSets as $name => $fieldSet) :
							foreach($this->form->getFieldset($name) as $fieldName => $field) : ?>
								<?php if ($field->hidden): ?>
									<?php echo $field->input;?>
								<?php else:?>					
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
								<?php endif; ?>									
							<?php endforeach; ?>
						<?php endforeach; ?>
						<input type="hidden" name="address_type" value="billing_address" />
						<input type="hidden" name="option" value="com_qazap"/>
						<input type="hidden" name="task" value="order.updateOrderAddress" />
						<?php echo JHtml::_('form.token'); ?>					
					<?php endif; ?>	
					</form>																									
				</div>												
			</div>
			<div class="span6">
				<div class="header-inner">
					<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDERGROUP_HISTORY') ?></div>
					<div class="history-table-container">
						<table class="table table-history table-hover table-condensed">
							<thead>
								<tr>
									<th class="small">
										<?php echo JText::_('COM_QAZAP_ORDERGROUP_HISTORY_STATUS') ?>
									</th>
									<th class="small" width="40%">
										<?php echo JText::_('COM_QAZAP_ORDERGROUP_HISTORY_COMMENT') ?>
									</th>
									<th class="center small">
										<?php echo JText::_('COM_QAZAP_ORDERGROUP_HISTORY_BUYER_NOTIFIED') ?>
									</th>
									<th class="center small">
										<?php echo JText::_('COM_QAZAP_ORDERGROUP_HISTORY_SELLER_NOTIFIED') ?>
									</th>									
									<th class="small">
										<?php echo JText::_('COM_QAZAP_ORDERGROUP_HISTORY_EDITOR') ?>
									</th>
									<th class="small">
										<?php echo JText::_('COM_QAZAP_ORDERGROUP_HISTORY_DATE') ?>
									</th>																																
								</tr>
							</thead>
							<tbody>
								<?php if($this->history) : ?>
									<?php foreach($this->history as $history) :	?>
									<tr>
										<td class="small"><?php echo $history->status_name ?></td>
										<td class="small"><?php echo $history->comments ?></td>
										<td class="center small"><?php echo ($history->mail_to_buyer ? '<i class="icon-checkmark-circle green hasTooltip" title="'.JText::_('JYES').'"></i>' : '<i class="icon-cancel-circle red hasTooltip" title="'.JText::_('JNO').'"></i>') ?></td>
										<td class="center small"><?php echo ($history->mail_to_vendor ? '<i class="icon-checkmark-circle green hasTooltip" title="'.JText::_('JYES').'"></i>' : '<i class="icon-cancel-circle red hasTooltip" title="'.JText::_('JNO').'"></i>') ?></td>
										<td class="small nowrap"><?php echo $history->editor ?></td>
										<td class="small nowrap"><?php echo QazapHelper::displayDate($history->created_on) ?></td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>				
				<div class="payment-details-container">
					<div class="header-inner form-horizontal condensed">
						<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_STATUS') ?></div>
						<?php
						$this->form->setValue('cart_total', null, QazapHelper::currencyFormat($this->ordergroup->cart_total));
						$this->form->setValue('payment_received', null, QazapHelper::currencyFormat($this->ordergroup->payment_received));
						$this->form->setValue('payment_refunded', null, QazapHelper::currencyFormat($this->ordergroup->payment_refunded));
						$this->form->setValue('payment_balance', null, QazapHelper::currencyFormat(($this->ordergroup->cart_total + $this->ordergroup->payment_refunded - $this->ordergroup->payment_received)));
						?>		
						<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $this->ordergroup->ordergroup_id); ?>" method="post" name="adminForm" id="payment-details-form">
							<div class="top-save-area">
								<button type="submit" class="btn btn-small btn-success"><span class="icon-apply icon-white"></span> <?php echo JText::_('JAPPLY') ?></button>
								<button type="reset" class="btn btn-small qazap-reset-button"><?php echo JText::_('COM_QAZAP_RESET') ?></button>
							</div>								
							<div class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('cart_total') ?></div>
									<div class="controls"><?php echo $this->form->getInput('cart_total') ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('payment_received') ?></div>
									<div class="controls"><?php echo $this->form->getInput('payment_received') ?></div>
								</div>	
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('payment_refunded') ?></div>
									<div class="controls"><?php echo $this->form->getInput('payment_refunded') ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('payment_balance') ?></div>
									<div class="controls"><?php echo $this->form->getInput('payment_balance') ?></div>
								</div>																																		
							</div>
							<input type="hidden" name="option" value="com_qazap"/>
							<input type="hidden" name="task" value="order.updatepayments" />
							<?php echo JHtml::_('form.token'); ?>															
						</form>
					</div>
					<?php if(!empty($this->paymentInfo)) : ?>
					<div class="onAdminOrderDisplay">
						<div class="header-inner form-horizontal text-form">
							<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_INFORMATION') ?></div>
							<div class="plugin-html">
								<?php echo $this->paymentInfo ?>
							</div>							
						</div>
					</div>
					<?php endif; ?>
				</div>	
				<?php if(!$this->params->get('downloadable') && !$this->params->get('intangible') && count($this->ordergroup->shipping_address)) : ?>
				<div class="header-inner form-horizontal condensed">
					<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDERGROUP_SHIPPING_ADDRESS') ?></div>									
					<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $this->ordergroup->ordergroup_id); ?>" method="post" name="adminForm" id="st-address-form" class="form-validate">						
						<div class="top-save-area">
							<button type="submit" class="btn btn-small btn-success validate"><span class="icon-apply icon-white"></span> <?php echo JText::_('JAPPLY') ?></button>
							<button type="reset" class="btn btn-small qazap-reset-button"><?php echo JText::_('COM_QAZAP_RESET') ?></button>
						</div>						
						<?php 
						$fieldSets = $this->form->getFieldsets('shipping_address');
						foreach ($fieldSets as $name => $fieldSet) :
							foreach($this->form->getFieldset($name) as $field) : ?>
								<?php if ($field->hidden): ?>
									<?php echo $field->input;?>
								<?php else:?>					
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
								<?php endif; ?>									
							<?php endforeach; ?>
						<?php endforeach; ?>
						<input type="hidden" name="address_type" value="shipping_address" />
						<input type="hidden" name="option" value="com_qazap"/>
						<input type="hidden" name="task" value="order.updateOrderAddress" />
						<?php echo JHtml::_('form.token'); ?>							
					</form>			
				</div>
				<?php endif; ?>											
			</div>			
		</div>	
	</div>
	
	<!-- Pop up forms -->
	<div class="hide">
		<div id="update-group-status">
		<?php echo $this->loadTemplate('ordergroupstatus'); ?>
		</div>
	</div>		
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	
	<?php echo JHtml::_('bootstrap.addTab', 'ordergroupTab', 'orders', JText::_('COM_QAZAP_ORDERGROUP_ORDERS', true)); ?>
		<?php echo $this->loadTemplate('orders'); ?>	
	<?php echo JHtml::_('bootstrap.endTab'); ?>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $this->ordergroup->ordergroup_id); ?>" method="post" name="adminForm" id="order-form">
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>	
</form>