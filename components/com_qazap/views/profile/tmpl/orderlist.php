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
?>
<div class="profile-<?php echo $this->layout . $this->pageclass_sfx ?>">
	
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>	
	<?php echo $this->menu; ?>
	<div class="qz-page-header clearfix">		
		<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_ALL_ORDERS') ?></h2>		
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
				<th>
					<?php echo JHtml::_('grid.sort', 'COM_QAZAP_ORDERGROUP_NUMBER_LABEL', 'og.ordergroup_number', $listDirn, $listOrder); ?>
				</th>
				<th class="right">
					<?php echo JHtml::_('grid.sort', 'COM_QAZAP_ORDER_TOTAL', 'og.cart_total', $listDirn, $listOrder); ?>						
				</th>
				<th class="center">
					<?php echo JHtml::_('grid.sort', 'COM_QAZAP_ORDER_STATUS', 'os.status_name', $listDirn, $listOrder); ?>						
				</th>
				<th class="center">
					<?php echo JHtml::_('grid.sort', 'COM_QAZAP_ORDERGROUP_DATE_LABEL', 'og.created_on', $listDirn, $listOrder); ?>						
				</th>
			</tr>
		</thead>
		<tbody>
		<?php if(empty($this->orders)) : ?>
			<tr>
				<td colspan="4" class="center">
					<?php echo JText::_('COM_QAZAP_ORDER_NOT_FOUND'); ?>
				</td>
			</tr>			
		<?php else : ?>
			<?php foreach($this->orders as $order): ?>		
				<tr>
					<td>
						<a href="<?php echo JRoute::_(QazapHelperRoute::getOrderdetailsRoute($order->ordergroup_id)) ?>"><?php echo $order->ordergroup_number?></a>
					</td>
					<td class="right">
						<?php echo QZHelper::orderCurrencyDisplay($order->cart_total, $order->order_currency, $order->user_currency, $order->currency_exchange_rate) ?>
					</td>
					<td class="center">
						<?php echo $order->status_name ?>
					</td>
					<td class="center">
						<?php echo QZHelper::displayDate($order->created_on) ?>
					</td>
				</tr>		
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		<div class="pagination">
			<p class="counter pull-right">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>

		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	</table>
		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="limitstart" value="" />
		<input type="hidden" name="task" value="" />
	</form>
</div> 
