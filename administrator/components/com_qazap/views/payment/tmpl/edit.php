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
    jq(document).ready(function(){
        jq('#jform_payment_amount').keyup(function(){
			var totalBalance = jq('#jform_total_balance').val();
			totalBalance = parseFloat(totalBalance);
			var payment = jq(this).val();
			payment = parseFloat(payment);
			jq('#jform_balance').val(totalBalance - payment);
		});
		jq('#jform_last_payment_date_img').remove();
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'payment.cancel'){
            Joomla.submitform(task, document.getElementById('payment-form'));
        }
        else{
            
            if (task != 'payment.cancel' && document.formvalidator.isValid(document.id('payment-form'))) {
                
                Joomla.submitform(task, document.getElementById('payment-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=payment&layout=edit&payment_id=' . (int) $this->item->payment_id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="payment-form" class="form-validate">
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">

            <div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('payment_id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('payment_id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
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
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('vendor'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('vendor'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('date'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('date'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('total_order_value'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('total_order_value'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('total_confirmed_order'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('total_confirmed_order'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('total_commission_value'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('total_commission_value'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('total_confirmed_commission'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('total_confirmed_commission'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('last_payment_amount'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('last_payment_amount'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('last_payment_date'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('last_payment_date'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('total_paid_amount'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('total_paid_amount'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('total_balance'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('total_balance'); ?></div>
			</div>
			<!--<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('currency'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('currency'); ?></div>
			</div>-->
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('payment_amount'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('payment_amount'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('balance'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('balance'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('note'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('note'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('payment_status'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('payment_status'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('payment_method'); ?></div>
				<div class="controls" id="paymentmethod-selector"><?php echo $this->form->getInput('payment_method'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('send_mail'); ?></div>
				<div class="controls" id="paymentmethod-selector"><?php echo $this->form->getInput('send_mail'); ?></div>
			</div>
			
			<div id="paymentmethod-params">
				<?php if($this->params) :
				foreach($this->params->getFieldset('params') as $field) : 
					if ($field->hidden): 
						 echo $field->input;
					else: ?>
						<div class="control-group">
							<div class="control-label">
							<?php echo $field->label; ?>
							<?php if (!$field->required && $field->type != 'Spacer') : ?>
								<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
							<?php endif; ?>
							</div>
							<div class="controls">
								<?php echo $field->input;?>
							</div>
						</div>
					<?php endif;?>		
				<?php endforeach; ?>					
				<?php endif;?>					
			</div>

            </fieldset>
        </div>

        

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>