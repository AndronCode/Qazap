<?php
/**
 * default_products.php
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
if(!empty($this->items))
{
	$iCol = 1;
	$iProduct = 1;
	$products_per_row = $params->get('products_per_row', 1);
	$width = 'span'.floor (12 / $products_per_row);
	$total_products = count($this->items);	
}
?>
<?php if(empty($this->items)) : ?>
	<?php if($params->get('show_no_products', 1)) : ?>
		<div class="no-products-container">
			<div class="message">
				<p><?php echo JText::_('COM_QAZAP_CATEGORY_NO_PRODUCTS_TITLE') ?></p>
				<p><?php echo JText::_('COM_QAZAP_CATEGORY_GO_BACK_TO_HOME_PAGE') ?>: <a href="<?php echo JUri::base() ?>" title="<?php echo JText::_('COM_QAZAP_GLOBAL_HOME_PAGE') ?>"><?php echo JText::_('COM_QAZAP_GLOBAL_HOME_PAGE') ?></a></p>
			</div>		
		</div>
	<?php endif; ?>
<?php else : ?>
	<?php if($this->sorter && $params->get('product_sorting')) : ?>
		<?php echo $this->loadTemplate('sorter'); ?>
	<?php endif; ?>
	<section class="category-page qazap-product-list">
		<?php foreach($this->items as $product) : ?>
			<?php if($iCol == 1) : ?>
				<div class="row-fluid">
			<?php endif; ?>
				<div class="qazap-product-list-item <?php echo $width ?>">
					<?php echo JHtml::_('qzproduct.display', $product, $params, $this->url); ?>
				</div>
			<?php if ($iCol == $products_per_row || $iProduct == $total_products) {?>
			</div>
			<?php
				$iCol = 1;
			} else {
				$iCol ++;
			}
			$iProduct ++;
			?>	
		<?php endforeach; ?>
		<div class="pagination">
			<?php if ($params->def('show_pagination_results', 1)) : ?>
			<p class="counter pull-right">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
			<?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	</section>
<?php endif; ?>