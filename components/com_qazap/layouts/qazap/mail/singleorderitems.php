<?php
/**
 * singleorderitems.php
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
$order = $displayData['order'];
$ordergroup = $displayData['ordergroup'];
?>
<table width="100%" cellpadding="5" cellspacing="0" border="0" style="border: 1px solid #DDDDDD;">
	<thead>
		<tr style="background: #FAFAFA;">
			<th width="25%" style="text-align: left; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
				<?php echo JText::_ ('COM_QAZAP_PRODUCT_NAME') ?>
			</th>
			<th align="center" style="text-align: center; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
				<?php echo JText::_ ('COM_QAZAP_PRODUCT_SKU') ?>
			</th>
			<th align="right" style="text-align: right; border-bottom: 1px solid #DDDDDD; padding: 8px 5px;">
				<?php echo JText::_ ('COM_QAZAP_PRODUCT_BASEPRICE') ?>
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
				<?php foreach($order->products as $product) : ?>
				<tr>
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
						<?php echo QazapHelper::orderCurrencyDisplay($product->product_basepricewithVariants, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>
					<td style="text-align: center; border-bottom: 1px solid #DDDDDD;">
						<?php echo $product->product_quantity ?>					
					</td>
					<td style="text-align: center; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderStatusNameByCode($product->order_status) ?>					
					</td>						
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($product->total_tax, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>	
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($product->total_discount, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>	
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($product->product_totalprice, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>					
				</tr>
				<?php endforeach; ?>
				<!--Display products subtotal-->
				<tr>
					<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo JText::_('COM_QAZAP_PRODUCT_SUBTOTAL') ?>:
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($order->productTotalTax, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($order->productTotalDiscount, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($order->totalProductPrice, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
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
						<?php echo QazapHelper::orderCurrencyDisplay($order->coupon_discount, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
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
								<?php echo QazapHelper::orderCurrencyDisplay($dbt->total, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
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
								<?php echo QazapHelper::orderCurrencyDisplay($tax->total, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
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
								<?php echo QazapHelper::orderCurrencyDisplay($dat->total, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
							</td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>												
						</tr>
					<?php endforeach; ?>				
				<?php endif; ?>
				<!--Display shipping method-->
				<?php if($ordergroup->cart_shipment_method_id) : ?>
					<tr>
						<td colspan="5" style="text-align: left; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZDisplay::getShipmentMethodNameByID($ordergroup->cart_shipment_method_id, false) ?>
						</td>	
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($order->shipmentTax, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
						</td>													
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($order->shipmentPrice, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
						</td>												
					</tr>	
				<?php endif; ?>
				<!--Display payment method-->
				<?php if($ordergroup->cart_payment_method_id) : ?>
					<tr>
						<td colspan="5" style="text-align: left; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZDisplay::getPaymentMethodNameByID($ordergroup->cart_payment_method_id, false) ?>
						</td>	
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($order->paymentTax, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
						</td>													
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($order->paymentPrice, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
						</td>												
					</tr>	
				<?php endif; ?>							
				<!--Display vendor cart total-->
				<tr class="qazap-vendor-total" style="background: #EEE">
					<td colspan="5" style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo JText::_('COM_QAZAP_VENDOR_CART_TOTAL') ?>:
					</td>	
					<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($order->TotalTax, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>													
					<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($order->TotalDiscount, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>
					<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($order->Total, $ordergroup->order_currency, $ordergroup->user_currency, $ordergroup->currency_exchange_rate) ?>
					</td>												
				</tr>			
			<?php endif; ?>					
		</tbody>		
	<?php endif; ?>	
</table>