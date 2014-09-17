<?php
/**
 * submenu.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('JPATH_BASE') or die;
$xml = JFactory::getXML(JPATH_COMPONENT_ADMINISTRATOR . '/qazap.xml');
$version = (string) $xml->version;
?>
<div id="qazap-sidebar">
	<div class="qazap-sidebar-logo">
		<a href="<?php echo JRoute::_('index.php?option=com_qazap', false) ?>">
			<img src="<?php echo JUri::base(true) ?>/components/com_qazap/assets/images/qazap-logo.jpg" width="180" height="61" />
		</a>
	</div>
	<div class="sidebar-nav" id="qazap-sidemenu">
		<?php if ($displayData->displayMenu) : 
			foreach ($displayData->list as $key => $item) : 
			$hasChild = count($item['items']); ?>			
			<div class="qzsidemenu-group">
        <?php if($key == 'home') : ?>
        <h2 class="qzsidemenu-header home<?php echo $item['active'] ? ' active' : ''; ?>"><a href="<?php echo !empty($item['link']) ? JFilterOutput::ampReplace($item['link']) : '#'; ?>" class="qazap-home"><?php echo $item['name'] ?></a></h2>
        <?php else : ?>
				<h2 class="qzsidemenu-header<?php echo $item['active'] ? ' active' : ''; ?>"><a href="#"><?php echo $item['name'] ?><div class="qzsidebar-arrow <?php echo $item['active'] ? 'qzarrow-down' : 'qzarrow-left'; ?>"></div></a></h2>
        <?php endif; ?>
				<?php if($hasChild) : ?>
				<div class="qzsidemenu-items <?php echo $item['active'] ? '' : 'hide'; ?>">
					<ul class="qzsidemenu-items-ul nav nav-list">
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
				</div>
				<?php endif; ?>
      </div>			
			<?php endforeach; ?>
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
	<div id="qazap-version">
		<span>Qazap <?php echo $version ?></span>
	</div>	
</div>
