<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;

if(empty($this->prices))
{
	return;
}
?>
<?php if(!empty($this->prices->product_salesprice)) : ?>
<div class="qazap-product-list-item-price">
	<?php if($this->params->get('display_original_price', 1) && ($this->prices->product_salespriceBeforeDiscount > $this->prices->product_salesprice)) : ?>
	<span class="price-before-discount">
		<?php echo QZHelper::displayPrice('product_salespriceBeforeDiscount', '', $this->prices, 'span'); ?>
	</span>
	<?php endif; ?>
	<span class="final-price">
		<?php echo QZHelper::displayPrice($this->params->get('displayed_price', 'product_salesprice'), '', $this->prices, 'span'); ?>
	</span>			
	<?php 
	// Other available price display options / variables
	/*
	echo QZHelper::displayPrice('product_baseprice', 'COM_QAZAP_BASE_PRICE', $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_basepricewithVariants', 'COM_QAZAP_BASE_PRICE_WITH_VARIENTS', $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_dbt', $this->prices->dbt_name, $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_basepriceBeforeTax', 'COM_QAZAP_BASE_PRICE_BEFORE_TAX', $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_tax', $this->prices->tax_name, $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_basepriceAfterTax', 'COM_QAZAP_BASE_PRICE_AFTER_TAX', $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_dat', $this->prices->dat_name, $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_discount', 'COM_QAZAP_TOTAL_DISCOUNT', $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_salespriceBeforeDiscount', 'COM_QAZAP_SALES_PRICE_BEFORE_DISCOUNT', $this->prices, 'div'); 
	echo QZHelper::displayPrice('product_salesprice', 'COM_QAZAP_SALES_PRICE', $this->prices, 'div');
	*/?>						
</div>
<?php elseif($this->params->get('contact_for_price', 1)) : ?>
	<!-- Show Contact For Price Button -->
	<div class="contact-link-cont">
		<a class="qazap-contactforprice-button fancybox-popup btn btn-warning" href="#qazap-contactforprice-popup-<?php echo $product->product_id ?>"><span><?php echo JText::_('COM_QAZAP_CONTACT_FOR_PRICE') ?></span></a>
	</div>
	<div class="qazap-hidden-items">
		<div id="qazap-contactforprice-popup-<?php echo $product->product_id ?>">
			<?php echo JLayoutHelper::render('qazap.products.contact_form', $displayData); ?>
		</div>	
	</div>		
<?php endif; ?>