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
$app = JFactory::getApplication('administrator');
$lang = JFactory::getLanguage();
$present_language = $lang->getTag();
$model = $this->getModel();

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
$saveOrder	= $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_qazap&task=products.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'productList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
$originalOrders = array();
if (!empty($this->extra_sidebar)) 
{
	$this->sidebar .= $this->extra_sidebar;
}
$formURL = 'index.php?option=com_qazap&view=products';
$vendor_id = (int) $this->state->get('filter.vendor_id');
if($vendor_id > 0)
{
	$formURL .= '&vendor_id=' . $vendor_id;
}
else
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

<form action="<?php echo JRoute::_($formURL); ?>" method="post" name="adminForm" id="adminForm">
	<?php if(!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
		<?php endif;?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));	?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>         
		<div class="clearfix"> </div>
		<table class="table table-striped" id="productList">
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
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_PRODUCT_NAME', 'b.product_name', $listDirn, $listOrder); ?>					
					</th>
					<th width="10%" class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_PRODUCT_VENDOR', 'v.shop_name', $listDirn, $listOrder); ?>
					</th>
					<th class="right" width="10%">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE', 'product_baseprice', $listDirn, $listOrder); ?>
					</th>
					<th class="right" width="10%" style="color: #777">
						<?php echo  JText::_('COM_QAZAP_FORM_PRODUCT_SALESPRICE') ?>
					</th>										
					<?php if($this->params->get('enablestockcheck')) : ?>
					<th width="10%" class="center">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_LIST_LBL_PRODUCT_AVAILABILITY', 'availability', $listDirn, $listOrder); ?>
					</th>
					<?php endif; ?>			
					<th width="2%" class="center">
						<?php echo JHtml::_('searchtools.sort',  'JFIELD_ACCESS_LABEL', 'j.title', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap center hidden-phone">						
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>					                  
					<th width="1%" class="nowrap center hidden-phone">						
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.product_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
      <?php if(isset($this->items[0])) 
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
						<?php echo JHtml::_('grid.id', $i, $item->product_id); ?>
					</td>
					<?php if (isset($this->items[0]->state)): ?>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, 'products.', $canChange, 'cb'); ?>
							<?php echo JHtml::_('productadministrator.featured', $item->featured, $i, $canChange); ?>
							<?php echo JHtml::_('productadministrator.block', $item->block, $i, $canChange); ?>
						</div>
					</td>
					<?php endif; ?>              
					<td class="left">
					<?php 
					$product_name = !empty($item->product_name) ? $this->escape($item->product_name) : '<span class="label label-important">'.JText::_('COM_QAZAP_FIELD_NAME_NOT_MAINTAINED').'</span>';
					if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'products.', $canCheckin);?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=product.edit&product_id='.(int) $item->product_id); ?>">
						<?php echo $product_name; ?></a>
					<?php else : ?>
						<?php echo $product_name; ?>
					<?php endif; ?>
					<div class="clear"></div>
					<div class="small">
						<?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_CATEGORY').':&nbsp;'. $this->escape($item->category_name) ?>
					</div>
					<?php if($item->children_count) : ?>
						<div class="small">Children: <?php echo $item->children_count?></div>					
						<?php $children = $model->getChildren($item->product_id); ?>
						<div class="child-products small">
						<?php foreach($children as $key=>$child) : ?>
							<?php if($key>0) : ?>
								&nbsp;|&nbsp;
							<?php endif; ?>
							<span>
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=product.edit&product_id='.(int) $child->product_id); ?>">
									<?php echo $child->product_name; ?>
								</a>
							</span>
						<?php endforeach; ?>
						</div>				
					<?php endif; ?>
					</td>
					<td class="left small">
						<?php echo $this->escape($item->shop_name); ?>
					</td>
					<td class="right nowrap small">
						<?php echo QazapHelper::currencyDisplay($item->product_baseprice); ?>
					</td>						
					<td class="right nowrap small">
						<?php echo QazapHelper::currencyDisplay(QazapHelper::getFinalPrice($item, 'product_salesprice')); ?>
					</td>					
					<?php if($this->params->get('enablestockcheck')) : ?>
					<td class="center small">
						<?php echo $item->availability ? JText::_('JYES') : JText::_('JNO') . '<br/><div class="label label-info">'.JText::sprintf('COM_QAZAP_PRODUCT_NOTIFY_COUNT',$item->notify_count).'</div>'; ?>
					</td>
					<?php endif; ?>		
					<td class="center small">
						<?php echo $item->access_name; ?>
					</td>
					<td class="center small">
						<?php echo (int) $item->hits ?>
					</td>					
					<td class="center hidden-phone">
						<?php echo (int) $item->product_id; ?>
					</td>
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

		
