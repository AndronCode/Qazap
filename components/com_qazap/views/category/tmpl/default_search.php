<?php
/**
 * @package     Qazap.Site
 * @subpackage  mod_qazap_categories
 *
 * @copyright   Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$lang			= JFactory::getlanguage();
$maxlength		= $lang->getUpperLimitSearchWord();
$params = $this->category->params;
?>
<div class="product-list-search">
	<form action="<?php echo JRoute::_('index.php');?>" method="post" class="form-vertical">
		<div class="btn-toolbar">
			<div class="btn-group pull-left">
				<input name="searchword" id="mod-qazap-search-searchword" maxlength="<?php echo $maxlength ?>"  class="inputbox" type="text" size="20" value="<?php echo $this->searchWord ?>"  onblur="if (this.value==\'\') this.value=\'<?php echo JText::_('COM_QAZAP_SEARCH_SEARCHBOX_TEXT') ?>\';" onfocus="if (this.value==\'<?php echo JText::_('COM_QAZAP_SEARCH_SEARCHBOX_TEXT') ?>\') this.value=\'\';" placeholder="<?php echo JText::_('COM_QAZAP_SEARCH_SEARCHBOX_TEXT') ?>"/>
			</div>
			<div class="btn-group pull-left">
			<button name="Search" onclick="this.form.submit()" class="btn hasTooltip" title="<?php echo JText::_('COM_QAZAP_SEARCH_LABEL') ?>">
				<span class="icon-search"></span>
			</button>
			</div>
			<div class="clearfix"></div>
		</div>	
		<?php if($params->get('show_categories', 1)) : ?>
		<fieldset>
			<legend><?php echo JText::_('COM_QAZAP_SEARCH_CATEGORIES') ?></legend>
			<div class="controls">				
				<?php echo $this->getCategoryList() ?>						
			</div>				
		</fieldset>		
		<?php endif; ?>
		<fieldset>
			<legend>Phrase</legend>
			<?php echo $this->searchphrase;?>
		</fieldset>
		
		<input type="hidden" name="task" value="category.search" />
		<input type="hidden" name="option" value="com_qazap" />
	</form>
</div>