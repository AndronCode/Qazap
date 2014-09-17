<?php
/*{qazapcopyright}*/
defined('_JEXEC') or die;

$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js', 'jquery.raty.js');
QZApp::loadCSS($css);
QZApp::loadJS($js);

// Set our variables for display
$noTitleFields = $this->params->get('display_no_title_fields', array('product_id'));
$maxNumber = (int) $this->params->get('compare_product_number', 2) ? $this->params->get('compare_product_number', 2) : 1;
$width = floor(85 / $maxNumber);
$position = $this->params->get('position_of_addtocart_button', '');
$position = in_array($position, $this->display_fields) ? $position : '';
$continue_link = '<a href="' . $this->continue_url . '">' . JText::_('COM_QAZAP_COMPARE_GO_BACK') . '</a>';
?>
<section class="comparison-page<?php echo $this->pageclass_sfx ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<header class="qz-page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</header>
	<?php endif; ?>
	<?php if(empty($this->list)) : ?>
		<div class="empty-page-msg">
			<p><?php echo JText::sprintf('COM_QAZAP_NO_PRODUCTS_FOUND_TO_COMPARE', $continue_link); ?></p>
		</div>
	<?php else : ?>
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<form name="compare-clear-form" action="<?php echo JRoute::_(QazapHelperRoute::getCompareRoute())?>" method="post">
						<button type="submit" name="submit" class="btn">							
							<?php echo JText::_('COM_QAZAP_REMOVE_ALL_FROM_COMPARE') ?>								
						</button>
						<input type="hidden" name="option" value="com_qazap"/>
						<input type="hidden" name="task" value="compare.removeAll" />
				</form>
			</div>
		</div>
		<table class="comparison-table">
			<tbody>
			<?php foreach ($this->list as $key => $data) : ?>
				<?php if(in_array($key, $this->display_fields) && !empty($data)) : ?>		
					<?php if(($key == 'custom_fields') || ($key == 'attributes')) : ?>
						<?php foreach($data as $option) : ?>
							<tr class="<?php echo trim($key) . '-row'; ?>">
								<td class="head" width="15%">
									<?php echo JText::_($option->title) ?>
								</td>									
								<?php foreach($option->data as $optionData) : ?>
									<td class="product-data" width="<?php echo $width ?>%">
										<?php echo !empty($optionData) ? $optionData->compare_display : ''; ?>
									</td>			
								<?php endforeach; ?>
							</tr>				
						<?php endforeach; ?>		
					<?php else : ?>
						<tr class="<?php echo trim($key) . '-row'; ?>">				
							<td class="head" width="15%">
								<?php if(!in_array($key, $noTitleFields)) : ?>
									<?php echo JHtml::_('qzproductobject.name', $key); ?>
								<?php endif; ?>
							</td>
							<?php foreach($data as $product_id => $value) : 
								$product = $this->products[$product_id];
								?>
								<td class="product-data-<?php echo $key ?>" width="<?php echo $width ?>%">
									<?php if($key == 'product_id') : // Display indivial product remove button ?>
									<form name="compare-remove-form" action="<?php echo JRoute::_(QazapHelperRoute::getCompareRoute())?>" method="post">
										<button type="submit" name="submit" class="btn btn-remove hasTooltip" title="<?php echo JText::_('COM_QAZAP_REMOVE')?>">
											<span class="close-icon">&#10006;</span>
											<span class="sr-only"><?php echo JText::_('COM_QAZAP_REMOVE')?></span>											
										</button>
										<input type="hidden" name="option" value="com_qazap"/>
										<input type="hidden" name="task" value="compare.remove"/>
										<input type="hidden" name="product_id" value="<?php echo $value ?>" />
										<input type="hidden" name="product_name" value="<?php echo base64_encode($product->product_name) ?>" />
									</form>
									<?php elseif($key == 'product_name') : ?>
										<a href="<?php echo JRoute::_(QazapHelperRoute::getProductRoute($product->slug, $product->category_id)) ?>" title="<?php echo $this->escape($product->product_name) ?>">
											<?php echo $this->escape($value) ?>
										</a>
									<?php elseif($key == 'category_name') : ?>
										<a href="<?php echo JRoute::_(QazapHelperRoute::getCategoryRoute($product->category_id)) ?>" title="<?php echo $this->escape($value) ?>">
											<?php echo $this->escape($value) ?>
										</a>
									<?php elseif($key == 'manufacturer_name') : ?>
										<a href="<?php echo JRoute::_(QazapHelperRoute::getBrandRoute($product->manufacturer_id)) ?>" title="<?php echo $this->escape($value) ?>">
											<?php echo $this->escape($value) ?>
										</a>																			
									<?php elseif($key == 'shop_name') : ?>
										<a href="<?php echo JRoute::_(QazapHelperRoute::getVendorRoute($product->vendor)) ?>" title="<?php echo $this->escape($product->shop_name) ?>">
											<?php echo $this->escape($value) ?>
										</a>										
									<?php elseif($key == 'images') : ?>
										<a href="<?php echo JRoute::_(QazapHelperRoute::getProductRoute($product->slug, $product->category_id)) ?>" title="<?php echo $this->escape($product->product_name) ?>">
											<?php echo QZImages::displaySingleImage($value) ?>
										</a>							
									<?php elseif($key == 'prices') : ?>
										<?php 
										$this->prices = $value;
										echo $this->loadTemplate('price');
										?>
									<?php elseif($key == 'rating') : ?>
										<span class="qazap-compare-item-rating js-rating" data-score="<?php echo (float) $value ?>" ></span>
										<span class="hasTooltip review-count" title="<?php echo JText::sprintf('COM_QAZAP_REVIEW_COUNT', (int) $product->review_count) ?>">(<?php echo (int) $product->review_count ?>)</span>
									<?php elseif($key == 'featured') : ?>									
										<?php echo $value ? JText::_('JYES') : JText::_('JNO'); ?>
									<?php elseif(!is_array($value) && !is_object($value)) : ?>
										<?php echo $value ?>
									<?php endif; ?>
								</td>			
							<?php endforeach; ?>
						</tr>
					<?php endif; ?>
				<?php endif; ?>
				<!-- place Add to cart button -->
				<?php if($key == $position) : ?>
				<tr class="addtocart-row">
					<td class="head" width="15%"></td>
					<?php foreach($data as $product_id => $value) : ?>
						<td width="<?php echo $width ?>%"><?php echo JHtml::_('qzproduct.addtocart', $this->products[$product_id], $this->params) ?></td>
					<?php endforeach;?>			
				</tr>
				<?php endif; ?>
			<?php endforeach; ?>				
			</tbody>
		</table>
	<?php endif; ?>
</section>