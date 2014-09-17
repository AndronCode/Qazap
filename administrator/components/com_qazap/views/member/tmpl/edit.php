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
$document->addScriptDeclaration('
jQuery(document).ready(function(){
	jQuery("#jform_from_date, #jform_to_date").attr("readonly", "readonly");
});
')
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'member.cancel'){
            Joomla.submitform(task, document.getElementById('member-form'));
        }
        else{
            
            if (task != 'member.cancel' && document.formvalidator.isValid(document.id('member-form'))) {
                
                Joomla.submitform(task, document.getElementById('member-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=member&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="member-form" class="form-validate">
   <div class="form-horizontal">
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="form-horizontal">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
						<div class="controls">
							<?php 
							if($this->item->user_id)
							{
								$this->form->setFieldAttribute('user_id', 'readonly', 'true');
							}
							echo $this->form->getInput('user_id'); 
							?>					
						</div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('membership_id'); ?></div>
						<div class="controls">
							<?php 
							if($this->item->membership_id)
							{
								$this->form->setFieldAttribute('membership_id', 'readonly', true);
							}
							echo $this->form->getInput('membership_id'); 
							?>				
						</div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('from_date'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('from_date'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('to_date'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('to_date'); ?></div>
					</div>
					<?php if($this->item->status!== ""):?>
					<div class="control-group">
						<div class="control-label"><?php echo JText::_('COM_QAZAP_ACTIVATION_STATE') ?></div>
						<div class="controls">
						<?php 
							if($this->item->status == 1)
							{
								echo JText::_('COM_QAZAP_FORM_LBL_MEMBER_ACTIVE');	
							}
							else
							{
								echo JText::_('COM_QAZAP_FORM_LBL_MEMBER_EXPIRE');
							}
							
						?>
						</div>
					</div>
					<?php endif;?>										
				</fieldset>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_QAZAP_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid">
			<div class="span12">
				<fieldset class="form-horizontal">
					<?php if($this->item->jusergroup_id):?>
					<div class="control-group">
						<div class="control-label"><?php echo JText::_('COM_QAZAP_CREATED_BY') ?></div>
						<div class="controls"><?php echo $this->item->created_by; ?></div>
					</div>
					<?php endif;?>
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
	<?php if($this->membershipHistory):?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'history', JText::_('COM_QAZAP_FIELDSET_HISTORY', true)); ?>
			<div class="row-fluid">
				<div class="span12">
					<fieldset class="form-horizontal">
						<table class="table table-striped">
							<thead>
								<tr>
									<th class="left"><?php echo JText::_ ('COM_QAZAP_ORDERS_MEMBERSHIP_STATUS') ?></th>
									<th class="left"><?php echo JText::_ ('COM_QAZAP_FORM_LBL_ORDER_CREATED_ON') ?></th>
									<th class="left"><?php echo JText::_ ('COM_QAZAP_FORM_LBL_ORDER_CREATED_BY') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($this->membershipHistory as $membership_history):?>
									<tr>
										<td><?php echo ($membership_history->status == 1)? JText::_('COM_QAZAP_ACTIVE'):JText::_('COM_QAZAP_FORM_LBL_MEMBER_EXPIRE') ?></td>
										<td><?php echo JHTML::Date($membership_history->date,'d-m-Y H:i:s',true)?></td>
										<td><?php echo $membership_history->name ?></td>																	</tr>
								<?php endforeach;?>	
							</tbody>
						</table>
					</fieldset>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif;?>
	</div>  
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>

