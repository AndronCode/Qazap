<?php
/**
 * default.php
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
JHtml::_('bootstrap.tooltip');
$params  = $this->item->params;
$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js', 'cloud-zoom.1.0.2.modified.js', 'jquery.raty.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
$canEdit = ($this->isActiveVendor && ($this->vendor_id == $this->item->vendor)) ? true : false;
?>
<article class="qazap-product-details" itemscope itemtype="http://schema.org/Product">

	<?php if ($this->params->get('show_page_heading', 1) && $this->params->get('page_heading')) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>

	<?php if (!$this->print) : ?>
		<div class="btn-toolbar clearfix">			
			<?php if ($canEdit || $params->get('print_view_link') || $params->get('email_icon')) : ?>	
			<div class="btn-group pull-right">		
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> <span class="icon-cog"></span> <span class="caret"></span> </a>
				<ul class="dropdown-menu actions">
					<?php if ($params->get('print_view_link')) : ?>
					<li class="print-icon"> <?php echo JHtml::_('qzicon.print_popup', $this->item, $params); ?> </li>
					<?php endif; ?>
					<?php if ($params->get('email_icon')) : ?>
					<li class="email-icon"> <?php echo JHtml::_('qzicon.email', $this->item, $params); ?> </li>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
					<li class="edit-icon"> <?php echo JHtml::_('qzicon.edit', $this->item, $params); ?> </li>
					<?php endif; ?>
				</ul>	
				</div>		
			<?php endif; ?>
			<?php if($params->get('show_category_product', 1) && $params->get('show_link_category_product', 1) && !empty($this->item->category_name)) : ?>		
				<div class="btn-group pull-right">
					<a class="btn" href="<?php echo JRoute::_(QazapHelperRoute::getCategoryRoute($this->item->category_id));?>" title="<?php echo $this->escape($this->item->category_name) ?>">
						<?php echo JText::sprintf('COM_QAZAP_BACK_TO_CATEGORY', $this->escape($this->item->category_name)) ?>					
					</a>
				</div>
			<?php endif; ?>			
		</div>
	<?php else : ?>
		<div id="pop-print" class="btn hidden-print">
			<?php echo JHtml::_('qzicon.print_screen', $this->item, $params); ?>
		</div>
	<?php endif; ?>
	<div class="row-fluid">
		<div class="span5">
			<figure class="qazap-product-images">
				<?php echo QZImages::display($this->item->images) ?>
			</figure>
			<?php echo $this->loadTemplate('children'); ?>			
		</div>
		<div class="span7">
			<div class="qazap-product-name">
				<h1 class="product-name" itemprop="name"><?php echo $this->item->product_name?></h1>
				<?php if($params->get('show_seller', 1)) :
					$url = QazapHelperRoute::getVendorRoute($this->item->vendor); 
					$seller = '<a href="' . JRoute::_($url) . '">' . $this->escape($this->item->shop_name) . '</a>';
					?>
					<em class="muted" itemprop="seller"><?php echo JText::sprintf('COM_QAZAP_SELLER', $seller) ?></em>
				<?php endif; ?>
				<?php if($params->get('show_manufacturer', 1) && !empty($this->item->manufacturer_name)) :
					$url = QazapHelperRoute::getBrandRoute($this->item->manufacturer_id); 
					$brand = '<a href="' . JRoute::_($url) . '">' . $this->escape($this->item->manufacturer_name) . '</a>';
					?>
					<em class="muted" itemprop="seller"><?php echo JText::sprintf('COM_QAZAP_BRAND', $brand) ?></em>
				<?php endif; ?>
				<?php if($params->get('show_category_product', 1) && !$params->get('show_link_category_product', 1) && !empty($this->item->category_name)) : ?>
					<em class="muted" itemprop="seller"><?php echo JText::sprintf('COM_QAZAP_CATEGORY', $this->escape($this->item->category_name)) ?></em>
				<?php endif; ?>											
			</div>
			
			<?php if($params->get('show_rating', 1)) : ?>
				<div class="qazap-product-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
					<meta itemprop="ratingValue" content="<?php echo (float) $this->item->rating ?>">
					<meta itemprop="ratingCount" content="<?php echo $this->item->review_count ?>">
					<div class="average-product-rating" data-score="<?php echo (float) $this->item->rating ?>"></div>
					<span class="review-count"><?php echo JText::sprintf('COM_QAZAP_REVIEW_COUNT_SPRINTF', (int) $this->item->review_count) ?></span>
				</div>
			<?php endif; ?>
			
			<div class="qazap-product-prices qazap-ajax-update-<?php echo $this->item->product_id ?>">
				<?php if(empty($this->item->prices->product_salesprice) && $params->get('contact_for_price', 1)) : ?>
				<!-- Show Contact For Price Button -->
				<a class="qazap-contactforprice-button fancybox-popup btn" href="#qazap-contactforprice-popup">
					<span><?php echo JText::_('COM_QAZAP_CONTACT_FOR_PRICE') ?></span>
				</a>
				<div class="qazap-hidden-items">
					<div id="qazap-contactforprice-popup">
						<?php echo $this->loadTemplate('contact'); ?>
					</div>	
				</div>									
				<?php elseif($this->item->prices->product_salesprice) : ?>
				<!-- Show Prices -->
				<div class="qazap-sales-price-wrap" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
					<meta itemprop="price" content="<?php echo QZHelper::currencyDisplay($this->item->prices->product_salesprice); ?>" />
					<?php if($params->get('display_original_price', 1) && ($this->item->prices->product_salespriceBeforeDiscount > $this->item->prices->product_salesprice)) : ?>
					<span class="price-before-discount">
						<?php echo QZHelper::displayPrice('product_salespriceBeforeDiscount', '', $this->item->prices, 'span'); ?>
					</span>
					<?php endif; ?>
					<span class="final-price">
						<?php echo QZHelper::displayPrice($params->get('displayed_price', 'product_salesprice'), '', $this->item->prices, 'span'); ?>
					</span>
					<?php if($this->item->in_stock - $this->item->booked_order > 0 || !$params->get('enablestockcheck')) : ?>
					<meta itemprop="availability" content="in_stock" />
					<?php else : ?>
					<meta itemprop="availability" content="out_of_stock" />
					<?php endif; ?>			
				</div>
				<div class="qazap-product-price-breakup">
					<div>
						<?php echo QZHelper::displayPrice('product_baseprice', 'COM_QAZAP_BASE_PRICE', $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_basepricewithVariants', 'COM_QAZAP_BASE_PRICE_WITH_VARIENTS', $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_dbt', $this->item->prices->dbt_name, $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_basepriceBeforeTax', 'COM_QAZAP_BASE_PRICE_BEFORE_TAX', $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_tax', $this->item->prices->tax_name, $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_basepriceAfterTax', 'COM_QAZAP_BASE_PRICE_AFTER_TAX', $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_dat', $this->item->prices->dat_name, $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_discount', 'COM_QAZAP_TOTAL_DISCOUNT', $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_salespriceBeforeDiscount', 'COM_QAZAP_SALES_PRICE_BEFORE_DISCOUNT', $this->item->prices, 'div'); ?>
						<?php echo QZHelper::displayPrice('product_salesprice', 'COM_QAZAP_SALES_PRICE', $this->item->prices, 'div'); ?>
					</div>					
				</div>
				<?php endif; ?>
			</div>
			<div class="qazap-product-availability-sku row-fluid">
				<div class="qazap-product-availability span6">
					<?php echo $this->loadTemplate('availability'); ?>					
				</div>
				<div class="qazap-product-sku span6">
					<span><?php echo JText::_('COM_QAZAP_PRODUCT_SKU') ?>:&nbsp;<?php echo $this->item->product_sku?></span>
				</div>
			</div>
			<?php if(!empty($this->item->short_description)) : ?>
			<div class="qazap-product-overview">
				<h3 class="quick-overview"><?php echo JText::_('COM_QAZAP_PRODUCT_SHORT_DESCRIPTION') ?></h3>
				<p><?php echo $this->item->short_description ?></p>
			</div>
			<?php endif; ?>
			<div class="qazap-addtocart-wrap">
				<?php echo $this->loadTemplate('addtocart'); ?>
			</div>
			<div class="qazap-additional-options">
				<!-- Show Ask a question button -->
				<?php if($params->get('ask_seller')) : ?>
					<a class="qazap-askquestion-button fancybox-popup btn" href="#qazap-askquestion-popup">
						<span><?php echo JText::_('COM_QAZAP_ASK_A_QUESTION') ?></span>
					</a>
					<div class="qazap-hidden-items">
						<div id="qazap-askquestion-popup">
							<?php echo $this->loadTemplate('askquestion'); ?>
						</div>	
					</div>
				<?php endif; ?>
				<!-- Show Add to WishList Button -->
				<?php if($params->get('wishlist_system', 1)) : ?>
					<span class="add-to-wishlist-wrap">
						<form class="qazap-addtowishlist-form" action="<?php echo JRoute::_($this->product_url)?>" method="post">
							<button type="submit" class="addtowishlist-button btn"><?php echo JText::_('COM_QAZAP_ADD_TO_WISHLIST') ?></button>
							<input type="hidden" name="option" value="com_qazap" />
							<input type="hidden" name="task" value="product.wishlist" />
							<input type="hidden" name="return" value="<?php echo base64_encode($this->product_url) ?>" />
							<input type="hidden" name="qzform[product_id]" value="<?php echo (int) $this->item->product_id ?>" />
							<input type="hidden" name="qzform[user_id]" value="<?php echo $this->user->id ?>" />
							<?php echo JHtml::_('form.token'); ?>
						</form>					
					</span>
				<?php endif; ?>
				<!--  Show Add To Compare Button -->
				<?php if($params->get('compare_system', 1)) : ?>
					<span class="add-to-compare-wrap">
						<form class="qazap-addtocompare-form" action="<?php echo JRoute::_($this->product_url)?>" method="post">
							<button type="submit" name="submit" class="addtocompare-button btn"><?php echo JText::_('COM_QAZAP_ADD_TO_COMPARE') ?></button>
							<input type="hidden" name="option" value="com_qazap"/>
							<input type="hidden" name="task" value="compare.add"/>
							<input type="hidden" name="return" value="<?php echo base64_encode($this->product_url) ?>" />
							<input type="hidden" name="product_id" value="<?php echo (int) $this->item->product_id ?>" />
							<input type="hidden" name="product_name" value="<?php echo base64_encode($this->item->product_name) ?>" />
						</form>					
					</span>
				<?php endif; ?>
			</div>
			<?php if ($params->get('show_product_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
			<div class="qazap-product-tags">								
				<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
				<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>							
			</div>
			<?php endif; ?>	
		</div>
	</div>
	<!--  Show Product Description -->
	<?php if($this->item->product_description) : ?>
	<div class="row-fluid">
		<div class="span12">
			<h4 class="qazap-product-description-title"><?php echo JText::_('COM_QAZAP_PRODUCT_DESCRIPTION') ?>:</h4>
			<p><?php echo $this->item->product_description ?></p>
		</div>
	</div>
	<?php endif; ?>
	<!--  Show Standard Custom Fields -->
	<div class="row-fluid">
		<div class="span12">
			<?php 
			$this->layout_position = 'standard'; 
			echo $this->loadTemplate('customfields'); 
			?>
		</div>
	</div>
	<!--  Show Siblings -->
	<?php echo $this->loadTemplate('siblings'); ?>	
	<!--  Show Product Reviews and Review Form -->
	<?php if($params->get('review_enabled', 1)) : ?>
		<?php echo $this->loadTemplate('reviews'); ?>
	<?php endif; ?>
	<!--  Show Related Products -->
	<?php echo $this->loadTemplate('relatedproducts'); ?>	
	<!--  Show Related Categories -->
	<?php echo $this->loadTemplate('relatedcategories'); ?>			
</article>