<?php
/**
 * orderlist.php
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
if($this->isVendor) : 
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
		<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_SELLER_ORDER_LIST') ?></h2>		
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
					<th><?php echo JHtml::_('grid.sort', 'COM_QAZAP_ORDER_NUMBER', 'a.order_number', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', 'Order Total', 'a.Total', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', 'Order Status', 'c.status_name', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', 'JGLOBAL_USERNAME', 'd.first_name', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
			<?php 
				if(empty($this->orders)):
					echo JText::_('COM_QAZAP_ORDER_NOT_FOUND');
				else:
					foreach($this->orders as $order):
			?>
			<tbody>
				<tr>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=seller&layout=order&ordergroup_id=' . (int) $order->ordergroup_id) ?>"><?php echo $order->order_number?></a>
					</td>
					<td>
						<?php echo QZHelper::orderCurrencyDisplay($order->Total, $order->order_currency)?>
					</td>
					<td>
						<?php echo $order->status_name?>
					</td>
					<td>
						<?php echo $order->first_name.$order->middle_name.$order->last_name?>
					</td>
				</tr>
				
			</tbody>
			<?php
					endforeach; 
				endif;
			?>
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
	<?php else :?>
	register

	<?php endif;?>
</div>