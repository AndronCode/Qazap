<?php
/**
 * default_availability.php
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

?>
<?php if($params->get('show_availablity', 1)) : ?>
<div class="product-availablity-status">
	<?php if($this->item->in_stock - $this->item->booked_order > 0 || !$params->get('enablestockcheck')) : ?>
		<?php if($image = $params->get('in_stock_image', null) ) : ?>
			<span class="product-availablity">
				<img src="<?php echo Juri::base().$image?>" alt="<?php echo JText::_('COM_QAZAP_IN_STOCK') ?>" />
			</span>
		<?php else : ?>
			<span class="product-availability-title sr-only"><?php echo JText::_('COM_QAZAP_PRODUCT_AVAILABILITY') ?>:&nbsp;</span>
			<span class="product-availablity text-success"><?php echo JText::_('COM_QAZAP_IN_STOCK') ?></span>
		<?php endif; ?>
	<?php else : ?>
		<?php if($image = $params->get('out_of_stock_image', null) ) : ?>
			<span class="product-availablity">
				<img src="<?php echo Juri::base().$image?>" alt="<?php echo JText::_('COM_QAZAP_OUT_OF_STOCK') ?>" />
			</span>
		<?php else : ?>
			<span class="product-availability-title sr-only"><?php echo JText::_('COM_QAZAP_PRODUCT_AVAILABILITY') ?>:&nbsp;</span>
			<span class="product-availablity text-error"><?php echo JText::_('COM_QAZAP_OUT_OF_STOCK') ?></span>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if($params->get('show_stock', 1)) : ?>
<div class="product-stock-status">
	<span class="product-stock-title"><?php echo JText::_('COM_QAZAP_IN_STOCK') ?>:&nbsp;</span>
	<span class="product-stock"><?php echo ($this->item->in_stock - $this->item->booked_order) ?></span>
</div>
<?php endif; ?>

