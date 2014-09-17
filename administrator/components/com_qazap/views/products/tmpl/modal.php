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
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

$app = JFactory::getApplication();

if ($app->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

require_once JPATH_ROOT . '/components/com_qazap/helpers/route.php';

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.framework', true);

$user = JFactory::getUser();
$function  = $app->input->getCmd('function', 'jSelectProduct');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$originalOrders = array();
?>


<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=products&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
    	<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>         
		<div class="clearfix"> </div>
		<table class="table table-striped" id="productList">
			<thead>
				<tr>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_ACTIVATION_STATE', 'a.block', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_FORM_LBL_PRODUCT_NAME', 'b.product_name', $listDirn, $listOrder); ?>					
					</th>

					<th width="2%" class="center">
						<?php echo JHtml::_('searchtools.sort',  'JFIELD_ACCESS_LABEL', 'j.title', $listDirn, $listOrder); ?>
					</th>
					<th class="right" width="8%">
						<?php echo  JText::_('COM_QAZAP_FORM_LBL_PRODUCT_FINALPRICE') ?>
					</th>			                  
					<th width="1%" class="nowrap center hidden-phone">						
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.product_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
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
					<?php if (isset($this->items[0]->state)): ?>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'products.', $canChange, 'cb'); ?>
					</td>
					<?php endif; ?>
					<?php if (isset($this->items[0]->block)): ?>
					<td class="center">
						<?php 
						$states = array(1 => array('block', 'COM_QAZAP_ACTIVE', 'COM_QAZAP_BLOCK_PRODUCT', 'COM_QAZAP_ACTIVE', true, 'publish', 'publish'),
										0 => array('activate', 'COM_QAZAP_INACTIVE', 'COM_QAZAP_BLOCK_UNPRODUCT', 'COM_QAZAP_INACTIVE', true, 'unpublish', 'unpublish'));
						echo JHtml::_('jgrid.state', $states, $item->block, $i, 'products.', $canChange, true, 'cb'); ?>
					</td>
					<?php endif; ?>                   
					<td class="left">
						<?php 
						$product_name = !empty($item->product_name) ? $this->escape($item->product_name) : '<span class="label label-important">'.JText::_('COM_QAZAP_FIELD_NAME_NOT_MAINTAINED').'</span>';?>
						
						<a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->product_id; ?>', '<?php echo $this->escape(addslashes($product_name)); ?>', '<?php echo $this->escape($item->category_id); ?>', null, null, null, null);">					
							<?php echo $product_name; ?>
						</a>

						<div class="clear"></div>
						<div class="small">
							<?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_CATEGORY').':&nbsp;'.$item->category_name ?>
						</div>
					</td>
					<td class="center">
						<?php echo $item->access_name; ?>
					</td>
					<td class="right nowrap">
						<?php echo QazapHelper::currencyDisplay(QazapHelper::getFinalPrice($item->product_baseprice, $item->product_id, 'product_salesprice')); ?>
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

		
