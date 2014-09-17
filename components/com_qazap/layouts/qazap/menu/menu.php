<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

//qzdump($displayData);exit;
?>
<div class="qazap-menu">
	<div class="qazap-menu-inner">
		<ul class="qzmenu-group horizontal-menu">
			<?php 
			if ($displayData->displayMenu) : 
			foreach ($displayData->list as $item) :
			$parentClass = $item['active'] ? 'class="parent active"' : 'class="parent"' ;
			$hasChild = count($item['items']); ?>			
			<li <?php echo $parentClass ?>>
				<a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a>
				<?php if($hasChild) : ?>
				<ul class="qzmenu-items-ul horizontal-menu">
					<?php foreach($item['items'] as $menu) : 
					$menuClass = $menu['active'] ? ' class="active"' : '' ;?>
					<li<?php echo $menuClass ?>>
						<?php if($menu['link']) : ?>
						<a href="<?php echo JFilterOutput::ampReplace($menu['link']); ?>">
							<i class="<?php echo $menu['icon'] ?>"></i> <span><?php echo $menu['name'] ?></<span>
						</a>
						<?php else: ?>
						<span>
							<i class="<?php echo $menu['icon'] ?>"></i> <?php echo $menu['name'] ?>
						</span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</li>      		
			<?php endforeach; ?>
		</ul>	
		<?php endif; ?>
		<?php if ($displayData->displayMenu && $displayData->displayFilters) : ?>
		<hr />
		<?php endif; ?>
		<?php if ($displayData->displayFilters) : ?>
		<div class="filter-select hidden-phone">
			<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
			<?php foreach ($displayData->filters as $filter) : ?>
				<label for="<?php echo $filter['name']; ?>" class="element-invisible"><?php echo $filter['label']; ?></label>
				<select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" class="span12 small" onchange="this.form.submit()">
					<?php if (!$filter['noDefault']) : ?>
						<option value=""><?php echo $filter['label']; ?></option>
					<?php endif; ?>
					<?php echo $filter['options']; ?>
				</select>
				<hr class="hr-condensed" />
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</div>
