<?php
/**
 * default_relatedproducts.php
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

// Use the global params here but not item/product params.
$params  = $this->params;
// Return if no related products are available
if(!$this->item->related_products)
{
	return;
}
$iCol = 1;
$iProduct = 1;
$products_per_row = (int) $params->get('products_per_row', 3);
$products_per_row = !empty($products_per_row) ? $products_per_row : 3;
$width = 'span' . floor (12 / $products_per_row);
$total_products = count($this->item->related_products);
?>
<div class="qazap-related-products-wrap">
	<h4><?php echo JText::_('COM_QAZAP_RELATED_PRODUCTS') ?></h4>
	<div class="qazap-releated-products">
		<?php foreach($this->item->related_products as $product) : ?>
			<?php if($iCol == 1) : ?>
			<div class="row-fluid">
			<?php endif; ?>
				<div class="qazap-product-list-item related-product <?php echo $width ?>">
					<?php echo JHtml::_('qzproduct.display', $product, $params, $this->product_url); ?>
				</div>
			<?php if ($iCol == $products_per_row || $iProduct == $total_products) { ?>
	   	</div> <!-- end of row -->
			<?php
	      $iCol = 1;
	   	} 
			else 
			{
				$iCol ++;
			}
			$iProduct ++;
			?>	
		<?php endforeach; ?>
	</div>
</div>