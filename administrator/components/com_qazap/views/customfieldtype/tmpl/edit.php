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
$model = $this->getModel();
$document = JFactory::getDocument();
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'customfieldtype.cancel'){
            Joomla.submitform(task, document.getElementById('customfieldtype-form'));
        }
        else{
            
            if (task != 'customfieldtype.cancel' && document.formvalidator.isValid(document.id('customfieldtype-form'))) {
                
                Joomla.submitform(task, document.getElementById('customfieldtype-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=customfieldtype&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="customfieldtype-form" class="form-validate">
	<div class="form-inline form-inline-header">
		<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>		
		</div>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="form-vertical">
					<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
				</fieldset>
			</div>
			<div class="span3">
				<fieldset class="form-vertical side-panel-cont">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('show_title'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('show_title'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('hidden'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('hidden'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('tooltip'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('tooltip'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('layout_position'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('layout_position'); ?></div>
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
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'params', JText::_('COM_QAZAP_FIELDSET_PARAMS', true)); ?>
			<div class="row-fluid">
				<div class="span12">
					<fieldset class="form-horizontal">
						<div id="customFieldParams">
						<?php 
						if($this->params) : 
							$fieldSets = $this->params->getFieldsets('params');
							foreach ($fieldSets as $name => $fieldSet) :
								foreach($this->params->getFieldset($name) as $field) :?>
									<div class="control-group">
										<div class="control-label"><?php echo $field->label; ?></div>
										<div class="controls"><?php echo $field->input; ?></div>
									</div>								
								<?php endforeach; ?>
							<?php endforeach; ?>
						<?php endif; ?>																			
						</div>								
					</fieldset>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	</div>    
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>