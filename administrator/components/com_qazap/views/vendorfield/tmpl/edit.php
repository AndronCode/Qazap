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
        if(task == 'vendorfield.cancel'){
            Joomla.submitform(task, document.getElementById('vendorfield-form'));
        }
        else{
            
            if (task != 'vendorfield.cancel' && document.formvalidator.isValid(document.id('vendorfield-form'))) {
                
                Joomla.submitform(task, document.getElementById('vendorfield-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=vendorfield&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="vendorfield-form" class="form-validate">
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">

                			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('field_name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('field_name'); ?></div>
			</div>
			<?php 
				$columnName = $this->item->field_title;
				$Jtext_name = 'COM_QAZAP_VENDORFIELD_'.strtoupper($columnName);
			?>
			<div class="control-group">
				<div class="control-label"><?php echo JText::_('COM_QAZAP_USERFIELD_JTEXT'); ?></div>
				<div class="controls"><input type="text" disabled="disabled" value="<?php echo $Jtext_name ?>" /></div>
			</div>			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('max_length'); ?></div>
				<?php if(empty($this->item->max_length)) { ?>
				<div class="controls"><?php echo $this->form->getInput('max_length'); ?></div>
				<?php } else { ?>
				<div class="controls"><input type="text" id="jform_max_length" name="jform[max_length]" readonly="readonly" value="<?php echo $this->item->max_length ?>" /></div>	
				<?php } ?>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('field_title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('field_title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('field_type'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('field_type'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('values'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('values'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('required'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('required'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('read_only'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('read_only'); ?></div>
			</div>
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


            </fieldset>
        </div>

        

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>