<?php
/**
 * default_items.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Categories Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

foreach ($list as $item) :
  $url = QazapHelperRoute::getCategoryRoute($item);  
  $hasChildren = ($params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0) || ($params->get('maxlevel') >= ($item->level - $startLevel))) && count($item->getChildren()));
  $active = ($_SERVER['PHP_SELF'] == JRoute::_($url)) ? 'active' : '';
  $class = $active . ($hasChildren ? ' parent' : '');
  ?>
	<li <?php echo $class ? 'class="' . $class . '"' : ''; ?>>
    <?php $levelup = $item->level - $startLevel - 1; ?>
		<h<?php echo $params->get('item_heading') + $levelup; ?> class="category-title">
		<a href="<?php echo JRoute::_(QazapHelperRoute::getCategoryRoute($item)); ?>"><?php echo $item->title;?></a>
		<?php if($params->get('numitems')): ?>
			<span class="muted hasTooltip" title="<?php echo JText::_('MOD_QAZAP_CATEGORIES_PRODUCT_COUNT') ?>">(<?php echo $item->numitems; ?>)</span>
		<?php endif; ?>    
		</h<?php echo $params->get('item_heading') + $levelup; ?>>

		<?php
		if ($hasChildren)
		{
      echo '<span class="category-toggler ' . ($active ? 'active' : '')  . '"></span>';
			echo '<ul class="category-children ' . ($active ? 'show' : 'hide')  . '">';
			$temp = $list;
			$list = $item->getChildren();
			require JModuleHelper::getLayoutPath('mod_qazap_categories', $params->get('layout', 'default') . '_items');
			$list = $temp;
			echo '</ul>';
		}
		?>
 </li>
<?php endforeach; ?>
