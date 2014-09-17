<?php
/**
 * default_relatedcategories.php
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
$params  = $this->item->params;
// Return if no related categories are available
if(!$this->item->related_categories)
{
	return;
}
$iCol = 1;
$iCat = 1;
$categories_per_row = 3;
$width = 'span'.floor ( 12 / $categories_per_row );
$total_categories = count($this->item->related_categories);

?>
<div class="qazap-related-categories-wrap">
	<h4><?php echo JText::_('COM_QAZAP_RELATED_CATEGORIES') ?></h4>
	<div class="qazap-related-categories qazap-category-list">
		<?php foreach($this->item->related_categories as $category) : ?>
			<?php $category_url = QazapHelperRoute::getCategoryRoute($category->category_id); ?>
			<?php if($iCol == 1) : ?>
			<div class="row-fluid">
			<?php endif; ?>
				<div class="related-category qazap-category-list-item <?php echo $width ?>">
					<div class="category-list-item-inner">
						<div class="image-cont">
							<a href="<?php echo JRoute::_($category_url);?>" title="<?php echo $category->title ?>">
								<?php echo QZImages::displaySingleImage($category->images) ?>
							</a>
						</div>
						<h3 class="category-list-item-title">
							<a href="<?php echo JRoute::_($category_url);?>" title="<?php echo $category->title ?>">
								<span><?php echo $category->title ?></span>
							</a>
							<?php if($params->get('show_subcategory_num_products', 1)) : ?>
							<span class="badge badge-info tip hasTooltip" title="<?php echo JText::_('COM_QAZAP_NUM_PRODUCTS') ?>">
								<?php echo $category->numitems ?>
							</span>
							<?php endif; ?>
						</h3>
					</div>
				</div>
			<?php if ($iCol == $categories_per_row || $iCat == $total_categories) {?>
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
	</div>
</div>