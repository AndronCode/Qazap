<?php
/**
 * product.php
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

$product			= $displayData['product'];
$params				= $displayData['params'];	
$product_url	= $displayData['product_url'];
$url					= $displayData['url'];
$user					= $displayData['user'];
$loadJS				= $displayData['load_js'];
$class 				= (string) $this->options->get('class', '') ? ' ' . $this->options->get('class', '') : '';

if($loadJS)
{
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("
	// Display product rating
	jQ(document).ready(function () {
	  if (jQ.fn.raty) {
	    jQ('.qazap-product-list-item-rating').raty({
	      'score': function () {
	        return jQ(this).attr('data-score');
	      },
	      'path': window.qzpath + 'components/com_qazap/assets/images/',
	      'readOnly': true,
	      'starHalf': 'star-small-half.png',
	      'starOff': 'star-small-off.png',
	      'starOn': 'star-small-on.png'      
	    });
	  }
	});
	// End of product rating script
	");	
}
?>
<article class="qazap-product-list-item-inner<?php echo $class ?>">
	<div class="qazap-product-list-item-image">
		<a href="<?php echo JRoute::_($product_url);?>" title="<?php echo $product->product_name ?>">
			<?php echo QZImages::displaySingleImage($product->images) ?>
		</a>
	</div>
	<div class="qazap-product-list-item-bottom">
		<h3 class="qazap-product-list-item-title">
			<a href="<?php echo JRoute::_($product_url);?>" title="<?php echo $product->product_name ?>">
				<span><?php echo $product->product_name ?></span>
			</a>
		</h3>
		<?php if($params->get('show_seller', 1)) : ?>
		<div class="qazap-product-list-item-vendor">
			<em class="muted"><?php echo JText::sprintf('COM_QAZAP_SELLER', $product->shop_name) ?></em>
		</div>
		<?php endif; ?>
		<div class="qazap-product-list-item-rating-wrap tip hasTooltip" title="<?php echo JText::sprintf('COM_QAZAP_REVIEW_COUNT_SPRINTF', (int) $product->review_count) ?>">
			<span class="qazap-product-list-item-rating" data-score="<?php echo (float) $product->rating ?>" ></span>
		</div>						
		<?php if(!empty($product->prices->product_salesprice)) : ?>
		<div class="qazap-product-list-item-price">
			<?php if($params->get('display_original_price', 1) && ($product->prices->product_salespriceBeforeDiscount > $product->prices->product_salesprice)) : ?>
			<span class="price-before-discount">
				<?php echo QZHelper::displayPrice('product_salespriceBeforeDiscount', '', $product->prices, 'span'); ?>
			</span>
			<?php endif; ?>
			<span class="final-price">
				<?php echo QZHelper::displayPrice($params->get('displayed_price', 'product_salesprice'), '', $product->prices, 'span'); ?>
			</span>			
			<?php 
			// Other available price display options / variables
			/*
			echo QZHelper::displayPrice('product_baseprice', 'COM_QAZAP_BASE_PRICE', $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_basepricewithVariants', 'COM_QAZAP_BASE_PRICE_WITH_VARIENTS', $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_dbt', $product->prices->dbt_name, $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_basepriceBeforeTax', 'COM_QAZAP_BASE_PRICE_BEFORE_TAX', $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_tax', $product->prices->tax_name, $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_basepriceAfterTax', 'COM_QAZAP_BASE_PRICE_AFTER_TAX', $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_dat', $product->prices->dat_name, $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_discount', 'COM_QAZAP_TOTAL_DISCOUNT', $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_salespriceBeforeDiscount', 'COM_QAZAP_SALES_PRICE_BEFORE_DISCOUNT', $product->prices, 'div'); 
			echo QZHelper::displayPrice('product_salesprice', 'COM_QAZAP_SALES_PRICE', $product->prices, 'div');
			*/?>						
		</div>
		<?php elseif($params->get('contact_for_price', 1)) : ?>
			<?php $unique_id = $product->product_id . '-' . QZHelper::unique_id(); ?>
			<!-- Show Contact For Price Button -->
			<div class="contact-link-cont">
				<a class="qazap-contactforprice-button fancybox-popup btn btn-warning" href="#qazap-contactforprice-popup-<?php echo $unique_id ?>"><span><?php echo JText::_('COM_QAZAP_CONTACT_FOR_PRICE') ?></span></a>
			</div>
			<div class="qazap-hidden-items">
				<div id="qazap-contactforprice-popup-<?php echo $unique_id ?>">
					<?php echo JLayoutHelper::render('qazap.products.contact_form', $displayData); ?>
				</div>	
			</div>		
		<?php endif; ?>		
		
		<?php if($params->get('show_add_to_cart_productlist', 1) || $params->get('compare_system', 1) || $params->get('wishlist_system', 1)) : ?>			
		<div class="qazap-product-list-extra-options row-fluid">			
			<div class="width50">
				<!-- Show Add to Cart Button -->
				<?php if($params->get('show_add_to_cart_productlist', 1)) : ?>
					<?php echo JHtml::_('qzproduct.addtocart', $product, $params, $url, array('reload_params' => false)); ?>
				<?php endif; ?>
			</div>
			
			<div class="width50 align-right">
				<!-- Show Add to WishList Button -->
				<?php if($params->get('wishlist_system', 1)) : ?>
				<span class="add-to-wishlist-wrap">
					<form class="qazap-addtowishlist-form" action="<?php echo JRoute::_($url)?>" method="post">
						<button type="submit" class="addtowishlist-button btn btn-icon hasTooltip" title="<?php echo JText::_('COM_QAZAP_ADD_TO_WISHLIST') ?>"><i class="icon-heart-2"></i><span class="sr-only"><?php echo JText::_('COM_QAZAP_ADD_TO_WISHLIST') ?></span></button>
						<input type="hidden" name="option" value="com_qazap" />
						<input type="hidden" name="task" value="product.wishlist" />
						<input type="hidden" name="return" value="<?php echo base64_encode($url) ?>"/>
						<input type="hidden" name="qzform[product_id]" value="<?php echo $product->product_id ?>" />
						<input type="hidden" name="qzform[user_id]" value="<?php echo $user->get('id') ?>" />
						<?php echo JHtml::_('form.token'); ?>
					</form>					
				</span>
				<?php endif; ?>
				
				<!--  Show Add To Compare Button -->
				<?php if($params->get('compare_system', 1)) : ?>
				<span class="add-to-compare-wrap">
					<form class="qazap-addtocompare-form" action="<?php echo JRoute::_($url)?>" method="post">
						<button type="submit" name="submit" class="addtocompare-button btn btn-icon hasTooltip" title="<?php echo JText::_('COM_QAZAP_ADD_TO_COMPARE') ?>"><i class="icon-copy"></i><span class="sr-only"><?php echo JText::_('COM_QAZAP_ADD_TO_COMPARE') ?></span></button>
						<input type="hidden" name="option" value="com_qazap"/>
						<input type="hidden" name="task" value="compare.add"/>
						<input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
						<input type="hidden" name="product_id" value="<?php echo $product->product_id ?>" />
						<input type="hidden" name="product_name" value="<?php echo base64_encode($product->product_name) ?>" />
					</form>					
				</span>
				<?php endif; ?>
			</div>													
		</div>
		<?php endif; ?>
	</div>
</article>
