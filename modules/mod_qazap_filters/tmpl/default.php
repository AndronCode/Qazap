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
 * @subpackage Qazap Filters Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;
JHtml::_('jquery.framework');
JHtml::_('jquery.ui'); 

if(!empty($prices)) 
{
	$doc->addStyleSheet('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');	
	$doc->addScript(JUri::base(true) . '/modules/mod_qazap_filters/assets/js/jquery.ui.slider.js');
	$doc->addScript(JUri::base(true) . '/modules/mod_qazap_filters/assets/js/jquery.formatCurrency-1.4.0.js');

	$currency = QZHelper::getCurrencyInfo();
	$currencyFormat = str_replace('{value}', '%n', str_replace('{symbol}', '%s', $currency->format));	
	$active_min_price = (float) !empty($prices->active_min_price) ? $prices->active_min_price : $prices->min_price;
	$active_max_price = (float) !empty($prices->active_max_price) ? $prices->active_max_price : $prices->max_price;
	$doc->addScriptDeclaration("
	if (typeof jQ === 'undefined') {
	    var jQ = jQuery.noConflict();
	}
	function positionPriceCounter() {
	    var leftOffset = jQ('#price-range-slider').find('.ui-slider-range').offset().left - jQ('#price-range-slider').offset().left;
	    var sliderWidth = jQ('#price-range-slider').find('.ui-slider-range').outerWidth();
	    var ownWidth = jQ('#slider-cont').find('.info').outerWidth();
	    var leftDistance = ((sliderWidth - ownWidth) / 2) + leftOffset;
	    jQ('#slider-cont').find('.info').css('left', leftDistance);
	}
	jQ(document).ready(function () {
	        var currencyFormat = {
	            symbol: '{$currency->currency_symbol}',
	            positiveFormat: '{$currencyFormat}',
	            negativeFormat: '-{$currencyFormat}',
	            decimalSymbol: '{$currency->decimals_symbol}',
	            digitGroupSymbol: '{$currency->thousand_separator}',
	            groupDigits: true,
	            roundToDecimalPlace: 0
	        }
	        jQ('#price-range-slider').slider({
	                range: true,
	                min: $prices->min_price,
	                max: $prices->max_price,
	                values: [ $active_min_price, $active_max_price ],
	                slide: function( event, ui ) {
	                    jQ('#slider-cont').find('.min .price-label').text((ui.values[0] * $currency->exchange_rate)).formatCurrency(currencyFormat);
	                    jQ('#slider-cont').find('.max .price-label').text((ui.values[1] * $currency->exchange_rate)).formatCurrency(currencyFormat);
	                    positionPriceCounter();
	                },
	                change: function( event, ui ) {
	                    jQ('#slider-cont').find('.min .input').val(ui.values[0]);
	                    jQ('#slider-cont').find('.max .input').val(ui.values[1]);
	                    jQ('#qazap-filter-form').submit();
	                },
	                stop: function( event, ui ) {
	                    jQ('#slider-cont').find('.min .input').val(ui.values[0]);
	                    jQ('#slider-cont').find('.max .input').val(ui.values[1]);
	                    jQ('#qazap-filter-form').submit();
	                }
	            });
	        positionPriceCounter();
	    });
	");	
}
$doc->addStyleSheet(JUri::base(true) . '/modules/mod_qazap_filters/assets/css/module.css');
?>
<div class="mod-qazap-filters<?php echo $moduleclass_sfx ?>">
	<?php if ($headerText) : ?>
	<div class="pretext"><p><?php echo $headerText; ?></p></div>
	<?php endif; ?>

	<form name="qazap-filter" id="qazap-filter-form" method="post" action="<?php echo JRoute::_($action_url) ?>">
	
		<!-- Display Brand Filter -->
		<?php if(!empty($brands) && $params->get('show_brand_filter', 1)) : 
			$options = array();
			$filtered = false;
			?>
			<div class="qazap-filter-group">
				<h3><?php echo JText::_('MOD_QAZAP_FILTERS_BRANDS') ?></h3>
				<?php foreach($brands->data as $brand) : 
						$disabled = empty($brand->product_count) ? true : false;
						$label = (string) $brand->brand_name . '<span class="filter-product-count">(' . $brand->product_count . ')</span>';
						$tmp = JHtml::_('select.option', (int) $brand->id, $label, 'value', 'text', $disabled);
						// Set some option attributes.
						$tmp->class = '';
						$tmp->checked = $brand->checked;
						if(!$filtered)
						{
							$filtered = $brand->checked;
						}
						// Set some JavaScript option attributes.
						$tmp->onclick = '';
						$tmp->onchange = 'this.form.submit();';
						// Add the option object to the result set.
						$options[] = $tmp;				
				endforeach; 
				echo JHtml::_('qzselect.checklist', $options, $brands->field_name, '', 'value', 'text', '', $brands->field_id.'-'); ?>
				<?php if($filtered) : ?>
				<div class="qazap-clear-filter-cont">
					<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=category.clearfilter&filter=manufacturers&category_id=' . (int) $category_id) ?>"><?php echo JText::_('MOD_QAZAP_FILTERS_CLEAR_BRANDS') ?></a>
				</div>
				<?php endif; ?>					
			</div>		
		<?php endif; ?>		
			
		<!-- Display Attribute Filters -->
		<?php if(!empty($attribute_groups) && $params->get('show_attribute_filter', 1)) : 
			$filtered = false;
			?>
			<?php foreach($attribute_groups as $group) : ?>
				<div class="qazap-filter-group">
				<h3><?php echo JText::_($group->title) ?></h3>
				<?php if(!empty($group->data)) : 
					$options = array();
					foreach($group->data as $attr) : 
						$disabled = empty($attr->product_count) ? true : false;
						$label = (string) $attr->display . '<span class="filter-product-count">(' . $attr->product_count . ')</span>';
						$tmp = JHtml::_('select.option', (int) $attr->attribute_id, $label, 'value', 'text', $disabled);
						// Set some option attributes.
						$tmp->class = '';
						$tmp->checked = $attr->checked;
						if(!$filtered)
						{
							$filtered = $attr->checked;
						}
						// Set some JavaScript option attributes.
						$tmp->onclick = '';
						$tmp->onchange = 'this.form.submit();';
						// Add the option object to the result set.
						$options[] = $tmp;												
					endforeach;
					echo JHtml::_('qzselect.checklist', $options, $group->field_name, '', 'value', 'text', '', $group->field_id.'-'); ?>
				<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<?php if($filtered) : ?>
			<div class="qazap-clear-filter-cont">
				<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=category.clearfilter&filter=attributes&category_id=' . (int) $category_id) ?>"><?php echo JText::_('MOD_QAZAP_FILTERS_CLEAR_ATTRIBUTES') ?></a>
			</div>
			<?php endif; ?>			
		<?php endif; ?>

		<!-- Display Price Filter -->
		<?php if(!empty($prices) && $params->get('show_price_filter', 1)) : ?>
			<div class="qazap-filter-group">
				<h3><?php echo JText::_('MOD_QAZAP_FILTERS_PRICE') ?></h3>
				<div id="slider-cont">
					<div class="info">
						<div class="val"><?php echo JText::sprintf('MOD_QAZAP_FILTERS_NUM_ITEMS', $prices->product_count) ?></div>
						<div class="arrow"></div>
					</div>
					<div class="slider-range-cont">
						<div id="price-range-slider"></div>
					</div>
					<div class="price-range-label">
						<div class="min">
							<span class="price-label"><?php echo QZHelper::currencyDisplay($active_min_price, true, true) ?></span>
							<input type="hidden" class="input" name="<?php echo $prices->field_name['min_price'] ?>" value="<?php echo $active_min_price?>" />
							<input type="hidden" name="<?php echo $prices->field_name['min_price_unfiltered'] ?>" value="<?php echo $prices->min_price ?>" />
						</div>
						<div class="max">
							<span class="price-label"><?php echo QZHelper::currencyDisplay($active_max_price, true, true) ?></span>
							<input type="hidden" class="input" name="<?php echo $prices->field_name['max_price'] ?>" value="<?php echo $active_max_price?>" />
							<input type="hidden" name="<?php echo $prices->field_name['max_price_unfiltered'] ?>" value="<?php echo $prices->max_price ?>" />
						</div>
					</div>
				</div> 
				<?php if(($active_min_price != $prices->min_price) || ($active_max_price != $prices->max_price)) : ?>
				<div class="qazap-clear-filter-cont">
					<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=category.clearfilter&filter=prices&category_id=' . (int) $category_id) ?>"><?php echo JText::_('MOD_QAZAP_FILTERS_CLEAR_PRICE') ?></a>
				</div>
				<?php endif; ?>
			</div>		
		<?php endif; ?>			
		
		<input type="hidden" name="option" value="com_qazap" />
		<input type="hidden" name="task" value="category.filter" />	
	</form>

	<?php if ($footerText) : ?>
	<div class="posttext"><p><?php echo $footerText; ?></p></div>
	<?php endif; ?>
</div>
