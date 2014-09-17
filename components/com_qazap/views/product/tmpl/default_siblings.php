<?php
/**
 * default_siblings.php
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
<?php if($this->siblings['left'] || $this->siblings['right']) : ?>
<ul class="pager siblings">
	<?php if($this->siblings['left']) : 
		$leftUrl = QazapHelperRoute::getProductRoute($this->siblings['left']->product_id, $this->siblings['left']->category_id);
		?>
		<li class="previous">
			<a class="hasTooltip" href="<?php echo JRoute::_($leftUrl) ?>" title="<?php echo JText::_('COM_QAZAP_PREVIOUS_PRODUCT') ?>">
				&larr; <?php echo JText::_('COM_QAZAP_PREVIOUS_PRODUCT') ?>
			</a>
		</li>
	<?php endif; ?>
	<?php if($this->siblings['right']) : 
		$rightUrl = QazapHelperRoute::getProductRoute($this->siblings['right']->product_id, $this->siblings['right']->category_id);
		?>
		<li class="next">
			<a class="hasTooltip" href="<?php echo JRoute::_($rightUrl) ?>" title="<?php echo JText::_('COM_QAZAP_NEXT_PRODUCT') ?>">
				<?php echo JText::_('COM_QAZAP_NEXT_PRODUCT') ?> &rarr;
			</a>
		</li>
	<?php endif; ?>
</ul>
<?php endif; ?>