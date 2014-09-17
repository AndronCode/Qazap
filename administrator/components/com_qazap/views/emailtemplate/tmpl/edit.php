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
        if(task == 'emailtemplate.cancel')
		{
            Joomla.submitform(task, document.getElementById('email-form'));
        }
        else
		{
            if (task != 'emailtemplate.cancel' && document.formvalidator.isValid(document.id('email-form'))) 
			{
                
                Joomla.submitform(task, document.getElementById('email-form'));
            }
            else 
			{
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=emailtemplate&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="email-form" class="form-validate">
    <div class="row-fluid">
		<div class="span6 form-vertical">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
			</div>
		</div>
		<div class="span6 form-vertical">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('subject'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('subject'); ?></div>
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
						<div class="control-label"><?php echo $this->form->getLabel('body'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('body'); ?></div>
					</div>
<!--					<div class="control-group">
						<div class="control-label"><?php //echo $this->form->getLabel('altbody'); ?></div>
						<div class="controls"><?php //echo $this->form->getInput('altbody'); ?></div>
					</div>-->
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('css'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('css'); ?></div>
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
						<div class="control-label"><?php echo $this->form->getLabel('purpose'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('purpose'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('mode'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('mode'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('default'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('default'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('lang'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('lang'); ?></div>
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
	</div>

<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>