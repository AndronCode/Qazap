<?php
/**
 * default_subcategories.php
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

$params = $this->category->params;

if(!$total_children = count($this->children[$this->category->category_id]))
{
	return;
}

$iCol = 1;
$iCat = 1;
$children_per_row = $params->get('subcategories_per_row', 3);
$width = 'span' . floor ( 12 / $children_per_row );
?>
<div class="qazap-sub-children-wrap">
	<div class="qazap-sub-children qazap-category-list">
		<?php foreach($this->children[$this->category->category_id] as $child) : ?>
			<?php if ($params->get('show_empty_subcategories') || $child->getNumItems(true) || count($child->getChildren())) : ?>
				<?php $child_url = QazapHelperRoute::getCategoryRoute($child); ?>
				<?php if($iCol == 1) : ?>
				<div class="row-fluid">
				<?php endif; ?>
					<div class="sub-category qazap-category-list-item <?php echo $width ?>">
						<div class="category-list-item-inner">
							<?php if($params->get('show_subcategory_image', 1)) : ?>
								<div class="image-cont">
									<a href="<?php echo JRoute::_($child_url);?>" title="<?php echo $this->escape($child->title) ?>">
										<?php echo QZImages::displaySingleImage($child->getImages()) ?>
									</a>
								</div>
							<?php endif; ?>
							<?php if($params->get('show_subcategory_title', 1) || $params->get('show_subcategory_num_products', 1)) : ?>
								<h3 class="category-list-item-title">
									<?php if($params->get('show_subcategory_title', 1)) : ?>
										<a href="<?php echo JRoute::_($child_url);?>" title="<?php echo $this->escape($child->title) ?>">
											<span><?php echo $this->escape($child->title) ?></span>
										</a>
									<?php endif; ?>
									<?php if($params->get('show_subcategory_num_products', 1)) : ?>
									<span class="badge badge-info tip hasTooltip" title="<?php echo JText::_('COM_QAZAP_NUM_PRODUCTS') ?>">
										<?php echo $child->numitems ?>
									</span>
									<?php endif; ?>								
								</h3>
							<?php endif; ?>
						</div>
					</div>
				<?php if ($iCol == $children_per_row || $iCat == $total_children) {?>
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
	  	<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>