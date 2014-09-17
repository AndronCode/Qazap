<?php
/**
 * default_order.php
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
$skip_address_fields = array('order_address_id', 'ordergroup_id', 'address_type', 'address_name', 'country', 'states_territory');
?>
<div class="order-details">
	<?php //echo $this->menu ?>
	<header class="page-header">
		<h1><?php echo JText::_('COM_QAZAP_SELLER_ORDER_DETAILS') ?></h1>
	</header>
	<div class="order-details-info">
		<p>
			<strong><?php echo JText::_('COM_QAZAP_ORDERGROUP_NUMBER_LABEL') ?>: </strong>
			<?php echo $this->orderDetails->ordergroup_number ?>
		</p>
		<p>
			<strong><?php echo JText::_('COM_QAZAP_ORDERGROUP_DATE_LABEL') ?>: </strong>
			<?php echo QZHelper::displayDate($this->orderDetails->created_on) ?>
		</p>
		<p>
			<strong><?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_LABEL') ?>: </strong>
			<?php echo QZHelper::orderStatusNameByCode($this->orderDetails->order_status) ?>
		</p>
		<?php if(empty($this->orderDetails->user_id)) : // Display only for guest orders ?>
			<p>
				<strong><?php echo JText::_('COM_QAZAP_ORDERGROUP_ACCESS_KEY_LABEL') ?>: </strong>
				<?php echo $this->orderDetails->access_key ?>
			</p>
			<p>
				<strong><?php echo JText::_('COM_QAZAP_ORDERGROUP_DETAILS_PAGE') ?>: </strong>
				<a href="<?php echo JRoute::_(QazapHelperRoute::getOrderdetailsRoute($this->orderDetails->ordergroup_id)) ?>"><?php echo JText::_('COM_QAZAP_ORDERGROUP_DETAILS_PAGE_LINK_TEXT') ?></a>		
			</p>	
		<?php endif; ?>
	</div>		

	<?php if(!empty($this->orderDetails->billing_address) || !empty($this->orderDetails->shipping_address)) : ?>
	<div class="row-fluid qazap-selected-addresses">
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
					<?php echo JText::_('COM_QAZAP_ORDERGROUP_SHIPPING_ADDRESS');	?>			
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
	<?php endif; ?>	
	
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="25%">
					<?php echo JText::_ ('COM_QAZAP_ORDER_PRODUCT_NAME') ?>
				</th>
				<th class="center">
					<?php echo JText::_ ('COM_QAZAP_ORDER_PRODUCT_SKU') ?>
				</th>
				<th class="right" >
					<?php echo JText::_ ('COM_QAZAP_ORDER_PRODUCT_BASEPRICE') ?>
				</th>
				<th class="center">
					<?php echo JText::_ ('COM_QAZAP_QUANTITY_UPDATE_DELETE') ?>
				</th>
				<th class="center">
					<?php echo JText::_ ('COM_QAZAP_ORDER_ITEM_STATUS') ?>
				</th>			
				<th class="right">
					<?php echo JText::_ ('COM_QAZAP_TAX_AMOUNT') ?>
				</th>
				<th class="right">
					<?php echo JText::_ ('COM_QAZAP_DISCOUNT') ?>
				</th>
				<th class="right">
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
						<td colspan="5" valign="top" class="left">
							<div><strong><?php echo JText::_('COM_QAZAP_ORDER_NUMBER') . ': ' . $vendor_cart->order_number ?></strong></div>
							<?php if($this->params->get('show_order_seller', 1)) : ?>
								<div><?php echo JText::sprintf('COM_QAZAP_CART_VENDOR', $vendor_cart->shop_name) ?></div>
							<?php endif; ?>
						</td>
						<td colspan="3" valign="top" class="right">
							<strong><?php echo JText::_('COM_QAZAP_ORDERS_ORDER_STATUS') ?>: <?php echo QZHelper::orderStatusNameByCode($vendor_cart->order_status) ?></strong>
						</td>												
					</tr>
					<?php $i = 0; ?>
					<?php foreach($vendor_cart->products as $product) : 
						$i++;
						?>
					<tr<?php echo $product->deleted ? ' class="deleted"' : ''; ?>>
						<td>
							<?php $product_url = QazapHelperRoute::getProductRoute($product->product_id); ?>
							<a href="<?php echo JRoute::_($product_url) ?>" title="<?php echo $this->escape($product->product_name) ?>">
								<?php echo $this->escape($product->product_name) ?>
							</a>
							<?php if($varients = $product->getVarients()) : ?>
								<div class="product-varients"><?php echo $varients ?></div>
							<?php endif; ?>
							<?php if($this->params->get('downloadable')) : ?>
								<div class="download-info-link">
									<a class="fancybox-popup" href="#download-info-popup-<?php echo $i ?>">
										<span><?php echo JText::_('COM_QAZAP_DOWNLOAD_INFORMATION') ?></span>
									</a>
								</div>							
								<div class="qazap-hidden-items">
									<div id="download-info-popup-<?php echo $i ?>">
										<div class="qazap-popup">
											<div class="qazap-popup-title">
												<h3><?php echo JText::_('COM_QAZAP_DOWNLOAD_INFORMATION') ?></h3>
											</div>
											<div class="qazap-popup-content product-download-info">												
											<?php if(!empty($product->download_id)) : ?>
												<dl class="dl-horizontal">
													<dt><?php echo JText::_('COM_QAZAP_DOWNLOAD_FILE_NAME') ?></dt>
													<dd><?php echo $product->downloadable_file ?></dd>
													<dt><?php echo JText::_('COM_QAZAP_DOWNLOAD_START_DATE') ?></dt>
													<dd><?php echo QZHelper::displayDate($product->download_start_date) ?></dd>
													<dt><?php echo JText::_('COM_QAZAP_DOWNLOAD_EXPIRY_DATE') ?></dt>													
													<?php
													$validity = (int) $this->params->get('download_validity', 0);
													if($validity > 0) :
														$date = JFactory::getDate(($product->download_start_date . '+ 1 days'), 'UTC');
														$expiryDate =  $date->format('Y-m-d H:i:s', true, false); ?>															
														<dd><?php echo QZHelper::displayDate($expiryDate) ?></dd>
													<?php else : ?>
														<dd><?php echo JText::_('COM_QAZAP_DOWNLOAD_LIFETIME') ?></dd>
													<?php endif; ?>
													<dt><?php echo JText::_('COM_QAZAP_DOWNLOAD_COUNT') ?></dt>
													<dd><?php echo (int) $product->download_count ?></dd>
													<dt><?php echo JText::_('COM_QAZAP_LAST_DOWNLOAD_DATE') ?></dt>
													<dd><?php echo QZHelper::displayDate($product->last_download) ?></dd>
													<dt><?php echo JText::_('COM_QAZAP_DOWNLOADS_LEFT') ?></dt>
													<?php
													$limit = (int) $this->params->get('download_limit', 0);
													if($limit > 0) :
														$download_left = ($limit - (int) $product->download_count); ?>
														<dd><?php echo $download_left; ?></dd>
													<?php else : ?>
														<dd><?php echo JText::_('COM_QAZAP_DOWNLOAD_NOLIMIT') ?></dd>
													<?php endif; ?>
													<dt><?php echo JText::_('COM_QAZAP_DOWNLOAD_PAGE_URL') ?></dt>
													<dd><?php echo QZHelper::showURL(QazapHelperRoute::getDownloadRoute()) ?></dd>				
													<dt><?php echo JText::_('COM_QAZAP_DOWNLOAD_ID_LBL') ?></dt>
													<dd><?php echo $product->download_id ?></dd>
													<dt><?php echo JText::_('COM_QAZAP_ORDERGROUP_ACCESS_KEY_LABEL') ?></dt>
													<dd><?php echo $product->download_passcode ?></dd>							
												</dl>																							
											<?php else : ?>
												<div class="no-download-file"><?php echo JText::_('COM_QAZAP_PRODUCT_MISSING_DOWNLOADABLE_FILE') ?></div>
											<?php endif; ?>												
											</div>
											<div class="qazap-popup-footer">
												<button type="button" class="qazap-popup-close btn">
													<?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?>														
												</button>
												<?php if(!empty($product->download_id)) : ?>
												<a href="<?php echo JRoute::_(QazapHelperRoute::getDownloadRoute($product->download_id, $product->download_passcode)) ?>" class="btn btn-primary">
													<?php echo JText::_('COM_QAZAP_DOWNLOAD') ?>														
												</a>
												<?php endif; ?>
										  </div> 											
										</div>
									</div>
								</div>					
							<?php endif; ?>													
						</td>
						<td class="center">
							<?php echo $product->product_sku ?>
						</td>	
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($product->product_basepricewithVariants, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td class="center">
							<?php echo $product->product_quantity ?>					
						</td>
						<td class="right">
							<?php echo QZHelper::orderStatusNameByCode($product->order_status) ?>					
						</td>						
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($product->total_tax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>	
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($product->total_discount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>	
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($product->product_totalprice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
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
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->productTotalTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->productTotalDiscount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->totalProductPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>										
					</tr>
					<!--Display coupon discount-->
					<?php if($vendor_cart->coupon_code) : ?>
					<tr>
						<td colspan="5" class="right">
							<?php echo $vendor_cart->coupon_data->html ?>:
						</td>
						<td></td>
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->coupon_discount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
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
								<td class="right">
									<?php echo QZHelper::orderCurrencyDisplay($dbt->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
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
								<td class="right">
									<?php echo QZHelper::orderCurrencyDisplay($tax->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
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
								<td class="right">
									<?php echo QZHelper::orderCurrencyDisplay($dat->total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
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
							<td class="right">
								<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->shipmentTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>													
							<td></td>
							<td class="right">
								<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->shipmentPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>												
						</tr>	
					<?php endif; ?>
					<!--Display payment method-->
					<?php if($this->orderDetails->cart_payment_method_id) : ?>
						<tr>
							<td colspan="5">
								<?php echo QZDisplay::getPaymentMethodNameByID($this->orderDetails->cart_payment_method_id, false) ?>
							</td>	
							<td class="right">
								<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->paymentTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>													
							<td></td>
							<td class="right">
								<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->paymentPrice, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
							</td>												
						</tr>	
					<?php endif; ?>							
					<!--Display vendor cart total-->
					<tr class="qazap-vendor-total" >
						<td colspan="5" class="right">
							<?php echo JText::_('COM_QAZAP_VENDOR_CART_TOTAL') ?>:
						</td>	
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->TotalTax, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>													
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->TotalDiscount, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>
						<td class="right">
							<?php echo QZHelper::orderCurrencyDisplay($vendor_cart->Total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
						</td>												
					</tr>			
				<?php endif; ?>			
			<?php endforeach; ?>
				<!--Display grand total-->
				<tr class="blank-row" >
					<td colspan="8"></td>
				</tr>
				<tr class="qazap-grand-total" >
					<td colspan="5" class="right">
						<?php echo JText::_('COM_QAZAP_GRAND_TOTAL') ?>:
					</td>	
					<td ></td>				
					<td ></td>
					<td class="right">
						<?php echo QZHelper::orderCurrencyDisplay($this->orderDetails->cart_total, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>	
				<tr class="qazap-payment-recived" >
					<td colspan="5" class="right">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_RECEIVED_LABEL') ?>:
					</td>	
					<td ></td>				
					<td ></td>
					<td class="right">
						<?php echo QZHelper::orderCurrencyDisplay($this->orderDetails->payment_received, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>
				<tr class="qazap-payment-refund" >
					<td colspan="5" class="right">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_REFUNDED_LABEL') ?>:
					</td>	
					<td></td>				
					<td></td>
					<td class="right">
						<?php echo QZHelper::orderCurrencyDisplay($this->orderDetails->payment_refunded, $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>
				<tr class="qazap-payment-balance" >
					<td colspan="5" class="right">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_BALANCE_LABEL') ?>:
					</td>	
					<td></td>				
					<td></td>
					<td class="right">
						<?php echo QZHelper::orderCurrencyDisplay(($this->orderDetails->cart_total + $this->orderDetails->payment_refunded - $this->orderDetails->payment_received), $this->orderDetails->order_currency, $this->orderDetails->user_currency, $this->orderDetails->currency_exchange_rate) ?>
					</td>												
				</tr>											
			</tbody>		
		<?php endif; ?>	
	</table>
</div>