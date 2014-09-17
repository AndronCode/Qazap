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
        if(task == 'coupon.cancel'){
            Joomla.submitform(task, document.getElementById('coupon-form'));
        }
        else{
            
            if (task != 'coupon.cancel' && document.formvalidator.isValid(document.id('coupon-form'))) {
                
                Joomla.submitform(task, document.getElementById('coupon-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=coupon&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="coupon-form" class="form-validate">
	<div class="form-inline form-inline-header">
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('coupon_code'); ?></div>
			<?php 
				if(empty($this->item->coupon_code)) 
				{ 
			?>
					<div class="controls"><?php echo $this->form->getInput('coupon_code'); ?></div>
			<?php 
				} 
				else 
				{ 
			?>
					<div class="controls"><input type="text" id="jform_max_length" name="jform[coupon_code]" readonly="readonly" value="<?php echo $this->item->coupon_code ?>" /></div>	
			<?php 
				} 
			?>
		</div>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span6">
				<fieldset class="form-horizontal">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('coupon_value'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('coupon_value'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('min_order_amount'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('min_order_amount'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('coupon_start_date'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('coupon_start_date'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('coupon_expiry_date'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('coupon_expiry_date'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
				</fieldset>
			</div>
			<div class="span6">
				<fieldset class="form-horizontal">

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('math_operation'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('math_operation'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('coupon_usage_type'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('coupon_usage_type'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('coupon_usage_limit'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('coupon_usage_limit'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('categories'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('categories'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('countUsage'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('countUsage'); ?></div>
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
		<?php 
			if(count($this->usage)):
				echo JHtml::_('bootstrap.addTab', 'myTab', 'usage', JText::_('COM_QAZAP_FIELDSET_USAGE', true));
		?>
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<table class="table table-striped">
								<thead>
									<tr>
										<th class="left">Id</th>
										<th class="left">User Name</th>
										<th class="left">Date</th>
										<th class="left">Order Id</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($this->usage as $usage):?>
									<tr>
										<td>
											<?php echo $usage->id?>
										</td>
										<td>
											<?php echo $usage->username?>
										</td>
										<td>
											<?php echo $usage->date?>
										</td>
										<td>
											<?php echo $usage->ordergroup_number?>
										</td>
									</tr>
									<?php endforeach;?>
								</tbody>
							</table>
						</fieldset>
					</div>
				</div>
		<?php 
			echo JHtml::_('bootstrap.endTab');
			endif;
		?>
				
	</div> 	  	
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>	
</form>	