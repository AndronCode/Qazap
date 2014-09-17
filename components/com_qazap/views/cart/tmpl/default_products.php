<?php
/**
 * default_products.php
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
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th>
				<?php echo JText::_('COM_QAZAP_PRODUCT_NAME') ?>
			</th>
			<th class="center">
				<?php echo JText::_('COM_QAZAP_PRODUCT_SKU') ?>
			</th>
			<th class="right">
				<?php echo JText::_('COM_QAZAP_PRODUCT_BASEPRICE') ?>
			</th>
			<th class="center no-wrap">
				<?php echo JText::_('COM_QAZAP_QUANTITY_UPDATE_DELETE') ?>
			</th>
			<th class="right">
				<?php echo JText::_('COM_QAZAP_TAX_AMOUNT') ?>
			</th>
			<th class="right">
				<?php echo JText::_('COM_QAZAP_DISCOUNT') ?>
			</th>
			<th class="right">
				<?php echo JText::_('COM_QAZAP_TOTAL') ?>
			</th>						
		</tr>
	</thead>
	<?php if(count($this->cart->vendor_carts)) : ?>
		<?php foreach($this->cart->vendor_carts as $vendor_cart) : ?>	
			<?php if(count($vendor_cart->products)) : ?>
			<tbody>
				<tr class="vendor-information">
					<td colspan="7" class="left">
						<?php echo JText::sprintf('COM_QAZAP_CART_VENDOR', $vendor_cart->shop_name) ?>
					</td>						
				</tr>
				<?php foreach($vendor_cart->products as $product) : ?>
				<tr>
					<td>
						<?php $product_url = QazapHelperRoute::getProductRoute($product->slug, $product->category_id); ?>
						<a href="<?php echo JRoute::_($product_url) ?>" title="<?php echo $this->escape($product->product_name) ?>">
							<?php echo $this->escape($product->product_name) ?>
						</a>
						<?php if($varients = $product->getVarients()) : ?>
							<div class="product-varients"><?php echo $varients ?></div>
						<?php endif; ?>						
					</td>
					<td class="center">
						<?php echo $product->product_sku ?>
					</td>	
					<td class="right">
						<?php echo QZHelper::currencyDisplay($product->product_basepricewithVariants) ?>
					</td>
					<td class="center no-wrap">
						<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart') ?>" class="qazap-qty-update form-inline"  method="post">
							<div class="input-append">
								<input type="text" name="qzform[quantity]" class="inputbox input-mini" value="<?php echo $product->product_quantity ?>" />
								<input type="hidden" name="option" value="com_qazap"/>
								<input type="hidden" name="task" value="cart.update" />
								<input type="hidden" name="product_name" value="<?php echo base64_encode($product->product_name) ?> "/>
								<input type="hidden" name="qzform[group_id]" value="<?php echo $product->group_id ?>" />								
								<button type="submit" class="qazap-qty-update-button btn" title="<?php echo  JText::_('COM_QAZAP_UPDATE_PRODUCT_CART') ?>" ><i class="icon-refresh"></i></button>
							</div>						
						</form>
						<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart') ?>" class="qazap-product-remove form-inline" method="post">
							<input type="hidden" name="option" value="com_qazap"/>
							<input type="hidden" name="task" value="cart.remove" />
							<input type="hidden" name="product_name" value="<?php echo base64_encode($product->product_name) ?> "/>
							<input type="hidden" name="qzform[vendor]" value="<?php echo $product->vendor ?>" />
							<input type="hidden" name="qzform[group_id]" value="<?php echo $product->group_id ?>" />								
							<button type="submit" class="qazap-remove-button btn" title="<?php echo  JText::_('COM_QAZAP_REMOVE_PRODUCT_CART') ?>" ><i class="icon-trash"></i></button>
						</form>						
					</td>	
					<td class="right">
						<?php echo QZHelper::currencyDisplay($product->total_tax) ?>
					</td>	
					<td class="right">
						<?php echo QZHelper::currencyDisplay($product->total_discount) ?>
					</td>	
					<td class="right">
						<?php echo QZHelper::currencyDisplay($product->product_totalprice) ?>
					</td>					
				</tr>
				<?php endforeach; ?>
				<!--Display products subtotal-->
				<tr>
					<td colspan="4" class="right">
						<?php echo JText::_('COM_QAZAP_PRODUCT_SUBTOTAL') ?>:
					</td>
					<td class="right">
						<?php echo QZHelper::currencyDisplay($vendor_cart->productTotalTax) ?>
					</td>
					<td class="right">
						<?php echo QZHelper::currencyDisplay($vendor_cart->productTotalDiscount) ?>
					</td>
					<td class="right">
						<?php echo QZHelper::currencyDisplay($vendor_cart->totalProductPrice) ?>
					</td>										
				</tr>
				<!--Display coupon discount-->
				<?php if($vendor_cart->coupon_code) : ?>
				<tr>
					<td colspan="4" class="right">
						<?php echo $vendor_cart->coupon_data->html ?>:
					</td>
					<td></td>
					<td class="right">
						<?php echo QZHelper::currencyDisplay($vendor_cart->coupon_discount) ?>
					</td>
					<td></td>										
				</tr>
				<?php endif; ?>			
				<!--Display cart discount before tax rules-->
				<?php if(count($vendor_cart->CartDiscountBeforeTaxInfo)) : ?>
					<?php foreach($vendor_cart->CartDiscountBeforeTaxInfo as $dbt) : ?>
						<tr>
							<td colspan="4" class="right">
								<?php echo JText::_($dbt->name) ?>:
							</td>
							<td></td>
							<td class="right">
								<?php echo QZHelper::currencyDisplay($dbt->total) ?>
							</td>
							<td></td>												
						</tr>
					<?php endforeach; ?>				
				<?php endif; ?>
				<!--Display cart tax rules-->
				<?php if(count($vendor_cart->CartTaxInfo)) : ?>
					<?php foreach($vendor_cart->CartTaxInfo as $tax) : ?>
						<tr>
							<td colspan="4" class="right">
								<?php echo JText::_($tax->name) ?>:
							</td>							
							<td class="right">
								<?php echo QZHelper::currencyDisplay($tax->total) ?>
							</td>
							<td></td>
							<td></td>												
						</tr>
					<?php endforeach; ?>				
				<?php endif; ?>
				<!--Display cart discount after tax rules-->
				<?php if(count($vendor_cart->CartDiscountAfterTaxInfo)) : ?>
					<?php foreach($vendor_cart->CartDiscountAfterTaxInfo as $dat) : ?>
						<tr>
							<td colspan="4" class="right">
								<?php echo JText::_($dat->name) ?>:
							</td>	
							<td></td>													
							<td class="right">
								<?php echo QZHelper::currencyDisplay($dat->total) ?>
							</td>
							<td></td>												
						</tr>
					<?php endforeach; ?>				
				<?php endif; ?>
				<!--Display shipping method-->
				<?php if($this->cart->cart_shipment_method_id) : ?>
					<tr>
						<td colspan="4" class="left">
							<?php echo $this->cart->cart_shipment_method_html ?>
							<div class="qazap-edit-sp-wrap">
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=cart&layout=select_shipping') ?>" class="btn btn-mini">
									<?php echo JText::_('COM_QAZAP_CART_CHANGE_SHIPMENT_METHOD') ?>
								</a>
							</div>
						</td>	
						<td class="right">
							<?php echo QZHelper::currencyDisplay($vendor_cart->shipmentTax) ?>
						</td>													
						<td class="right"></td>
						<td class="right">
							<?php echo QZHelper::currencyDisplay($vendor_cart->shipmentPrice) ?>
						</td>												
					</tr>	
				<?php endif; ?>
				<!--Display payment method-->
				<?php if($this->cart->cart_payment_method_id) : ?>
					<tr>
						<td colspan="4" class="left">
							<?php echo $this->cart->cart_payment_method_html ?>
							<div class="qazap-edit-sp-wrap">
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=cart&layout=select_payment') ?>" class="btn btn-mini">
									<?php echo JText::_('COM_QAZAP_CART_CHANGE_PAYMENT_METHOD') ?>
								</a>
							</div>							
						</td>	
						<td class="right">
							<?php echo QZHelper::currencyDisplay($vendor_cart->paymentTax) ?>
						</td>													
						<td class="right"></td>
						<td class="right">
							<?php echo QZHelper::currencyDisplay($vendor_cart->paymentPrice) ?>
						</td>												
					</tr>	
				<?php endif; ?>							
				<!--Display vendor cart total-->
				<tr class="qazap-vendor-total">
					<td colspan="4" class="right">
						<?php echo JText::_('COM_QAZAP_VENDOR_CART_TOTAL') ?>:
					</td>	
					<td class="right">
						<?php echo QZHelper::currencyDisplay($vendor_cart->TotalTax) ?>
					</td>													
					<td class="right">
						<?php echo QZHelper::currencyDisplay($vendor_cart->TotalDiscount) ?>
					</td>
					<td class="right">
						<?php echo QZHelper::currencyDisplay($vendor_cart->Total) ?>
					</td>												
				</tr>						
			</tbody>
			<?php endif; ?>			
		<?php endforeach; ?>		
	<?php endif; ?>	
</table>