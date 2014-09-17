<?php
/**
 * default_children.php
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

// Child product considers its parent product params as global
$params  = $this->item->params;
// Return if children products are available
if(!$this->children)
{
	return;
}
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
// Display child product rating
jQ(document).ready(function () {
  if (jQ.fn.raty) {
    jQ('.child-product-rating').raty({
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
?>
<div class="child-products-wrap">
	<h3><?php echo JText::_('COM_QAZAP_CHILD_PRODUCTS') ?></h3>
	<div class="qazap-releated-products">
		<?php foreach($this->children as $child) : 
			$child_url = QazapHelperRoute::getProductRoute($child->product_id, $child->category_id);
			$displayData = array();
			$displayData['product'] = $child;
			$displayData['params'] = $params;
			$displayData['product_url'] = $child_url;
			$displayData['url'] = $this->product_url;
			$displayData['user'] = $this->user;			
			?>
			<div class="row-fluid">
				<div class="child-product span12">
					<div class="inner clearfix">
						<div class="child-image-rating">
							<div class="child-image">
								<a href="<?php echo JRoute::_($child_url);?>" title="<?php echo $child->product_name ?>">
									<?php echo QZImages::displaySingleImage($child->images) ?>
								</a>
							</div>
							<div class="child-rating">
								<span class="child-rating-wrap tip hasTooltip" title="<?php echo JText::sprintf('COM_QAZAP_REVIEW_COUNT_SPRINTF', (int) $child->review_count) ?>">
									<span class="child-product-rating" data-score="<?php echo (float) $child->rating ?>" ></span>
								</span>								
							</div>														
						</div>
						<div class="child-details">
							<div class="inner">
								<h4>
									<a href="<?php echo JRoute::_($child_url) ?>" title="<?php echo $child->product_name ?>">
										<?php echo $this->escape($child->product_name) ?>			
									</a>
								</h4>
								<div class="child-price">
									<?php if(!empty($child->prices->product_salesprice)) : ?>
										<?php if($params->get('display_original_price', 1) && ($child->prices->product_salespriceBeforeDiscount > $child->prices->product_salesprice)) : ?>
										<span class="price-before-discount">
											<?php echo QZHelper::displayPrice('product_salespriceBeforeDiscount', '', $child->prices, 'span'); ?>
										</span>
										<?php endif; ?>
										<span class="final-price">
											<?php echo QZHelper::displayPrice($params->get('displayed_price', 'product_salesprice'), '', $child->prices, 'span'); ?>
										</span>				
									<?php elseif($params->get('contact_for_price', 1)) : ?>
										<!-- Show Contact For Price Button -->
										<span class="contact-link-cont">
											<a class="qazap-contactforprice-button fancybox-popup btn btn-warning" href="#qazap-contactforprice-popup-<?php echo $child->product_id ?>">
												<span><?php echo JText::_('COM_QAZAP_CONTACT_FOR_PRICE') ?></span>
											</a>
										</span>
										<div class="qazap-hidden-items">
											<div id="qazap-contactforprice-popup-<?php echo $child->product_id ?>">
												<?php echo JLayoutHelper::render('qazap.products.contact_form', $displayData); ?>
											</div>	
										</div>		
									<?php endif; ?>										
								</div>
								<div class="child-actions">
									<!-- Show Add to Cart Button -->
									<?php echo JHtml::_('qzproduct.addtocart', $child, $params, $this->product_url); ?>
									
									<!-- Show Add to WishList Button -->
									<span class="add-to-wishlist-wrap">
										<form class="qazap-addtowishlist-form" action="<?php echo JRoute::_($this->product_url)?>" method="post">
											<button type="submit" class="addtowishlist-button btn btn-icon hasTooltip" title="<?php echo JText::_('COM_QAZAP_ADD_TO_WISHLIST') ?>">
												<i class="icon-heart-2"></i><span class="sr-only"><?php echo JText::_('COM_QAZAP_ADD_TO_WISHLIST') ?></span>
											</button>
											<input type="hidden" name="option" value="com_qazap" />
											<input type="hidden" name="task" value="product.wishlist" />
											<input type="hidden" name="return" value="<?php echo base64_encode($this->product_url) ?>"/>
											<input type="hidden" name="qzform[product_id]" value="<?php echo $child->product_id ?>" />
											<input type="hidden" name="qzform[user_id]" value="<?php echo $this->user->get('id') ?>" />
											<?php echo JHtml::_('form.token'); ?>
										</form>					
									</span>
									
									<!--  Show Add To Compare Button -->
									<span class="add-to-compare-wrap">
										<form class="qazap-addtocompare-form" action="<?php echo JRoute::_($this->product_url)?>" method="post">
											<button type="submit" name="submit" class="addtocompare-button btn btn-icon hasTooltip" title="<?php echo JText::_('COM_QAZAP_ADD_TO_COMPARE') ?>">
												<i class="icon-copy"></i><span class="sr-only"><?php echo JText::_('COM_QAZAP_ADD_TO_COMPARE') ?></span>
											</button>
											<input type="hidden" name="option" value="com_qazap"/>
											<input type="hidden" name="task" value="compare.add"/>
											<input type="hidden" name="return" value="<?php echo base64_encode($this->product_url) ?>" />
											<input type="hidden" name="product_id" value="<?php echo $child->product_id ?>" />
											<input type="hidden" name="product_name" value="<?php echo base64_encode($child->product_name) ?>" />
										</form>					
									</span>									
								</div>							
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>