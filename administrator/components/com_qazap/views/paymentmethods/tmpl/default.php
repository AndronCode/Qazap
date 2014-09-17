<?php
/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Anik Saha <anik.saha.2007@gmail.com> - http://www.virtueplanet.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_qazap&task=paymentmethods.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'paymentmethodList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$originalOrders = array();
//$sortFields = $this->getSortFields();
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

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=paymentmethods'); ?>" method="post" name="adminForm" id="adminForm">
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
		<table class="table table-striped" id="paymentmethodList">
			<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<?php endif; ?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<?php if (isset($this->items[0]->state)): ?>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<?php endif; ?>
					  
					<th class='left'>
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_PAYMENTMETHODS_PAYMENT_NAME', 'a.payment_name', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_PAYMENTMETHODS_PAYMENT_METHOD', 'c.name', $listDirn, $listOrder); ?>
					</th>
					<th class="right">
					<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_PAYMENTMETHOD_PRICE', 'a.price', $listDirn, $listOrder); ?>
					</th>
					<th class="right">
					<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_PAYMENTMETHOD_TAX', 'a.tax', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
					<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_PAYMENTMETHOD_TAX_CALCULATION', 'a.tax_calculation', $listDirn, $listOrder); ?>
					</th>			  
					<?php if (isset($this->items[0]->id)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
					<?php endif; ?>
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
			} ?>
			<tr>
				<td colspan="<?php echo $colspan ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$ordering   = ($listOrder == 'a.ordering');
        $canCreate	= $user->authorise('core.create',		'com_qazap');
        $canEdit	= $user->authorise('core.edit',			'com_qazap');
        $canCheckin	= $user->authorise('core.manage',		'com_qazap');
        $canChange	= $user->authorise('core.edit.state',	'com_qazap');
				?>
				<tr class="row<?php echo $i % 2; ?>">
                    
					<?php if (isset($this->items[0]->ordering)): ?>
					<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';
						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
                <?php endif; ?>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
                <?php if (isset($this->items[0]->state)): ?>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'paymentmethods.', $canChange, 'cb'); ?>
					</td>
                <?php endif; ?>
                    
				<td>
				<?php if (isset($item->checked_out) && $item->checked_out) : ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'paymentmethods.', $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=paymentmethod.edit&id='.(int) $item->id); ?>">
					<?php echo $this->escape($item->payment_name); ?></a>
				<?php else : ?>
					<?php echo $this->escape($item->payment_name); ?>
				<?php endif; ?>
				</td>
				<td>

					<?php echo $item->name; ?>
				</td>
				<td class="right">
					<?php echo QazapHelper::currencyDisplay($item->price);?>
				</td>
				<td class="right">
					<?php if($item->tax_calculation == 'p') 
					{
						echo (float) $item->tax;
					}
					else
					{
						echo QazapHelper::currencyDisplay($item->tax);
					} ?>
				</td>
				<td>
					<?php echo ($item->tax_calculation == 'p') ? JText::_('QAZAP_FORM_PERCENTAGE') : JText::_('QAZAP_FORM_VALUE') ?>
				</td>
				
					<?php if (isset($this->items[0]->id)): ?>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
					<?php endif; ?>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif;?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>        

		
