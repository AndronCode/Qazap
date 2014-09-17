<?php
/**
 * paymentlist.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;
JHtml::_('behavior.caption');
JHtml::_('behavior.framework');
QZApp::loadJS();
QZApp::loadCSS();
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
if($this->isVendor): 
?>
<div class="profile-<?php echo $this->layout . $this->pageclass_sfx ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>	
	<?php echo $this->menu; ?>
	<div class="qz-page-header clearfix">		
		<div class="seller-account-status pull-right">
			<strong><?php echo JText::_('COM_QAZAP_SELLER_ACCOUNT_STATUS') ?>:</strong>
			<?php if($this->isVendor) : ?>
				<?php if(!$this->activeVendor) : ?>
					<span class="label label-important toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_UNAPPROVED')?></span>
				<?php else : ?>
					<span class="label label-success toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_APPROVED')?></span>
				<?php endif; ?>
			<?php endif;?>
		</div>
		<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_SELLER_PAYMENT_LIST') ?></h2>		
	</div>

	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
		<fieldset class="filters btn-toolbar clearfix">
			<div class="input-append pull-right">
			  <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="inputbox" onchange="this.form.submit();" placeholder="<?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?>" />	
			  <button class="btn hasTooltip" type="submit" title="<?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?>"><?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?></button>
			  <button class="btn hasTooltip" type="button" onclick="this.form.getElementById('filter-search').value='';this.form.submit();" title="<?php echo JText::_('COM_QAZAP_GLOBAL_CLEAR'); ?>"><?php echo JText::_('COM_QAZAP_GLOBAL_CLEAR'); ?></button>
			</div>		
		</fieldset>
		<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'JDATE', 'a.date', $listDirn, $listOrder); ?>
					</th>			
					<th class="right">
						<?php echo JHtml::_('grid.sort', 'COM_QAZAP_PAYMENT_AMOUNT', 'a.payment_amount', $listDirn, $listOrder); ?>
					</th>
					<th class="right">
						<?php echo JHtml::_('grid.sort', 'COM_QAZAP_PAYMENT_CONFIRMED_ORDER_VALUE', 'a.total_confirmed_order', $listDirn, $listOrder); ?>
					</th>
					<th class="right">
						<?php echo JHtml::_('grid.sort', 'COM_QAZAP_PAYMENT_TOTAL_COMMISSION', 'a.total_confirmed_commission', $listDirn, $listOrder); ?>
					</th>				
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'COM_QAZAP_PAYMENT_STATUS', 'a.payment_status', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('grid.sort', 'COM_QAZAP_PAYMENT_ID', 'a.payment_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if(empty($this->payments)) : ?>
				<tr>
					<td colspan="6" class="center">
						<?php echo JText::_('COM_QAZAP_SELLER_NO_PAYMENT_MADE'); ?>
					</td>
				</tr>			
			<?php else : ?>
				<?php foreach($this->payments as $payment) : ?>	
					<tr>
						<td class="center">
							<?php echo QZHelper::displayDate($payment->date); ?>
						</td>			
						<td class="right">
							<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=seller&layout=paymentdetails&payment_id='.$payment->payment_id) ?>">
								<?php echo QZHelper::currencyDisplay($payment->payment_amount); ?>
							</a>
						</td>
						<td class="right">
							<?php echo QZHelper::currencyDisplay($payment->total_confirmed_order); ?>
						</td>
						<td class="right">
							<?php echo QZHelper::currencyDisplay($payment->total_confirmed_commission); ?>
						</td>
						<td class="center">
							<?php
								if($payment->payment_status == 1):
									echo JTEXT::_('COM_QAZAP_SUCCESS');
								else:
									echo JTEXT::_('COM_QAZAP_FAILURE');
								endif;
							
							?>
						</td>
						<td>
							<?php echo (int) $payment->payment_id ?>
						</td>				
					</tr>			
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<div class="pagination">
			<p class="counter pull-right">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>		
		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="limitstart" value="" />
		<input type="hidden" name="task" value="" />
	</form> 
	<?php endif; ?>

	<?php if(!empty($this->summery)) : ?>
	<h3><?php echo JText::_('COM_QAZAP_SELLER_ACCOUNT_SUMMERY') ?></h3>
	<table class="table table-striped table-bordered table-hover">
		<tr>
			<td><?php echo JText::_('COM_QAZAP_SELLER_TOTAL_ORDER_VALUE')?></td>
			<td class="right"><?php echo QZHelper::currencyDisplay($this->summery->total_order_value) ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_QAZAP_SELLER_TOTAL_COMPLETED_ORDER')?></td>
			<td class="right"><?php echo QZHelper::currencyDisplay($this->summery->total_confirmed_order) ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_QAZAP_SELLER_TOTAL_COMPLETED_COMMISSION')?></td>
			<td class="right"><?php echo QZHelper::currencyDisplay($this->summery->total_confirmed_commission) ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_QAZAP_SELLER_TOTAL_PAYMENT')?></td>
			<td class="right"><?php echo QZHelper::currencyDisplay($this->summery->total_payment) ?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('COM_QAZAP_SELLER_TOTAL_BALANCE')?></td>
			<td class="right"><?php echo QZHelper::currencyDisplay($this->summery->total_confirmed_order - ($this->summery->total_payment + $this->summery->total_confirmed_commission)) ?></td>
		</tr>		
	</table>
	<?php endif;?>
</div>