<?php
/**
 * default_seller.php
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
<div class="shop-page-seller" itemprop="seller" itemscope itemtype="http://schema.org/Organization">
	<div class="seller-top">
		<div class="row-fluid">
			<div class="span3">
				<div class="seller-image-cont">				
					<?php echo QZImages::displaySingleImage($this->shop->image) ?>
				</div>								
			</div>
			<div class="span9">
				<div class="shop-list-item-details">
					<h2 itemprop="name" class="shop-title">
						<?php echo $this->escape($this->shop->shop_name) ?>		
					</h2>
					<div class="rating">
						<span><?php echo JText::_('COM_QAZAP_RATING') ?>:&nbsp;</span>
						<span class="qazap-shop-list-item-rating js-rating" data-score="<?php echo (float) $this->shop->average_rating ?>" ></span>
						<span class="hasTooltip review-count" title="<?php echo JText::sprintf('COM_QAZAP_REVIEW_COUNT', $this->shop->rating_count) ?>">(<?php echo (int) $this->shop->rating_count ?>)</span>
					</div>									
					<div class="product-count">
						<span><?php echo JText::_('COM_QAZAP_PRODUCT_COUNT') ?>:&nbsp;</span>
						<span><?php echo (int) $this->shop->product_count ?></span>
					</div>
					<div class="joining-date">
						<span><?php echo JText::_('COM_QAZAP_VENDOR_JOINING_DATE') ?>:&nbsp;</span>
						<span><?php echo JHtml::_('date', $this->shop->created_time, 'M Y')	?></span>
					</div>
					<div class="show-products">
						<a href="<?php echo JRoute::_(QazapHelperRoute::getCategoryRoute(0, 0, 0, '', '', $filters = array('vendor_id'=>$this->shop->id))) ?>" class="btn btn-primary"><?php echo JText::_('COM_QAZAP_SELLER_SEE_ALL_PRODUCTS') ?></a>
					</div>													
				</div>								
			</div>							
		</div>		
	</div>
	<?php if(!empty($this->shop->shop_description)) : ?>
	<div class="seller-bottom">
		<?php echo $this->shop->shop_description ?>
	</div>
	<?php endif; ?>
</div>