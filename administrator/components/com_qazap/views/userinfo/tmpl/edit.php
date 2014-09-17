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
$model = $this->getModel();
$document = JFactory::getDocument();
$selectedStateID = $this->item->states_territory;
$document->addScriptDeclaration("\n"."window.qzstate = '$selectedStateID';"."\n");
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'userinfo.cancel'){
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
        else{
            
            if (task != 'userinfo.cancel' && document.formvalidator.isValid(document.id('adminForm'))) {
                
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
	UserinfoFormsubmit = function(task){
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
	
</script>
<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=userinfo&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate">
    
    <div class="row-fluid">
		<div class="span6 form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
			</div>
		</div>
		<div class="span6 form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('address_type'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('address_type'); ?></div>
			</div>
		</div>
	</div>
	<div class="form-horizontal">
	<?php 
	$fieldSets = $this->form->getFieldsets(); 
	$tabs = array_keys($fieldSets);
	echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => $tabs[0])); ?>	
	<?php foreach ($this->form->getFieldsets() as $key => $fieldset) : ?>
		<?php $fields = $this->form->getFieldset($fieldset->name);
			if (count($fields)):
				$groupName = (isset($fieldset->label) && !empty($fieldset->label)) ? $fieldset->label : 'COM_QAZAP_USERFIELD_DETAILS';	
				?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', $key, JText::_($groupName)); ?>
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<?php foreach ($fields as $field) :// Iterate through the fields in the set and display them.
								if ($field->hidden):
									echo $field->input;
								else : ?>
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

    </div>
</form>