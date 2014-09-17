<?php
/**
 * default_batch.php
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

?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_QAZAP_ORDER_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
		<p><?php echo JText::_('COM_QAZAP_ORDER_BATCH_TIP'); ?></p>
		<div class="form-horizontal">
			<fieldset>
				<div class="control-group">
					<label class="control-label hasTooltip" for="batch-order-status" title="<?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_DESC') ?>">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_LABEL') ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_('qzbatch.orderstatus'); ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label hasTooltip" for="batch-order-status" title="<?php echo JText::_('COM_QAZAP_ORDERGROUP_APPLY_TO_ALL_ORDERS_DESC') ?>">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_APPLY_TO_ALL_ORDERS_LABEL') ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_('qzbatch.applytoall'); ?>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label hasTooltip" for="batch-order-status" title="<?php echo JText::_('COM_QAZAP_ORDERGROUP_COMMENTS_DESC') ?>">
						<?php echo JText::_('COM_QAZAP_ORDERGROUP_COMMENTS_LABEL') ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_('qzbatch.comment'); ?>
					</div>
				</div>								
			</fieldset>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.id('batch-order-status').value='';document.id('batch-order-status').selectedIndex = 0;document.id('batch-comment').value='';" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('order.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
