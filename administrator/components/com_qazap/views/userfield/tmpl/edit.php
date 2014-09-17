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
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_qazap/assets/css/qazap.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'userfield.cancel'){
            Joomla.submitform(task, document.getElementById('userfield-form'));
        }
        else{
            
            if (task != 'userfield.cancel' && document.formvalidator.isValid(document.id('userfield-form'))) {
                
                Joomla.submitform(task, document.getElementById('userfield-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=userfield&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="userfield-form" class="form-validate">
<?php 
	$columnName = $this->item->field_title;
	$Jtext_name = 'COM_QAZAP_USERFIELD_'.strtoupper($columnName);
?>
    <div class="row-fluid">
		<div class="span6 form-vertical">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('field_name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('field_name'); ?></div>
			</div>
		</div>
		<div class="span6 form-vertical">
			<div class="control-group">
				<div class="control-label"><?php echo JText::_('COM_QAZAP_USERFIELD_JTEXT'); ?></div>
				<div class="controls"><input type="text" disabled="disabled" value="<?php echo $Jtext_name ?>" /></div>
			</div>
		</div>
	</div>    
	<div class="form-horizontal">
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="form-vertical">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('values'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('values'); ?></div>
					</div>									
				</fieldset>
			</div>
			<div class="span3">
				<fieldset class="form-vertical side-panel-cont">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('max_length'); ?></div>
						<?php if(empty($this->item->max_length)): ?>
									<div class="controls"><?php echo $this->form->getInput('max_length'); ?></div>
						<?php else : ?>
									<div class="controls"><input type="text" id="jform_max_length" name="jform[max_length]" readonly="readonly" value="<?php echo $this->item->max_length ?>" /></div>	
						<?php endif; ?>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('field_title'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('field_title'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('field_type'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('field_type'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('required'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('required'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('show_in_userbilling_form'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('show_in_userbilling_form'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('show_in_shipment_form'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('show_in_shipment_form'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('read_only'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('read_only'); ?></div>
					</div>																		
				</fieldset>
			</div>
			
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_QAZAP_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid">
			<div class="span12">
				<fieldset class="form-horizontal">
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
				<div class="controls"><?php echo $this->form->getInput('modified_time'); ?></div>									</div>
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

    </div>
</form>