<?php
/**
 * default.php
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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$ordering 	= ($listOrder == 'og.ordergroup_id');
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
$sortFields = $this->getSortFields();
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
var jQ = jQuery.noConflict();
jQ(document).ready(function(){
	jQ('.orders-list-opener').click(function(e) {
		e.preventDefault();
		jq(this).toggleClass('closed');
		var toOpen = jq(this).attr('href');
		jq(toOpen).toggleClass('hide');
		return false;		
	});
});
");


?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=orders'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif;?>
    	<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?> 
		<table class="table" id="orderList">
			<thead>
				<tr>		
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_FORM_LBL_USER', 'oa.first_name', $listDirn, $listOrder); ?>
					</th>                  
					<th width="15%">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_ORDERGROUP_NUMBER_LABEL', 'og.ordergroup_number', $listDirn, $listOrder); ?>
					</th>	
					<th width="8%" class="nowrap right">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_ORDERGROUP_CART_TOTAL_LABEL', 'og.cart_total', $listDirn, $listOrder); ?>
					</th>						
					<th width="8%" class="nowrap hidden-phone center">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_TITLE_PAYMENTMETHOD', 'pm.payment_name', $listDirn, $listOrder); ?>
					</th>	
					<th width="8%" class="center">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_ORDERGROUP_ORDER_STATUS_LABEL', 'os.status_name', $listDirn, $listOrder); ?>
					</th>														
					<th width="3%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_ORDERGROUP_ID_LABEL', 'og.ordergroup_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			<?php 
			if(isset($this->items[0]))
			{
				$colspan = count(get_object_vars($this->items[0]));
			}
			else
			{
				$colspan = 10;
			}
			?>
			<tr>
				<td colspan="<?php echo $colspan ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :	
        $canCreate	= $user->authorise('core.create', 'com_qazap');
        $canEdit	= $user->authorise('core.edit', 'com_qazap');
        $canCheckin	= $user->authorise('core.manage', 'com_qazap');
        $canChange	= $user->authorise('core.edit.state', 'com_qazap');
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->ordergroup_id ?>" item-id="<?php echo $item->ordergroup_id ?>">  
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->ordergroup_id); ?>
					</td>
					<td class="center">
						<?php echo preg_replace('/\s+/', ' ', $item->username) ?>
					</td>
					<td>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=order.edit&ordergroup_id=' . $item->ordergroup_id); ?>">
								<?php echo $this->escape($item->ordergroup_number); ?>
							</a>
						<?php else : ?>
							<?php echo $this->escape($item->ordergroup_number); ?>
						<?php endif; ?>
						<a href="#qazap-order-group-<?php echo $item->ordergroup_id ?>" class="small orders-list-opener closed">
							<span class="on-close text-warning">(View Orders)</span>
							<span class="on-open text-success">(Hide Orders)</span>
						</a>
					</td>
					<td class="nowrap right">
						<?php echo QazapHelper::orderCurrencyDisplay($item->cart_total, $item->order_currency) ?>
					</td>										
					<td class="center">
						<?php echo $this->escape($item->payment_name); ?>
					</td>	
					<td class="center">
						<?php echo $this->escape($item->status_name); ?>
					</td>														
					<td class="center hidden-phone">
						<?php echo (int) $item->ordergroup_id ?>
					</td>
				</tr>
				<?php if(isset($this->orders[$item->ordergroup_id])) :?>
				<tr class="qazap-orders-row hide" id="qazap-order-group-<?php echo $item->ordergroup_id ?>">
					<td colspan="2"></td>
					<td colspan="5">
						<table class="table table-striped">
							<thead>
								<tr>
									<th width="20%" class="small">
										<?php echo JText::_('COM_QAZAP_ORDER_NUMBER') ?>
									</th>
									<th width="10%" class="small">
										<?php echo JText::_('COM_QAZAP_ORDER_VENDOR') ?>
									</th>
									<th width="20%" class="nowrap right small">
										<?php echo JText::_('COM_QAZAP_ORDER_TOTAL_IN_ORDER_CURRENCY') ?>
									</th>
									<th width="15%" class="nowrap right small">
										<?php echo JText::_('COM_QAZAP_ORDER_COMMISSION') ?>
									</th>	
									<th width="15%" class="center small">
										<?php echo JText::_('COM_QAZAP_ORDER_STATUS') ?>
									</th>
									<th width="15%" class="center small">
										<?php echo JText::_('COM_QAZAP_ORDER_ID') ?>
									</th>																																									
								</tr>
							</thead>
							<?php foreach($this->orders[$item->ordergroup_id] as $order) : ?>
							<tr>
								<td class="small">
									<?php echo $this->escape($order->order_number) ?>
								</td>
								<td class="small">
									<?php echo $this->escape($order->shop_name) ?>
								</td>
								<td class="nowrap right small">
									<?php echo QazapHelper::orderCurrencyDisplay($order->Total, $item->order_currency) ?>
								</td>
								<td class="nowrap right small">
									<?php echo QazapHelper::orderCurrencyDisplay($order->commission, $item->order_currency) ?>
								</td>
								<td class="center small">
									<?php echo $this->escape($order->status_name) ?>
								</td>	
								<td class="center small">
									<?php echo $this->escape($order->order_id) ?>
								</td>																																
							</tr>
							<?php endforeach; ?>
						</table>
					</td>
				</tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif;?>
		<?php //Load the batch processing form. ?>
		<?php echo $this->loadTemplate('batch'); ?>
				
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>