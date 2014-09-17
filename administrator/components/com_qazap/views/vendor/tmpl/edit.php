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
 * @subpackage Admin
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

// Import CSS
$document = JFactory::getDocument();
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'vendor.cancel'){
            Joomla.submitform(task, document.getElementById('vendor-form'));
        }
        else{
            
				js = jQuery.noConflict();
				if(js('#jform_used_url').val() != ''){
					js('#jform_used_url_hidden').val(js('#jform_used_url').val());
				}
            if (task != 'vendor.cancel' && document.formvalidator.isValid(document.id('vendor-form'))) {
                
                Joomla.submitform(task, document.getElementById('vendor-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=vendor&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="vendor-form" class="form-validate">

	<div class="row-fluid">
		<div class="span6 form-vertical">
			<div class="control-label"><?php echo $this->form->getLabel('shop_name'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('shop_name'); ?></div>
		</div>
		<div class="span6 form-vertical">
			<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
		</div>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		
		<div class="row-fluid">
			<div class="span12">
				<fieldset class="form-horizontal">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('vendor_admin'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('vendor_admin'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('vendor_group_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('vendor_group_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo JText::_('Commission'); ?></div>
						<div class="controls"><input type="text" id="vendor_commission" value="" disabled="disabled"/></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('category_list'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('category_list'); ?></div>
					</div>												
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('shipment_methods'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('shipment_methods'); ?></div>
					</div>			
				</fieldset>
			</div>			
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<!--Iterate through the form fieldsets and display each one.-->
	<?php foreach ($this->form->getFieldsets() as $key=>$fieldset): ?>
		<?php $fields = $this->form->getFieldset($fieldset->name); 
			if (count($fields)):
				$groupName = (isset($fieldset->label) && !empty($fieldset->label)) ? $fieldset->label : 'COM_QAZAP_VENDORFIELD_CONTACT_DETAILS';				
				?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', $key, JText::_($groupName)); ?>
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">					
							<?php foreach ($fields as $field) :// Iterate through the fields in the set and display them.?>
								<?php if ($field->hidden):// If the field is hidden, just display the input.?>
										<?php echo $field->input;?>
								<?php else:?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input;?>
										</div>
									</div>
								<?php endif;?>
								<?php endforeach;?>
						</fieldset>
					</div>
				</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif;?>
	<?php endforeach;?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_QAZAP_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid">
			<div class="span12">
				<fieldset class="form-horizontal">
					<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created_time'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('created_time'); ?></div>
					</div>	
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('modified_by'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('modified_by'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('modified_time'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('modified_time'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
					</div>							
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>				
	</div>
        

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

</form>