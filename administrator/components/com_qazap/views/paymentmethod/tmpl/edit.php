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

JHtml::_('jquery.framework');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
JHtml::_('script', 'system/html5fallback.js', false, true);
JHtml::_('behavior.colorpicker');
// Import CSS
$document = JFactory::getDocument();

?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if(task == 'paymentmethod.cancel'){
            Joomla.submitform(task, document.getElementById('paymentmethod-form'));
        }
        else{
            
            if (task != 'paymentmethod.cancel' && document.formvalidator.isValid(document.id('paymentmethod-form'))) {
                
                Joomla.submitform(task, document.getElementById('paymentmethod-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=paymentmethod&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="paymentmethod-form" class="form-validate">
	<div class="form-inline form-inline-header">
		<div class="control-group ">
			<div class="control-label"><?php echo $this->form->getLabel('payment_name'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('payment_name'); ?></div>
		</div>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="form-vertical">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('payment_description'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('payment_description'); ?></div>
					</div>
				</fieldset>					
			</div>
			<div class="span3">
				<fieldset class="form-vertical">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('payment_method'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('payment_method'); ?></div>
					</div>	
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('logo'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('logo'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('price'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('price'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('tax'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('tax'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('tax_calculation'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('tax_calculation'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('user_group'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('user_group'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('countries'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('countries'); ?></div>
					</div>						
				</fieldset>										
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'configuration', JText::_('COM_QAZAP_FIELDSET_CONFIGURATION', true)); ?>
		<div class="row-fluid">
			<div class="span12">
				<fieldset class="form-horizontal" id="PaymentParams">
					<?php 
					$fieldSets = $this->form->getFieldsets('params');
					foreach ($fieldSets as $name => $fieldSet) :
						foreach($this->form->getFieldset($name) as $field) : ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>								
						<?php endforeach; ?>
					<?php endforeach; ?>						
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