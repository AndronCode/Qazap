<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;
JHtml::_('behavior.caption');
JHtml::_('behavior.framework');
QZApp::loadCSS();			
QZApp::loadJS();
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');

if($this->isVendor): 
$return = base64_encode(QazapHelperRoute::getSellerRoute('productlist'));
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
		<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_SELLER_PRODUCT_LIST') ?></h2>		
	</div>
				
	<?php if(!$this->activeVendor):?>
		<div class="alert alert-warning">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo JText::_('COM_QAZAP_NEED_APPROVAL_TO_EDIT_OR_ADD_PRODUCT') ?>
		</div>
	<?php endif;?>	
	
	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
		<fieldset class="filters btn-toolbar clearfix">
			<?php if($this->activeVendor) : ?>
			<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=product&task=product.add&return='. $return) ?>" class="btn btn-success">
				<?php echo JText::_('COM_QAZAP_ADD_NEW_PRODUCT') ?>		
			</a>
			<?php endif; ?>
			<div class="input-append pull-right">
			  <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="inputbox" onchange="this.form.submit();" placeholder="<?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?>" />	
			  <button class="btn hasTooltip" type="submit" title="<?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?>"><?php echo JText::_('COM_QAZAP_SEARCH_FILTER_LABEL'); ?></button>
			  <button class="btn hasTooltip" type="button" onclick="this.form.getElementById('filter-search').value='';this.form.submit();" title="<?php echo JText::_('COM_QAZAP_GLOBAL_CLEAR'); ?>"><?php echo JText::_('COM_QAZAP_GLOBAL_CLEAR'); ?></button>
			</div>
		</fieldset>
		<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th><?php echo JHtml::_('grid.sort', 'COM_QAZAP_PRODUCT_NAME', 'product_name', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', 'COM_QAZAP_PRODUCT_CATEGORY_NAME', 'cd.title', $listDirn, $listOrder); ?></th>
					<th class="right"><?php echo JText::_('COM_QAZAP_PRODUCT_SALES_PRICE'); ?></th>

					<th><?php echo JHtml::_('grid.sort', 'COM_QAZAP_PRODUCT_CREATED_DATE', 'p.created_time', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
			<?php 
				if(empty($this->products)):
					echo JText::_('COM_QAZAP_ORDER_NO_PRODUCT_LISTED');
				else:
					$i = 1;
					foreach($this->products as $product):
			?>
			<tbody>
				<tr>
					<td>
						<?php echo $i++?>
					</td>
					<td>
						<?php if(!$this->activeVendor) : ?>
							<span class="text-disabled hasTooltip" title="<?php echo JText::_('COM_QAZAP_NEED_APPROVAL_TO_EDIT_OR_ADD_PRODUCT') ?>">
								<?php echo $product->product_name; ?>									
							</span>
						<?php else : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=product&task=product.edit&product_id='.$product->product_id).'&return='.$return ?>">
								<?php echo $product->product_name?>								
							</a>
						<?php endif;?>
						&nbsp;
						<?php if(!$product->block) : ?>
							<span class="label label-success">Approved</span>
						<?php else : ?>
							<span class="label hasTooltip" title="This product is not yet approved.">Pending</span>
						<?php endif; ?>
					</td>
					<td>
						<?php echo $product->category_name;?>
					</td>
					<td class="right">
						<?php 
						$sales_price = QZHelper::getFinalPrice($product,'product_salesprice');
						echo QZHelper::currencyDisplay($sales_price);?>
					</td>
					<td>
						<?php echo QZHelper::displayDate($product->created_time);?>
					</td>
				</tr>
				
			</tbody>
			<?php
					endforeach; 
				endif;
			?>
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
	<?php endif;?>
	
</div>
