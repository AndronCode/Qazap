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
        if(task == 'currency.cancel'){
            Joomla.submitform(task, document.getElementById('currency-form'));
        }
        else{
            
            if (task != 'currency.cancel' && document.formvalidator.isValid(document.id('currency-form'))) {
                
                Joomla.submitform(task, document.getElementById('currency-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=currency&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="currency-form" class="form-validate">

	<div class="form-inline form-inline-header">
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('currency'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('currency'); ?></div>
		</div>
	</div>
    <div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span6">
				<fieldset class="form-horizontal">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('exchange_rate'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('exchange_rate'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('currency_symbol'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('currency_symbol'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('code3letters'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('code3letters'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('numeric_code'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('numeric_code'); ?></div>
					</div>									
				</fieldset>
			</div>
			<div class="span6">
				<fieldset class="form-horizontal">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('decimals'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('decimals'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('decimals_symbol'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('decimals_symbol'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('format'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('format'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('thousand_separator'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('thousand_separator'); ?></div>
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
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>

    </div>
</form>