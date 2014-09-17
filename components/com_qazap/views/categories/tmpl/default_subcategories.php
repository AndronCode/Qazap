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

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
$lang	= JFactory::getLanguage();

if (count($this->items[$this->parent->category_id]) > 0 && $this->maxLevelcat != 0) 
{
	foreach($this->items[$this->parent->category_id] as $id => $item) 
	{
		if ($this->params->get('show_empty_categories_cat', 1) || $item->numitems || count($item->getChildren())) 
		{
			$category_url = QazapHelperRoute::getCategoryRoute($item);
			?>				
			  <li>
			    <h4 class="category-tree-subcat-title">
						<a href="<?php echo JRoute::_($category_url) ?>" title="<?php echo $this->escape($item->title) ?>"><?php echo $this->escape($item->title) ?></a>
						<?php if ($this->params->get('show_cat_num_products_cat', 1)) : ?>
							<span class="badge badge-info tip" title="<?php echo JHtml::tooltipText('COM_QAZAP_NUM_PRODUCTS'); ?>"><?php echo $item->numitems; ?></span>
						<?php endif; ?>										
					</h4>
					<?php if (count($item->getChildren()) > 0) : ?>
			    <ul class="children-cats">
						<?php
						$this->items[$item->category_id] = $item->getChildren();
						$this->parent = $item;
						$this->maxLevelcat--;
						echo $this->loadTemplate('subcategories');
						$this->parent = $item->getParent();
						$this->maxLevelcat++;
						?>
			    </ul>
			    <?php endif; ?>
			  </li>
		<?php
		}
	}
} ?>		
