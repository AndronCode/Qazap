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
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
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

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=shops'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if(!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
		<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped" id="shoplist">
					<thead>
						<th class='left'>
							<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_SHOP_LANGUAGE', 'a.lang', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_SHOP_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_SHOP_MODIFIED_BY', 'a.country_3_code', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_MODIFIED_ON', 'a.country_2_code', $listDirn, $listOrder); ?>
						</th>
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
						<?php foreach ($this->items as $item) :?>
						<tr>
							<td>
							<?php if($item->lang == '*'):?>
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=shop&layout=edit'); ?>">
								<?php echo JText::_('JALL')?></a>
							
							<?php else: ?>
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&task=shop.edit&layout=edit&lang='.$item->lang); ?>">											<?php echo $item->lang?></a>
							
							<?php endif;?>		
					
							</td>
							<td>
							<?php echo $this->escape($item->name); ?>					
							</td>
							<td>
							<?php echo $item->name;?>					
							</td>
							<td>
							<?php echo QZHelper::displayDate($item->modified_time, 'd-m-Y H:i:s');?>					
							</td>
						</tr>
						<?php endforeach;?>		
					</tbody>
				</table>
			
			<?php endif;?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>