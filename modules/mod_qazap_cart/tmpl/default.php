<?php
/**
 * default.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Cart Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

JHTML::_('bootstrap.tooltip');
$uri = JUri::base(true);
$document->addStyleSheet($uri . '/modules/mod_qazap_cart/assets/css/module.css');
$document->addScript($uri . '/modules/mod_qazap_cart/assets/js/module.js');
$document->addScriptDeclaration("
if(typeof window.qzuri === 'undefined' || typeof window.qzuri === undefined) {
	window.qzuri = '{$uri}/index.php';
}
window.cart_module_itemid = $menuid;
window.cart_module_class = 'qazap-cart-module{$moduleclass_sfx}';
");
?>
<div class="qazap-cart-module<?php echo $moduleclass_sfx; ?>">
  <?php if($isEmpty) : ?>
  	<div class="cart-module-empty"><?php echo JText::_('MOD_QAZAP_CART_IS_EMPTY') ?></div>
  <?php else : ?>
  	<div class="qazap-cart-module-inner">  		
  		<div class="cart-module-total"><?php echo JText::sprintf('MOD_QAZAP_CART_TOTAL', '<strong>' . QZHelper::currencyDisplay($cart->cart_total) . '</strong>') ?></div>
  		<div class="cart-module-product-count"><?php echo JText::sprintf('MOD_QAZAP_CART_PRODUCT_COUNT', $count) ?></div>
  		<div class="cart-module-checkout clearfix">
  			<a class="btn btn-small cart-module-showproduct" href="#" title="<?php echo JText::_('MOD_QAZAP_CART_SHOW_PRODUCTS') ?>">
  				<span class="onhide"><?php echo JText::_('MOD_QAZAP_CART_SHOW_PRODUCTS') ?></span>
  				<span class="onshow hide"><?php echo JText::_('MOD_QAZAP_CART_HIDE_PRODUCTS') ?></span>
  			</a>  		
  			<a class="btn btn-small btn-primary pull-right" href="<?php echo JRoute::_($cart_url) ?>" title="<?php echo JText::_('MOD_QAZAP_CART_CHECKOUT') ?>">
  				<span><?php echo JText::_('MOD_QAZAP_CART_CHECKOUT') ?></span>
  			</a>
  		</div>
  		<div class="cart-module-products hide">
  			<h4><?php echo JText::_('MOD_QAZAP_CART_PRODUCTS_IN_CART') ?></h4>
  			<div class="cart-module-products-inner">
  				<?php foreach($products as $product) : ?>
  					<?php $product_url = QazapHelperRoute::getProductRoute($product->slug, $product->category_id); ?>
  					<div class="cart-module-product">
  						<div class="product-inner clearfix">
								<div class="product-image">
									<a href="<?php echo JRoute::_($product_url);?>" title="<?php echo htmlspecialchars($product->product_name) ?>">
										<?php echo QZImages::displaySingleImage($product->images) ?>
									</a>  									
								</div>
								<div class="product-details">
									<div class="inner">
										<a href="<?php echo JRoute::_($product_url) ?>" title="<?php echo htmlspecialchars($product->product_name) ?>">
											<?php echo htmlspecialchars($product->product_name) ?>
										</a>
										<?php if($varients = $product->getVarients()) : ?>
											<div class="product-varients"><?php echo $varients ?></div>
										<?php endif; ?>
										<div class="quantity-price">
											<?php echo $product->product_quantity . ' x ' . QZHelper::currencyDisplay($product->product_salesprice); ?>
										</div>										
									</div>									
								</div>
  						</div>
  					</div>
  				<?php endforeach; ?>
  			</div>
  		</div>
  	</div>
  <?php endif; ?>  
</div>
