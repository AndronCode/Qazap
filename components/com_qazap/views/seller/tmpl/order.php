<?php
/**
 * order.php
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
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.caption');
JHtml::_('behavior.framework');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
$skip_address_fields = array('id', 'order_address_id', 'ordergroup_id', 'address_type', 'address_name', 'country', 'states_territory');
$order = $this->orderDetails->vendor_carts[$this->state->get('vendor.id')];
?>
<div class="profile-<?php echo $this->layout . $this->pageclass_sfx ?>">
	<?php echo $this->menu ?>
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<header class="qz-page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
			<?php if($this->isVendor) : ?>
				<?php if(!$this->activeVendor) : ?>
					<span class="label label-important toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_UNAPPROVED')?></span>
				<?php else : ?>
					<span class="label label-success toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_APPROVED')?></span>
				<?php endif; ?>
			<?php endif; ?>		
		</h1>
	</header>
	<?php endif; ?>

	<!--<h2><?php echo JText::_('COM_QAZAP_ORDER_NUMBER') . ': ' . $order->order_number ?></h2>-->
	<div><?php echo '<strong>' . JText::_('COM_QAZAP_ORDERS_ORDER_STATUS') . ':</strong> ' . QZHelper::orderStatusNameByCode($order->order_status) ?></div>
	<div><?php echo '<strong>' . JText::_('COM_QAZAP_FORM_LBL_ORDER_CREATED_ON') . ':</strong> ' . QZHelper::displayDate($order->created_on) ?></div>
	<div><?php echo '<strong>' . JText::_('COM_QAZAP_FORM_LBL_ORDER_MODIFIED_ON') . ':</strong> ' . QZHelper::displayDate($order->modified_on) ?></div>

	<div class="row-fluid">
		<div class="span12 margin-bottom-15">
			<a href="#change-order-status-popup" class="btn btn-success fancybox-popup pull-right">
				<i class="qzicon-pencil2"></i>&nbsp;<?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_EDIT')?>
			</a>	
		</div>
	</div>
	<div class="hide">
		<div id="change-order-status-popup">
			<div class="qazap-popup">
				<div class="qazap-popup-title">
					<h3><?php echo JText::_('COM_QAZAP_ORDER_STATUS_EDIT_TITLE') ?></h3>
				</div>
				<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=seller&layout=order&ordergroup_id=' . (int) $this->orderDetails->ordergroup_id); ?>" method="post" name="adminForm" class="form-validate form-vertical">
				<div class="qazap-popup-content">
					<div class="control-group">
						<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_STATUS') ?></div>
						<div class="controls"><?php echo JHtml::_('qzstatus.orderstatus', $order->order_status) ?></div>
					</div>	
					<div class="control-group">
						<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDERGROUP_APPLY_TO_ALL_ITEMS_LABEL')?></div>
						<div class="controls"><?php echo JHtml::_('qzstatus.applytoall') ?></div>
					</div>	
					<div class="control-group">
						<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_COMMENTS') ?></div>
						<div class="controls"><?php echo JHtml::_('qzstatus.comment') ?></div>
					</div>
				</div>
				<div class="qazap-popup-footer">
					<button type="button" class="qazap-popup-close btn"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?></button>	
					<button type="submit" class="btn btn-primary"><?php echo JText::_('JSUBMIT') ?></button>					
		  		</div> 									
				<input type="hidden" name="option" value="com_qazap"/>
				<input type="hidden" name="order_id" value="<?php echo $order->order_id ?>"/>
				<input type="hidden" name="task" value="seller.updateOrderStatus" />
				<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
		</div>	
	</div>

	<div class="row-fluid qazap-order-addresses">
		<div class="address-container span6">
			<div class="user-address">
				<div class="address-title">
					<?php echo JText::_('COM_QAZAP_ORDERGROUP_BILLING_ADDRESS') ?>				
				</div>
				<div class="address">
					<?php if(!empty($this->orderDetails->billing_address)) : ?>
					<?php echo QZHelper::displayAddress($this->orderDetails->billing_address, $skip_address_fields) ?>
					<?php endif; ?>
				</div>			
			</div>		
		</div>
		<?php if(!$this->params->get('intangible') && !$this->params->get('downloadable')) : ?>
		<div class="address-container span6">
			<div class="user-address">
				<div class="address-title">
					<?php echo JText::_('COM_QAZAP_ORDERGROUP_SHIPPING_ADDRESS'); ?>		
				</div>
				<div class="address">
					<?php if(!empty($this->orderDetails->shipping_address)) : ?>
					<?php echo QZHelper::displayAddress($this->orderDetails->shipping_address, $skip_address_fields) ?>
					<?php endif; ?>
				</div>			
			</div>		
		</div>
		<?php endif; ?>
	</div>

	<table class="table table-striped">
		<thead>
			<tr style="background: #FAFAFA;">
				<th width="25%" style="text-align: left; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_FORM_LBL_PRODUCT_NAME') ?>
				</th>
				<th align="center" style="text-align: center; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_FORM_LBL_PRODUCT_SKU') ?>
				</th>
				<th align="right" style="text-align: right; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE') ?>
				</th>
				<th align="center" style="text-align: center; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_QUANTITY_UPDATE_DELETE') ?>
				</th>
				<th align="center" style="text-align: center; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_ORDER_ITEM_STATUS') ?>
				</th>			
				<th align="right" style="text-align: right; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_TAX_AMOUNT') ?>
				</th>
				<th align="right" style="text-align: right; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_DISCOUNT') ?>
				</th>
				<th align="right" style="text-align: right; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
					<?php echo JText::_ ('COM_QAZAP_TOTAL') ?>
				</th>						
			</tr>
		</thead>
		<tbody>
		<?php if(!empty($order)) : ?>
				<?php if(!empty($order->products)) : ?>			
					<?php foreach($order->products as $product) : 
					$deleted = ($product->order_status == 'D') ? 'class="deleted"' : '' ; ?>
					<tr <?php echo $deleted ?>>
						<td style="text-align: left; border-bottom: 1px solid #DDDDDD;">
							<?php echo $product->product_name ?>
							<?php if($varients = $product->getVarients()) : ?>
								<div class="product-varients"><?php echo $varients ?></div>
							<?php endif; ?>						
						</td>
						<td style="text-align: center; border-bottom: 1px solid #DDDDDD;">
							<?php echo $product->product_sku ?>
						</td>	
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($product->product_basepricewithVariants, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td style="text-align: center; border-bottom: 1px solid #DDDDDD;">
							<?php echo $product->product_quantity ?>					
						</td>
						<td class="nowrap center">
							<?php echo QZHelper::orderStatusNameByCode($product->order_status) ?>
							<?php if($product->order_status != 'D') : ?>
							<div>
								<a href="#change-order-status-popup-<?php echo $product->product_id?>" class="btn btn-small btn-success fancybox-popup">
									<i class="qzicon-pencil2"></i>&nbsp;<?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_EDIT')?>
								</a>	
							</div>
							<div class="hide">
								<div id="change-order-status-popup-<?php echo $product->product_id?>">
									<div class="qazap-popup">
										<div class="qazap-popup-title">
											<h3><?php echo JText::_('COM_QAZAP_ORDER_STATUS_EDIT_TITLE') ?></h3>
										</div>
										<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=seller&layout=order&ordergroup_id=' . (int) $this->orderDetails->ordergroup_id); ?>" method="post" name="adminForm" class="form-validate form-vertical">
										<div class="qazap-popup-content">
											<div class="control-group">
												<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_STATUS') ?></div>
												<div class="controls"><?php echo JHtml::_('qzstatus.orderstatus', $product->order_status) ?></div>
											</div>	
											<div class="control-group">
												<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_COMMENTS') ?></div>
												<div class="controls"><?php echo JHtml::_('qzstatus.comment') ?></div>
											</div>
										</div>
										<div class="qazap-popup-footer">
											<button type="button" class="qazap-popup-close btn"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?></button>	
											<button type="submit" class="btn btn-primary"><?php echo JText::_('JSUBMIT') ?></button>					
								  		</div> 									
										<input type="hidden" name="option" value="com_qazap"/>
										<input type="hidden" name="order_id" value="<?php echo $order->order_id ?>"/>
										<input type="hidden" name="task" value="seller.updateItemStatus" />
										<?php echo JHtml::_('form.token'); ?>
										</form>
									</div>
								</div>	
							</div>
							<?php endif; ?>											
						</td>
					
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($product->total_tax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>	
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($product->total_discount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>	
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($product->product_totalprice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>					
					</tr>
					<?php endforeach; ?>
					<!--Display products subtotal-->
					<tr>
						<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo JText::_('COM_QAZAP_PRODUCT_SUBTOTAL') ?>:
						</td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($order->productTotalTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($order->productTotalDiscount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($order->totalProductPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>										
					</tr>
					<!--Display coupon discount-->
					<?php if($order->coupon_code) : ?>
					<tr>
						<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo $order->coupon_data->html ?>:
						</td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($order->coupon_discount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>										
					</tr>
					<?php endif; ?>			
					<!--Display cart discount before tax rules-->
					<?php if(count($order->CartDiscountBeforeTaxInfo)) : ?>
						<?php foreach($order->CartDiscountBeforeTaxInfo as $dbt) : 
							$dbt = (object) $dbt; ?>
							<tr style="border-bottom: 1px solid #DDDDDD;">
								<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
									<?php echo JText::_($dbt->name) ?>:
								</td>
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
									<?php echo QZHelper::orderCurrencyDisplay($dbt->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
								</td>
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display cart tax rules-->
					<?php if(count($order->CartTaxInfo)) : ?>
						<?php foreach($order->CartTaxInfo as $tax) : 
							$tax = (object) $tax; ?>
							<tr>
								<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
									<?php echo JText::_($tax->name) ?>:
								</td>							
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
									<?php echo QZHelper::orderCurrencyDisplay($tax->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
								</td>
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display cart discount after tax rules-->
					<?php if(count($order->CartDiscountAfterTaxInfo)) : ?>
						<?php foreach($order->CartDiscountAfterTaxInfo as $dat) : 
							$dat = (object) $dat; ?>
							<tr>
								<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
									<?php echo JText::_($dat->name) ?>:
								</td>	
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>													
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
									<?php echo QZHelper::orderCurrencyDisplay($dat->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
								</td>
								<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display shipping method-->
					<?php if($this->orderDetails->cart_shipment_method_id) : ?>
						<tr>
							<td colspan="5" style="text-align: left; border-bottom: 1px solid #DDDDDD;">
								<?php echo QZDisplay::getShipmentMethodNameByID($this->orderDetails->cart_shipment_method_id, false) ?>
							</td>	
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo QZHelper::orderCurrencyDisplay($order->shipmentTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>													
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo QZHelper::orderCurrencyDisplay($order->shipmentPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>												
						</tr>	
					<?php endif; ?>
					<!--Display payment method-->
					<?php if($this->orderDetails->cart_payment_method_id) : ?>
						<tr>
							<td colspan="5" style="text-align: left; border-bottom: 1px solid #DDDDDD;">
								<?php echo QZDisplay::getPaymentMethodNameByID($this->orderDetails->cart_payment_method_id, false) ?>
							</td>	
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo QZHelper::orderCurrencyDisplay($order->paymentTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>													
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo QZHelper::orderCurrencyDisplay($order->paymentPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>												
						</tr>	
					<?php endif; ?>							
					<!--Display vendor cart total-->
					<tr class="qazap-vendor-total" style="background: #EEE">
						<td colspan="5" style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
							<?php echo JText::_('COM_QAZAP_VENDOR_CART_TOTAL') ?>:
						</td>	
						<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($order->TotalTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>													
						<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($order->TotalDiscount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZHelper::orderCurrencyDisplay($order->Total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>												
					</tr>			
				<?php endif; ?>					
			</tbody>		
		<?php endif; ?>	
	</table>
</div>

