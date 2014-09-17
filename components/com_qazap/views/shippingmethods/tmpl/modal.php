<?php
/**
 * modal.php
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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
QZApp::loadCSS();
QZApp::loadJS();

// Import CSS
$function = JFactory::getApplication()->input->getCmd('function', 'jSelectShipment');
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_qazap&task=shippingmethods.saveOrderAjax&layout=modal&tmpl=component';
	JHtml::_('sortablelist.sortable', 'shipmentmethodList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
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

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=shippingmethods&layout=modal&tmpl=component'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('COM_QAZAP_GLOBAL_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn hasTooltip" type="button" title="<?php echo JText::_('COM_QAZAP_GLOBAL_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('COM_QAZAP_FIELD_ORDERING_DESC');?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('COM_QAZAP_FIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_QAZAP_GLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_QAZAP_GLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('COM_QAZAP_GLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('COM_QAZAP_GLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>        
		<div class="clearfix"> </div>
		<table class="table table-striped" id="shipmentmethodList">
			<thead>
				<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('COM_QAZAP_GLOBAL_CHCK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
                
				<th class="left">
				<?php echo JHtml::_('grid.sort',  'COM_QAZAP_SHIPMENTMETHODS_SHIPMENT_NAME', 'a.shipment_name', $listDirn, $listOrder); ?>
				</th>
				<th class="left">
				<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_SHIPMENTMETHODS_SHIPMENT_NAME', 'a.shipment_name', $listDirn, $listOrder); ?>
				</th>
				<th class="right">
				<?php echo JHtml::_('grid.sort',  'COM_QAZAP_FORM_LBL_SHIPMENTMETHOD_PRICE', 'a.price', $listDirn, $listOrder); ?>
				</th>
				<th class="right">
				<?php echo JHtml::_('grid.sort',  'COM_QAZAP_FORM_LBL_SHIPMENTMETHOD_TAX', 'a.tax', $listDirn, $listOrder); ?>
				</th>
				<th class="left">
				<?php echo JHtml::_('grid.sort',  'COM_QAZAP_FORM_LBL_SHIPMENTMETHOD_TAX_CALCULATION', 'a.tax_calculation', $listDirn, $listOrder); ?>
				</th>
				<?php if (isset($this->items[0]->id)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
                <?php endif; ?>
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
				$ordering   = ($listOrder == 'a.ordering');
                $canCreate	= $user->authorise('core.create',		'com_qazap');
                $canEdit	= $user->authorise('core.edit',			'com_qazap');
                $canCheckin	= $user->authorise('core.manage',		'com_qazap');
                $canChange	= $user->authorise('core.edit.state',	'com_qazap');
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>                    
					<td class="qazap_shipment_name">
						<?php echo $this->escape($item->shipment_name); ?>
					</td>
					<td>
						<?php echo $item->name;?>
					</td>
					<td class="right">
						<?php echo $item->price;?>
					</td>
					<td class="right">
						<?php echo $item->tax;?>
					</td>
					<td>
						<?php echo ($item->tax_calculation == 'p') ? JText::_('COM_QAZAP_PERCENT') : JText::_('COM_QAZAP_VALUE') ?>
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

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<button type="button" onclick="if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');} else if(window.parent){ return window.returnParentValues(this);}" class="btn btn-small">
		<span class="icon-publish"></span>Submit
	</button>		
</form>        

<?php
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
function returnParentValues(btn) {
	var form = jQ(btn).parents('form');
	var val = [];
	var name = [];
	form.find(':checkbox:checked').each(function(i){
		name[i] = jQ(this).parents('tr').find('.qazap_shipment_name').text();
		name[i] = jQ.trim(name[i]);
		val[i] = jQ(this).val();
	});
	if(val.length) {
	  var newArray = new Array();
	  for(var i = 0; i< val.length; i++){
	      if (val[i]){
	        newArray.push(val[i]);
	    }
	  }
	  val = newArray;	
	  
	  var newArray = new Array();
	  for(var i = 0; i< name.length; i++){
	      if (name[i]){
	        newArray.push(name[i]);
	    }
	  }
	  name = newArray;	  	
	}		
	if(val.length && name.length)
	{
		var names = name.join(', ');
		var values = val.join(',');
		if (window.parent) {
			window.parent.".$this->escape($function)."(values, names);
		}				
	}	
	return false;
}
");
?>