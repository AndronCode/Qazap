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

$user	= JFactory::getUser();
$userId	= $user->get('id');
$fullOrdering = $this->state->get('list.fullordering') ? explode(' ', $this->state->get('list.fullordering')) : array('a.ordering', 'ASC');
$listOrder	= $this->escape($fullOrdering[0]);
$listDirn	= $this->escape($fullOrdering[1]);
$ordering 	= ($listOrder == 'a.ordering');
$saveOrder 	= ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$originalOrders = array();
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_qazap&task=taxes.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'taxList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
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

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=taxes'); ?>" method="post" name="adminForm" id="adminForm">
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
		<table class="table table-striped" id="taxeList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>               
					<th class="left">
					<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_TAXES_CALCULATION_RULE_NAME', 'a.calculation_rule_name', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_TAXES_TYPE_OF_ARITHMATIC_OPERATION', 'a.type_of_arithmatic_operation', $listDirn, $listOrder); ?>
					</th>
					<th class="right">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_TAXES_VALUE', 'a.value', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_TAX_MATH_OPERATION', 'a.math_operation', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>			
				<?php $colspan = isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 8; ?>
				<tr>
					<td colspan="<?php echo $colspan ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach($this->items as $i => $item) :
				$ordering   = ($listOrder == 'a.ordering');
				$canCreate	= $user->authorise('core.create',		'com_qazap');
				$canEdit	= $user->authorise('core.edit',			'com_qazap');
				$canCheckin	= $user->authorise('core.manage',		'com_qazap');
				$canChange	= $user->authorise('core.edit.state',	'com_qazap');
				?>
				<tr class="row<?php echo $i % 2; ?>">                    
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
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'taxes.', $canChange, 'cb'); ?>
					</td>                
					<td>
						<?php if (isset($item->checked_out) && $item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'taxes.', $canCheckin); ?>
						<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=tax.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->calculation_rule_name); ?>		
						</a>
					<?php else : ?>
						<?php echo $this->escape($item->calculation_rule_name); ?>
					<?php endif; ?>
					</td>
					<td>
						<?php echo $this->getOperation($item->type_of_arithmatic_operation); ?>
					</td>
					<td class="right nowrap">
						<?php echo $item->value; ?>
					</td>
					<td class="center">
						<?php echo ucfirst($item->math_operation); ?>
					</td>	
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>