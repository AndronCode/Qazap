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
defined('_JEXEC') or die;

$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js', 'jquery.raty.js');
QZApp::loadCSS($css);
QZApp::loadJS($js);
$shop_url = QazapHelperRoute::getVendorRoute($this->shop->id);

if(!empty($this->productLists)) :
	foreach($this->productLists as $type => $list) :
		if(!empty($list->products)) : ?>
		<section class="<?php echo $type ?>-products-view qazap-product-list">			
			<h3 class="front-page-titles"><?php echo JText::_($list->title) ?></h3>
			<?php
			$iCol = 1;
			$iProduct = 1;
			$products_per_row = (int) $this->params->get($type .'_products_per_row_shop', 4);
			$products_per_row = ($products_per_row > 0) ? $products_per_row : 1;
			$width = 'span' . floor ( 12 / $products_per_row );
			$total_products = count($list->products);

			foreach($list->products as $product) : ?>
			<?php $product_url = QazapHelperRoute::getProductRoute($product->slug, $product->category_id); ?>
			<?php if($iCol == 1) : ?>
			<div class="row-fluid">
			<?php endif; ?>
				<div class="qazap-product-list-item <?php echo $width ?>">
					<?php echo JHtml::_('qzproduct.display', $product, $this->params, $shop_url); ?>		
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
		</section>
		<?php	
		endif;
	endforeach;
endif; ?>
