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
$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js', 'jquery.raty.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
$iCol = 1;
$iCat = 1;
$per_row = $this->params->get('shops_per_row', 2);
$width = 'span' . floor ( 12 / $per_row );
$count = count($this->shops);
?>
<div class="shops-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="qz-page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>
	
	<section class="qazap-shop-list">
		<?php if (empty($this->shops)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_QAZAP_NO_SHOPS_FOUND'); ?>
		</div>
		<?php else : ?> 	
		<?php foreach($this->shops as $shop) : ?>
			<?php $url = QazapHelperRoute::getVendorRoute($shop); ?>
			<?php if($iCol == 1) : ?>
			<div class="row-fluid qazap-shop-list">
			<?php endif; ?>
				<div class="shop qazap-shop-list-item <?php echo $width ?>">
					<div class="shop-list-item-inner">
						<div class="row-fluid">
							<div class="span5">
								<div class="image-cont">
									<a href="<?php echo JRoute::_($url);?>" title="<?php echo $this->escape($shop->shop_name) ?>">
										<?php echo QZImages::displaySingleImage($shop->image) ?>
									</a>
								</div>								
							</div>
							<div class="span7">
								<div class="shop-list-item-details">
									<h3 class="shop-list-item-title">
										<a href="<?php echo JRoute::_($url);?>" title="<?php echo $this->escape($shop->shop_name) ?>">
											<span><?php echo $this->escape($shop->shop_name) ?></span>
										</a>							
									</h3>
									<div class="rating">
										<span><?php echo JText::_('COM_QAZAP_RATING') ?>:&nbsp;</span>
										<span class="qazap-shop-list-item-rating js-rating" data-score="<?php echo (float) $shop->average_rating ?>" ></span>
										<span class="hasTooltip review-count" title="<?php echo JText::sprintf('COM_QAZAP_REVIEW_COUNT', $shop->rating_count) ?>">(<?php echo (int) $shop->rating_count ?>)</span>
									</div>									
									<div class="product-count">
										<span><?php echo JText::_('COM_QAZAP_PRODUCT_COUNT') ?>:&nbsp;</span>
										<span><?php echo (int) $shop->product_count ?></span>
									</div>
									<div class="joining-date">
										<span><?php echo JText::_('COM_QAZAP_VENDOR_JOINING_DATE') ?>:&nbsp;</span>
										<span><?php echo JHtml::_('date', $shop->created_time, 'M Y')	?></span>
									</div>														
								</div>								
							</div>							
						</div>
					</div>
				</div>
			<?php if ($iCol == $per_row || $iCat == $count) {?>
			</div> <!-- end of row -->
			<?php
			$iCol = 1;
			} 
			else 
			{
			$iCol ++;
			}
			$iCat ++;
			?>					
		<?php endforeach; ?>
		<div class="pagination">
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter pull-right">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
			<?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>		
		<?php endif; ?>
	</section>
</div>
