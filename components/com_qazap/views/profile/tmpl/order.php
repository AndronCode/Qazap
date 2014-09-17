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
QZApp::loadJS();
QZApp::loadCSS();
?>
<div class="profile-<?php echo $this->layout . $this->pageclass_sfx ?>">
	<?php echo $this->menu ?>
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<header class="qz-page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</header>
	<?php endif; ?>
	<h4><?php echo JText::_('COM_QAZAP_ORDERGROUP_NUMBER_LABEL') . ': ' . $this->orderDetails->ordergroup_number ?></h4>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="25%">
					<?php echo JText::_ ('COM_QAZAP_ORDER_PRODUCT_NAME') ?>
				</th>
				<th align="center">
					<?php echo JText::_ ('COM_QAZAP_ORDER_PRODUCT_SKU') ?>
				</th>
				<th align="right" >
					<?php echo JText::_ ('COM_QAZAP_ORDER_PRODUCT_BASEPRICE') ?>
				</th>
				<th align="center">
					<?php echo JText::_ ('COM_QAZAP_QUANTITY_UPDATE_DELETE') ?>
				</th>
				<th align="center" >
					<?php echo JText::_ ('COM_QAZAP_ORDER_ITEM_STATUS') ?>
				</th>			
				<th align="right" >
					<?php echo JText::_ ('COM_QAZAP_TAX_AMOUNT') ?>
				</th>
				<th align="right" >
					<?php echo JText::_ ('COM_QAZAP_DISCOUNT') ?>
				</th>
				<th align="right" >
					<?php echo JText::_ ('COM_QAZAP_TOTAL') ?>
				</th>						
			</tr>
		</thead>
		<tbody>
		<?php if(!empty($this->orderDetails->vendor_carts)) : 
			$i = 0;
			?>
			<?php foreach($this->orderDetails->vendor_carts as $vendor_cart) : ?>	
				<?php if(count($vendor_cart->products)) : ?>			
					<?php if($i > 0) : ?>
					<tr class="blank-row"><td colspan="8"></td></tr>
					<?php endif; ?>
					<tr class="vendor-information">
						<td colspan="5" valign="top" align="left">
							<div><strong><?php echo JText::_('COM_QAZAP_ORDER_NUMBER') . ': ' . $vendor_cart->order_number ?></strong></div>
							<div><?php echo JText::sprintf('COM_QAZAP_CART_VENDOR', $vendor_cart->shop_name) ?></div>
						</td>
						<td colspan="3" valign="top" align="right">
							<strong><?php echo JText::_('COM_QAZAP_ORDERS_ORDER_STATUS') ?>: <?php echo QazapHelper::orderStatusNameByCode($vendor_cart->order_status) ?></strong>
						</td>												
					</tr>
					<?php foreach($vendor_cart->products as $product) : ?>
					<tr>
						<td>
							<?php echo $product->product_name ?>
							<?php if($varients = $product->getVarients()) : ?>
								<div class="product-varients"><?php echo $varients ?></div>
							<?php endif; ?>						
						</td>
						<td>
							<?php echo $product->product_sku ?>
						</td>	
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($product->product_basepricewithVariants, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td>
							<?php echo $product->product_quantity ?>
						</td>
						<td>
							<?php echo QazapHelper::orderStatusNameByCode($product->order_status) ?>
						</td>						
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($product->total_tax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>	
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($product->total_discount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>	
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($product->product_totalprice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>					
					</tr>
					<?php 
					$i++;
					endforeach; ?>
					<!--Display products subtotal-->
					<tr>
						<td colspan="5" class="right">
							<?php echo JText::_('COM_QAZAP_PRODUCT_SUBTOTAL') ?>:
						</td>
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->productTotalTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->productTotalDiscount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->totalProductPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>										
					</tr>
					<!--Display coupon discount-->
					<?php if($vendor_cart->coupon_code) : ?>
					<tr>
						<td colspan="5" class="right">
							<?php echo $vendor_cart->coupon_data->html ?>:
						</td>
						<td></td>
						<td>
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->coupon_discount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td></td>										
					</tr>
					<?php endif; ?>			
					<!--Display cart discount before tax rules-->
					<?php if(count($vendor_cart->CartDiscountBeforeTaxInfo)) : ?>
						<?php foreach($vendor_cart->CartDiscountBeforeTaxInfo as $dbt) : 
							$dbt = (object) $dbt; ?>
							<tr>
								<td colspan="5" class="right">
									<?php echo JText::_($dbt->name) ?>:
								</td>
								<td></td>
								<td>
									<?php echo QazapHelper::orderCurrencyDisplay($dbt->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
								</td>
								<td></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display cart tax rules-->
					<?php if(count($vendor_cart->CartTaxInfo)) : ?>
						<?php foreach($vendor_cart->CartTaxInfo as $tax) : 
							$tax = (object) $tax; ?>
							<tr>
								<td colspan="5" class="right">
									<?php echo JText::_($tax->name) ?>:
								</td>							
								<td>
									<?php echo QazapHelper::orderCurrencyDisplay($tax->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
								</td>
								<td></td>
								<td></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display cart discount after tax rules-->
					<?php if(count($vendor_cart->CartDiscountAfterTaxInfo)) : ?>
						<?php foreach($vendor_cart->CartDiscountAfterTaxInfo as $dat) : 
							$dat = (object) $dat; ?>
							<tr>
								<td colspan="5" class="right">
									<?php echo JText::_($dat->name) ?>:
								</td>	
								<td></td>													
								<td>
									<?php echo QazapHelper::orderCurrencyDisplay($dat->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
								</td>
								<td></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display shipping method-->
					<?php if($this->orderDetails->cart_shipment_method_id) : ?>
						<tr>
							<td colspan="5">
								<?php echo QZDisplay::getShipmentMethodNameByID($this->orderDetails->cart_shipment_method_id, false) ?>
							</td>	
							<td>
								<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->shipmentTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>													
							<td></td>
							<td>
								<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->shipmentPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>												
						</tr>	
					<?php endif; ?>
					<!--Display payment method-->
					<?php if($this->orderDetails->cart_payment_method_id) : ?>
						<tr>
							<td colspan="5">
								<?php echo QZDisplay::getPaymentMethodNameByID($this->orderDetails->cart_payment_method_id, false) ?>
							</td>	
							<td>
								<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->paymentTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>													
							<td></td>
							<td>
								<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->paymentPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>												
						</tr>	
					<?php endif; ?>							
					<!--Display vendor cart total-->
					<tr class="qazap-vendor-total" >
						<td colspan="5" >
							<?php echo JText::_('COM_QAZAP_VENDOR_CART_TOTAL') ?>:
						</td>	
						<td >
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->TotalTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>													
						<td >
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->TotalDiscount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td >
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->Total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>												
					</tr>			
				<?php endif; ?>			
			<?php endforeach; ?>
				<!--Display grand total-->
				<tr class="blank-row" >
					<td colspan="8"></td>
				</tr>
				<tr class="qazap-grand-total" >
					<td colspan="5">
						<?php echo JText::_('COM_QAZAP_GRAND_TOTAL') ?>:
					</td>	
					<td ></td>				
					<td ></td>
					<td>
						<?php echo QazapHelper::orderCurrencyDisplay($this->orderDetails->cart_total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>	
				<tr class="qazap-payment-recived" >
					<td colspan="5" >
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_RECEIVED_LABEL') ?>:
					</td>	
					<td ></td>				
					<td ></td>
					<td >
						<?php echo QazapHelper::orderCurrencyDisplay($this->orderDetails->payment_received, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>
				<tr class="qazap-payment-refund" >
					<td colspan="5" >
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_REFUNDED_LABEL') ?>:
					</td>	
					<td ></td>				
					<td ></td>
					<td >
						<?php echo QazapHelper::orderCurrencyDisplay($this->orderDetails->payment_refunded, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>
				<tr class="qazap-payment-balance" >
					<td colspan="5">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_BALANCE_LABEL') ?>:
					</td>	
					<td></td>				
					<td></td>
					<td>
						<?php echo QazapHelper::orderCurrencyDisplay(($this->orderDetails->cart_total + $this->orderDetails->payment_refunded - $this->orderDetails->payment_received), $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>											
			</tbody>		
		<?php endif; ?>	
	</table>
</div>