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
$js = array('jquery.fancybox-1.3.4.pack.js', 'cloud-zoom.1.0.2.modified.js', 'jquery.raty.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
$params = $this->category->params;

$filters = array();
$price_range = '';
if(!empty($this->filters)) 
{
	if(!empty($this->filters->brands->selected))
	{
		foreach($this->filters->brands->selected as $brand)
		{
			$filters[] = $brand->brand_name;
		}
	}

	if(!empty($this->filters->attributes))
	{
		foreach($this->filters->attributes as $type)
		{
			if(!empty($type->data))
			{
				foreach($type->data as $attribs)
				{
					if($attribs->checked)
					{
						$filters[] = JText::_($type->title) . ': ' . $attribs->display;
					}
				}				
			}			
		}
	}
	
	if(!empty($this->filters->prices))
	{
		if(!empty($this->filters->prices->active_min_price) || !empty($this->filters->prices->active_max_price))
		{
			if(empty($this->filters->prices->active_min_price))
			{
				$min_price = QZHelper::currencyDisplay($this->filters->prices->min_price, true, true);
			}
			else
			{
				$min_price =  QZHelper::currencyDisplay($this->filters->prices->active_min_price, true, true);
			}
			
			if(empty($this->filters->prices->active_max_price))
			{
				$max_price =  QZHelper::currencyDisplay($this->filters->prices->max_price, true, true);
			}
			else
			{
				$max_price =  QZHelper::currencyDisplay($this->filters->prices->active_max_price, true, true);
			}	
			
			$price_range = JText::sprintf('COM_QAZAP_CATEGORY_FILTERED_PRICE', '<strong>' . $min_price . '</strong>', '<strong>' .$max_price . '</strong>');		
		}
	}	
	
}

?>
<section class="category-page<?php echo $this->pageclass_sfx ?>">

	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>
	
	<?php if($this->isSearch) : ?>
		<?php echo $this->loadTemplate('search'); ?>
	<?php endif; ?>
	
	<?php echo $this->loadTemplate('category'); ?>
	
	<?php if($params->get('display_subcategories', 1) && !$this->isFiltered && !$this->isSearch) : ?>
		<?php echo $this->loadTemplate('subcategories'); ?>
	<?php endif; ?>
	
	<?php if(!empty($filters) || !empty($price_range) || $this->isSearch) : ?>
		<div class="product-list-filters">
			<?php if(!empty($filters)) : ?>
				<p class="filtered-by"><?php echo JText::sprintf('COM_QAZAP_CATEGORY_FILTERED_BY', '<strong>' . implode(', ', $filters) . '</strong>') ?></p>
			<?php endif; ?>
			
			<?php if(!empty($price_range)) : ?>
				<p class="price-range"><?php echo $price_range ?></p>
			<?php endif; ?>	
			
			<?php if($this->isSearch)	: ?>
				<p class="search-word"><?php echo JText::sprintf('COM_QAZAP_SEARCH_RESULTS_FOR', '<strong>' .$this->escape($this->searchWord) . '</strong>') ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	
	<?php echo $this->loadTemplate('products'); ?>
	
</section>
