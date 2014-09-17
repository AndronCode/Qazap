<?php
/**
 * edit.php
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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
$css = array('smoothness/jquery-ui-1.10.4.custom.min.css', 'jquery.fancybox-1.3.4.css', 'qazap.css');
$js = array('jquery-ui-1.10.4.custom.min.js', 'jquery.fancybox-1.3.4.pack.js', 'jquery.easing.1.3.min.js', 'spin.min.js', 'qazap.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'seller.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{			
			Joomla.submitform(task);
		}
	}
</script>

<div class="seller-edit<?php echo $this->pageclass_sfx ?>">
	<?php if($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>	
	<?php echo $this->menu; ?>
	<?php if($this->vendor->id > 0) : ?>
		<div class="qz-page-header clearfix">		
			<div class="seller-account-status pull-right">
				<strong><?php echo JText::_('COM_QAZAP_SELLER_ACCOUNT_STATUS') ?>:</strong>
				<?php if($this->vendor->state) : ?>
					<?php if(!$this->vendor->state) : ?>
						<span class="label label-important toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_UNAPPROVED')?></span>
					<?php else : ?>
						<span class="label label-success toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_APPROVED')?></span>
					<?php endif; ?>
				<?php endif;?>
			</div>
			<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_SELLER_EDIT_BILLING_ADDRESS') ?></h2>		
		</div>
	<?php else : ?>
		<div class="qz-page-header clearfix">		
			<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_SELLER_ADD_BILLING_ADDRESS') ?></h2>		
		</div>	
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=sellerform&layout=edit') ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate form-horizontal label-align-left">
		<fieldset>
			<legend>
				<?php echo JText::_('COM_QAZAP_FIELDSET_GENERAL') ?>
			</legend>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('shop_name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('shop_name'); ?></div>
			</div>			
			<?php if($this->params->get('display_group', 1) == 1):?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('vendor_group_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('vendor_group_id'); ?></div>
				</div>
			<?php else : ?>
				<?php if($this->vendor->vendor_group_id > 0) : ?>
				<input type="hidden" name="jform[vendor_group_id]" value="<?php echo (int) $this->vendor->vendor_group_id ?>"/>
				<?php else : ?>
				<input type="hidden" name="jform[vendor_group_id]" value="<?php echo (int) $this->params->get('default_vendor_group') ?>" />
				<?php endif; ?>
			<?php endif; ?>
				<div class="control-group">
					<div class="control-label"><?php echo JText::_('Commission'); ?></div>
					<div class="controls"><input type="text" id="vendor_commission" value="<?php echo $this->group->commission?>%" class="readonly" readonly="true"/></div>
				</div>
			<?php if($this->params->get('display_categories', 1) == 1):?>					
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('category_list'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('category_list'); ?></div>
				</div>
			<?php else : ?>
				<input type="hidden" name="jform[category_list]" value="<?php echo $this->valueToString($this->vendor->category_list) ?>"/>
			<?php endif; ?>
			<?php if($this->params->get('display_shipments') == 1):?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('shipment_methods'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('shipment_methods'); ?></div>
				</div>
			<?php else : ?>
				<input type="hidden" name="jform[shipment_methods]" value="<?php echo $this->valueToString($this->vendor->shipment_methods) ?>"/>
			<?php endif;?>
		</fieldset>
		
		<?php foreach ($this->form->getFieldsets() as $key=>$fieldset): ?>
			<?php $fields = $this->form->getFieldset($fieldset->name); 
				if (count($fields)):?>
					<fieldset>						
						<legend>
							<?php echo (isset($fieldset->label) && !empty($fieldset->label)) ? JText::_($fieldset->label) : JText::_('COM_QAZAP_VENDORFIELD_CONTACT_DETAILS'); ?>
						</legend>
						<?php foreach ($fields as $field) :// Iterate through the fields in the set and display them.?>
							<?php 
							if($field->fieldname == 'image') : 
								continue;
							elseif ($field->hidden):// If the field is hidden, just display the input.?>
								<?php echo $field->input;?>
							<?php else:?>
								<?php if($field->type == 'Editor') : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input;?>
									</div>
								</div>
								<?php else : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input;?>
									</div>
								</div>
								<?php endif; ?>								
							<?php endif;?>
							<?php endforeach;?>
					</fieldset>
			<?php endif;?>
		<?php endforeach;?>
		
		<?php if($this->form->getField('image')) : ?>
			<fieldset>
				<legend>
					<?php echo JText::_($this->form->getField('image')->title) ?>
				</legend>			
				<div class="clearfix">
					<?php echo $this->form->getInput('image') ?>
				</div>					
			</fieldset>
		<?php endif; ?>			
	
		<div class="form-actions">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('seller.save')"><?php echo JText::_('JSAVE') ?></button>
			<button type="button" class="btn" onclick="Joomla.submitbutton('seller.cancel')"><?php echo JText::_('JCANCEL') ?></button>
			<?php echo $this->form->getInput('vendor_admin'); ?>    
			<?php echo $this->form->getInput('state'); ?>
			<?php echo $this->form->getInput('id'); ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return_page" value="<?php echo $this->return_page ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>