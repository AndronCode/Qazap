<?php

// no direct access
defined('_JEXEC') or die;

$form = $displayData['form'];
$order = $displayData['order'];

?>
<div class="qazap-popup">
	<div class="qazap-popup-title">
		<h3><?php echo JText::_('COM_QAZAP_ORDER_STATUS_EDIT_TITLE') ?></h3>
	</div>	
	<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $order->ordergroup_id); ?>" method="post" enctype="multipart/form-data" name="adminForm" class="form-validate form-vertical">
		<div class="qazap-popup-content">
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('order_status') ?></div>
				<div class="controls"><?php echo $form->getInput('order_status') ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('apply_to_all_items') ?></div>
				<div class="controls"><?php echo $form->getInput('apply_to_all_items') ?></div>
			</div>			
			<div class="control-group">
				<div class="control-label"><?php echo $form->getLabel('comment') ?></div>
				<div class="controls"><?php echo $form->getInput('comment') ?></div>
			</div>			
	  </div>
		<div class="qazap-popup-footer">
			<button type="button" class="qazap-popup-close btn"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?></button>	
			<button type="submit" class="btn btn-success"><?php echo JText::_('JAPPLY') ?></button>					
	  </div> 	  
		<input type="hidden" name="option" value="com_qazap"/>
		<input type="hidden" name="order_id" value="<?php echo $order->order_id ?>"/>
		<input type="hidden" name="task" value="order.updateorderstatus" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

