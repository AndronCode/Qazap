<?php
/**
 * default_categories.php
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
$lang	= JFactory::getLanguage();
?>
<section class="qazap-category-list">
	<h2 class="front-page-titles"><?php echo JText::_('COM_QAZAP_PRODUCTCATEGORIES') ?></h2>
	<?php if (count($this->items[$this->parent->category_id]) > 0 && $this->maxLevelcat != 0) 
	{
		$iCol = 1;
		$iCount = 1;
		$per_row = (int) $this->params->get('subcategories_per_row_cat', 1);
		$per_row = ($per_row > 0) ? $per_row : 1; 
		$width = 'span' . floor(12 / $per_row);
		$total = count($this->items[$this->parent->category_id]);
		
		foreach($this->items[$this->parent->category_id] as $id => $item) 
		{
			if ($this->params->get('show_empty_categories_cat', 1) || $item->numitems || count($item->getChildren())) 
			{
				$category_url = QazapHelperRoute::getCategoryRoute($item);
				if($iCol == 1) : ?>
					<div class="row-fluid">
				<?php endif; ?>
					<div class="<?php echo $width ?>">
						<div class="qazap-category-parent">					
							<div class="category-tree category-list-item-inner">
								<?php if ($this->params->get('show_subcat_image_cat')) : ?>
									<div class="image-cont">
										<a href="<?php echo JRoute::_($category_url) ?>">
											<?php echo QZImages::displaySingleImage($item->getImages(), array('class' => 'category-image')) ?>
										</a>
									</div>
								<?php endif; ?>
						    <h3 class="category-list-item-title">
									<a href="<?php echo JRoute::_($category_url) ?>" title="<?php echo $this->escape($item->title) ?>"><?php echo $this->escape($item->title) ?></a>
									<?php if ($this->params->get('show_cat_num_products_cat', 1)) : ?>
										<span class="badge badge-info tip hasTooltip" title="<?php echo JHtml::tooltipText('COM_QAZAP_NUM_PRODUCTS'); ?>"><?php echo $item->numitems; ?></span>
									<?php endif; ?>										
								</h3>
								<?php if (count($item->getChildren()) > 0) :
									$this->items[$item->category_id] = $item->getChildren();
									$this->parent = $item;
									$this->maxLevelcat--;
									$subcategories = $this->loadTemplate('subcategories');
									if(!empty($subcategories)) : ?>
									<div class="category-tree-body">
										<div class="inner">
									    <h3 class="category-tree-parent-title">
												<a href="<?php echo JRoute::_($category_url) ?>" title="<?php echo $this->escape($item->title) ?>"><?php echo $this->escape($item->title) ?></a>								
											</h3>
											<ul class="children-cats">
												<?php echo $subcategories ?>
											</ul>
										</div>
									</div>
									<?php
									endif;
									$this->parent = $item->getParent();
									$this->maxLevelcat++;									
								endif; ?>
							</div>					
						</div>
					</div>
				<?php if(($iCol == $per_row) || $iCount == $total) : ?>		
					</div>
				<?php
					$iCol = 1;
				else : 
					$iCol++;
				endif;			
				$iCount++;
			}
		}
	} ?>
</section>
