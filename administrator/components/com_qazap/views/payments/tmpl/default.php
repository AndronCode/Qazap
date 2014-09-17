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
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_qazap/assets/css/qazap.css');

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
$sortFields = $this->getSortFields();
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

<?php
//Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar)) {
    $this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=payments'); ?>" method="post" name="adminForm" id="adminForm">
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
		<table class="table table-striped" id="paymentSummery">	
			<tr>
				<th class="left">Summery</th>
				<th class="right">Total Order Value</th>
				<th class="right">Total Commission</th>
				<th class="right">Confirmed Order Value</th>
				<th class="right">Confirmed Commission</th>
				<th class="right">Vendor Earning</th>
				<th class="right">Paid</th>
				<th class="right">Balance</th>
			</tr>
			<tr>
				<td class="left">Total</td>
				<td class="right"><?php echo QazapHelper::currencyDisplay($this->paymentTotal->total_order_value) ?></td>
				<td class="right"><?php echo QazapHelper::currencyDisplay($this->paymentTotal->total_commission_value) ?></td>
				<td class="right"><?php echo QazapHelper::currencyDisplay($this->paymentTotal->total_confirmed_order) ?></td>
				<td class="right"><?php echo QazapHelper::currencyDisplay($this->paymentTotal->total_confirmed_commission) ?></td>
				<td class="right"><?php echo QazapHelper::currencyDisplay($this->paymentTotal->earning) ?></td>
				<td class="right"><?php echo QazapHelper::currencyDisplay($this->paymentTotal->total_payment) ?></td>
				<td class="right"><?php echo QazapHelper::currencyDisplay($this->paymentTotal->balance) ?></td>						
			</tr>      
		</table>		
		
		<table class="table table-striped" id="paymentList">
			<thead>
				<tr>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="3%" class="center">
						<?php //echo JHtml::_('grid.sort',  'COM_QAZAP_PAYMENTS_VENDOR', 'b.shop_name', $listDirn, $listOrder); ?>
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_PAYMENTS_VENDOR', 'b.shop_name', $listDirn, $listOrder); ?>
					</th>					
					<th class="center">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_PAYMENTS_DATE', 'a.date', $listDirn, $listOrder); ?>
						
					</th>
					<th class="right">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_PAYMENTS_PAYMENT_AMOUNT', 'a.payment_amount', $listDirn, $listOrder); ?>
					</th>	    
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_PAYMENTS_PAYMENT_METHOD', 'a.payment_method', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_PAYMENTS_PAYMENT_STATUS', 'a.payment_status', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_PAYMENTS_MAIL_SENT', 'a.mail_sent', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.payment_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
                <?php 
                if(isset($this->items[0])){
                    $colspan = count(get_object_vars($this->items[0]));
                }
                else{
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
                $canCreate	= $user->authorise('core.create',		'com_qazap');
                $canEdit	= $user->authorise('core.edit',			'com_qazap');
                $canCheckin	= $user->authorise('core.manage',		'com_qazap');
                $canChange	= $user->authorise('core.edit.state',	'com_qazap');
				?>
				<tr class="row<?php echo $i % 2; ?>">
                    
				<td class="center hidden-phone">
					<?php echo JHtml::_('grid.id', $i, $item->payment_id); ?>
				</td>                    
				<td class="center">
					<?php 
						if($item->state == 0)
						{
							echo "<i class='icon-unpublish hasTooltip' title='UnProcessed' ></i>";
						}	
						if($item->state == -2)
						{
							echo "<i class='icon-trash hasTooltip' title='Trashed' ></i>";
						}
						if($item->state == 1)
						{
							echo "<i class='icon-publish hasTooltip' title='Processed' ></i>";
						}
					 ?>
				</td>
				<td>
				<?php if (isset($item->checked_out) && $item->checked_out) : ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'payments.', $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=payment.edit&payment_id='.(int) $item->payment_id); ?>">
					<?php echo $this->escape($item->shop_name); ?></a>
				<?php else : ?>
					<?php echo $this->escape($item->shop_name); ?>
				<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $item->date; ?>
				</td>
				<td class="right">
				<?php if ($canEdit) : ?>
					
					<?php echo $this->escape(QazapHelper::currencyDisplay($item->payment_amount)); ?>
				<?php else : ?>
					<?php echo $this->escape(QazapHelper::currencyDisplay($item->payment_amount)); ?>
				<?php endif; ?>
				</td>
				<td>
					<?php					
					if(!$item->payment_method)
					{
						echo "Manual Payment";
					}
					else
					{
						$plugin = QazapHelper::getPlugin($item->payment_method);
						if(isset($plugin->name))
						{
							echo $plugin->name;
						}	
					}				
					
					 ?>
				</td>
				<td class="center">
					<?php 
					if(!$item->payment_status)
					{
						echo "Unpaid";
					}
					else
					{
						echo "Paid";
						}
					?>
				</td>
				<td class="center">
					<?php 
					if(!$item->mail_sent)
					{
						echo "Not Sent";
					}
					else
					{
						echo "Sent";
						}
					?>
				</td>
				<td class="center hidden-phone">
					<?php echo (int) $item->payment_id; ?>
				</td>

				</tr>
				<?php endforeach; ?>
			</tbody>			
		</table>
		<?php endif;?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form> 
		
