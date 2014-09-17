<?php
/**
 * orderitems.php
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
?>
<?php if(!$displayData->user_id) : // If guest user send order details page link ?>
	<div style="background: #FAFAFA; padding: 10px; margin: 10px 0 15px 0;">
		<p style="padding: 0; margin: 0 0 5px 0;">
			<strong><?php echo JText::_('COM_QAZAP_ORDERGROUP_ACCESS_KEY_LABEL') ?>: </strong>
			<?php echo $displayData->access_key ?>
		</p>
		<p style="padding: 0; margin: 0 0 5px 0;">
			<strong><?php echo JText::_('COM_QAZAP_ORDERGROUP_DETAILS_PAGE') ?>: </strong>
			<a href="<?php echo QazapHelperRoute::mail(QazapHelperRoute::getOrderdetailsRoute($displayData->ordergroup_id)) ?>"><?php echo JText::_('COM_QAZAP_ORDERGROUP_DETAILS_PAGE_LINK_TEXT') ?></a>		
		</p>
	</div>
<?php endif; ?>
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
	<?php if(!empty($displayData->vendor_carts)) : 
		$i = 0;
		?>
		<?php foreach($displayData->vendor_carts as $vendor_cart) : ?>	
			<?php if(count($vendor_cart->products)) : ?>			
				<?php if($i > 0) : ?>
				<tr class="blank-row"><td colspan="8" style="border-bottom: 1px solid #DDDDDD;background: #FFF;height: 10px;"></td></tr>
				<?php endif; ?>
				<tr class="vendor-information" style="background: #fff8f2;">
					<td colspan="5" valign="top" align="left" style="text-align: left; border-bottom: 1px solid #DDDDDD;">
						<div><strong><?php echo JText::_('COM_QAZAP_ORDER_NUMBER') . ': ' . $vendor_cart->order_number ?></strong></div>
						<div style="font-style: italic; padding-top: 5px;"><?php echo JText::sprintf('COM_QAZAP_CART_VENDOR', $vendor_cart->shop_name) ?></div>
					</td>
					<td colspan="3" valign="top" align="right" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<strong><?php echo JText::_('COM_QAZAP_ORDERS_ORDER_STATUS') ?>: <?php echo QazapHelper::orderStatusNameByCode($vendor_cart->order_status) ?></strong>
					</td>												
				</tr>
				<?php foreach($vendor_cart->products as $product) : ?>
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
						<?php echo QazapHelper::orderCurrencyDisplay($product->product_basepricewithVariants, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>
					<td style="text-align: center; border-bottom: 1px solid #DDDDDD;">
						<?php echo $product->product_quantity ?>					
					</td>
					<td style="text-align: center; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderStatusNameByCode($product->order_status) ?>					
					</td>						
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($product->total_tax, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>	
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($product->total_discount, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>	
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($product->product_totalprice, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>					
				</tr>
				<?php 
				$i++;
				endforeach; ?>
				<!--Display products subtotal-->
				<tr>
					<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo JText::_('COM_QAZAP_PRODUCT_SUBTOTAL') ?>:
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->productTotalTax, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->productTotalDiscount, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->totalProductPrice, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>										
				</tr>
				<!--Display coupon discount-->
				<?php if($vendor_cart->coupon_code) : ?>
				<tr>
					<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo $vendor_cart->coupon_data->html ?>:
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->coupon_discount, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>
					<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>										
				</tr>
				<?php endif; ?>			
				<!--Display cart discount before tax rules-->
				<?php if(count($vendor_cart->CartDiscountBeforeTaxInfo)) : ?>
					<?php foreach($vendor_cart->CartDiscountBeforeTaxInfo as $dbt) : 
						$dbt = (object) $dbt; ?>
						<tr style="border-bottom: 1px solid #DDDDDD;">
							<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo JText::_($dbt->name) ?>:
							</td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo QazapHelper::orderCurrencyDisplay($dbt->total, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
							</td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>												
						</tr>
					<?php endforeach; ?>				
				<?php endif; ?>
				<!--Display cart tax rules-->
				<?php if(count($vendor_cart->CartTaxInfo)) : ?>
					<?php foreach($vendor_cart->CartTaxInfo as $tax) : 
						$tax = (object) $tax; ?>
						<tr>
							<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo JText::_($tax->name) ?>:
							</td>							
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo QazapHelper::orderCurrencyDisplay($tax->total, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
							</td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>												
						</tr>
					<?php endforeach; ?>				
				<?php endif; ?>
				<!--Display cart discount after tax rules-->
				<?php if(count($vendor_cart->CartDiscountAfterTaxInfo)) : ?>
					<?php foreach($vendor_cart->CartDiscountAfterTaxInfo as $dat) : 
						$dat = (object) $dat; ?>
						<tr>
							<td colspan="5" style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo JText::_($dat->name) ?>:
							</td>	
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>													
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
								<?php echo QazapHelper::orderCurrencyDisplay($dat->total, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
							</td>
							<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>												
						</tr>
					<?php endforeach; ?>				
				<?php endif; ?>
				<!--Display shipping method-->
				<?php if($displayData->cart_shipment_method_id) : ?>
					<tr>
						<td colspan="5" style="text-align: left; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZDisplay::getShipmentMethodNameByID($displayData->cart_shipment_method_id, false) ?>
						</td>	
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->shipmentTax, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
						</td>													
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->shipmentPrice, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
						</td>												
					</tr>	
				<?php endif; ?>
				<!--Display payment method-->
				<?php if($displayData->cart_payment_method_id) : ?>
					<tr>
						<td colspan="5" style="text-align: left; border-bottom: 1px solid #DDDDDD;">
							<?php echo QZDisplay::getPaymentMethodNameByID($displayData->cart_payment_method_id, false) ?>
						</td>	
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->paymentTax, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
						</td>													
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;"></td>
						<td style="text-align: right; border-bottom: 1px solid #DDDDDD;">
							<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->paymentPrice, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
						</td>												
					</tr>	
				<?php endif; ?>							
				<!--Display vendor cart total-->
				<tr class="qazap-vendor-total" style="background: #EEE">
					<td colspan="5" style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo JText::_('COM_QAZAP_VENDOR_CART_TOTAL') ?>:
					</td>	
					<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->TotalTax, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>													
					<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->TotalDiscount, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>
					<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
						<?php echo QazapHelper::orderCurrencyDisplay($vendor_cart->Total, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
					</td>												
				</tr>			
			<?php endif; ?>			
		<?php endforeach; ?>
			<!--Display grand total-->
			<tr class="blank-row" style="background: #EEE">
				<td colspan="8" style="height: 10px; background: #FFF; border-bottom: 1px solid #DDDDDD;"></td>
			</tr>
			<tr class="qazap-grand-total" style="background: #EEE">
				<td colspan="5" style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD; font-weight: bold;">
					<?php echo JText::_('COM_QAZAP_GRAND_TOTAL') ?>:
				</td>	
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;"></td>				
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;"></td>
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD; font-weight: bold;">
					<?php echo QazapHelper::orderCurrencyDisplay($displayData->cart_total, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
				</td>												
			</tr>	
			<tr class="qazap-payment-recived" style="background: #FAFAFA">
				<td colspan="5" style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
					<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_RECEIVED_LABEL') ?>:
				</td>	
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;"></td>				
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;"></td>
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
					<?php echo QazapHelper::orderCurrencyDisplay($displayData->payment_received, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
				</td>												
			</tr>
			<tr class="qazap-payment-refund" style="background: #FAFAFA">
				<td colspan="5" style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
					<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_REFUNDED_LABEL') ?>:
				</td>	
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;"></td>				
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;"></td>
				<td style="text-align: right; font-size: 16px; padding: 8px 5px; border-bottom: 1px solid #DDDDDD;">
					<?php echo QazapHelper::orderCurrencyDisplay($displayData->payment_refunded, $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
				</td>												
			</tr>
			<tr class="qazap-payment-balance" style="background: #FAFAFA">
				<td colspan="5" style="text-align: right; font-size: 16px; padding: 8px 5px;">
					<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_BALANCE_LABEL') ?>:
				</td>	
				<td style="text-align: right; font-size: 16px; padding: 8px 5px;"></td>				
				<td style="text-align: right; font-size: 16px; padding: 8px 5px;"></td>
				<td style="text-align: right; font-size: 16px; padding: 8px 5px;">
					<?php echo QazapHelper::orderCurrencyDisplay(($displayData->cart_total + $displayData->payment_refunded - $displayData->payment_received), $displayData->order_currency, $displayData->user_currency, $displayData->currency_exchange_rate) ?>
				</td>												
			</tr>											
		</tbody>		
	<?php endif; ?>	
</table>