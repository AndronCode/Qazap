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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'currency.cancel'){
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
        else{
            
            if (task != 'currency.cancel' && document.formvalidator.isValid(document.id('adminForm'))) {
                
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&layout=edit'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
	<div class="form-inline form-inline-header">
		<div class="control-group ">
			<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
		</div>
	</div>
    <div class="form-horizontal">	
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
			
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span6">
				<fieldset class="form-horizontal">					
					<div class="control-group ">
						<div class="control-label"><?php echo $this->form->getLabel('company'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('company'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('contact_person'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('contact_person'); ?></div>
					</div>						
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('address_1'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('address_1'); ?></div>
					</div>						
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('address_2'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('address_2'); ?></div>
					</div>						
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('city'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('city'); ?></div>
					</div>						
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('zip'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('zip'); ?></div>
					</div>						
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('country'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('country'); ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>						
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('phone_1'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('phone_1'); ?></div>
					</div>	
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('phone_2'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('phone_2'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('fax'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('fax'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('mobile'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('mobile'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('vat'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('vat'); ?></div>
					</div>
				</fieldset>	
			</div>
			<div class="span6">
				<fieldset class="form-vertical row-fluid">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
					</div>
				</fieldset>	
				<fieldset class="form-vertical row-fluid">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('additional_info'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('additional_info'); ?></div>
					</div>
				</fieldset>									
			</div>
		</div>
		  
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		  
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tos', JText::_('COM_QAZAP_FIELDSET_TOS', true)); ?>
		<div class="row-fluid">
			<fieldset class="form-vertical">
				<div class="control-group">
					<div class="controls"><?php echo $this->form->getInput('tos'); ?></div>
				</div>
			</fieldset>		
		</div>		
		<?php echo JHtml::_('bootstrap.endTab'); ?> 
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_QAZAP_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_by'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_by'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_time'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_time'); ?></div>
				</div>				
			</fieldset>
		</div>				 
		<?php echo JHtml::_('bootstrap.endTab'); ?> 
		  
	</div>
        
	<?php echo $this->form->getInput('lang'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>