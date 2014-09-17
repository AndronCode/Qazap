<?php
/**
 * edit_orders.php
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
$orders = $this->ordergroup->vendor_carts;
?>
<?php if(count($orders)) : ?>
	<div class="qazap-orders row-fluid" id="qazap-orders">
		<?php
		$i = 1;
		foreach($orders as $order) : //qzdump($order);exit; ?>	
		<div class="qazap-order-title">
			<a href="#order-id-<?php echo $order->order_id ?>" class="<?php echo $i == 1 ? 'active btn btn-large' : 'btn btn-large' ?>">
				<span class="order-number"><?php echo JText::_('COM_QAZAP_ORDER_NUMBER') ?>: <?php echo $order->order_number ?></span>
				<?php if($i == 1) : ?>
				<div class="qzsidebar-arrow qzarrow-down"></div>
				<?php else : ?>
				<div class="qzsidebar-arrow qzarrow-left"></div>
				<?php endif; ?>
			</a>
		</div>
		<div id="order-id-<?php echo $order->order_id ?>" class="qazap-order-contents <?php echo $i == 1 ? 'opened' : 'closed' ?>">
			<div class="row-fluid">
				<div class="span6">
					<div class="header-inner form-horizontal text-form">
						<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDER_DETAILS') ?></div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_ID') ?></div>
							<div class="controls"><?php echo $order->order_id ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_NUMBER') ?></div>
							<div class="controls"><?php echo $order->order_number ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_VENDOR') ?></div>
							<div class="controls"><?php echo JHTML::link(JRoute::_('index.php?option=com_qazap&task=vendor.edit&id=' . $order->vendor), $order->shop_name, array('target'=>'_blank', 'title'=>$order->shop_name)) ?></div>
						</div>															
					</div>					
				</div>
				<div class="span6 pull-right">
					<div class="header-inner form-horizontal text-form">
						<div class="header-title"><?php echo JText::_('COM_QAZAP_ORDERGROUP_SUMMERY') ?></div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_ITEM_COUNT') ?></div>
							<?php
							$deleted = 0;
							if($product_count = count($order->products)) {
								foreach($order->products as $product) {
									if($product->deleted)
									{
										$deleted++;
									}
								}
							} ?>
							<div class="controls">
								<?php echo $product_count; ?>
								<?php if($deleted) : ?>
									<span class="order-item-delete-count label label-info"> <?php echo JText::_('COM_QAZAP_ORDER_ITEM_DELETE_COUNT') . ': ' . $deleted ?></span>
								<?php endif; ?>
							</div>
						</div>						
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_TOTAL_IN_ORDER_CURRENCY') ?></div>
							<div class="controls"><?php echo QazapHelper::orderCurrencyDisplay($order->Total, $this->ordergroup->order_currency) ?></div>
						</div>
						<?php if($this->ordergroup->order_currency != $this->ordergroup->user_currency)	: ?>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_TOTAL_IN_USER_CURRENCY') ?></div>
							<div class="controls"><?php echo QazapHelper::orderCurrencyDisplay($order->Total, $this->ordergroup->order_currency, $this->ordergroup->user_currency, $this->ordergroup->currency_exchange_rate) ?></div>
						</div>
						<?php endif; ?>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_COMMISSION') ?></div>
							<div class="controls"><?php echo QazapHelper::orderCurrencyDisplay($order->getCommission(), $this->ordergroup->order_currency); ?></div>
						</div>						
						<div class="control-group">
							<div class="control-label"><?php echo JText::_('COM_QAZAP_ORDER_STATUS') ?></div>
							<div class="controls">
								<?php echo QazapHelper::orderStatusNameByCode($order->order_status) ?>
								<span>&nbsp;<a href="#update-order-status-<?php echo $order->order_id ?>" class="btn btn-small btn-success pull-right fancybox-popup"><i class="qzicon-pencil2"></i>&nbsp;<?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_EDIT')?></a></span>
								<div class="hide">
									<div id="update-order-status-<?php echo $order->order_id ?>">
										<?php echo $this->renderOrderStatusUpdateForm($order); ?>
									</div>
								</div>								
							</div>
						</div>															
					</div>					
				</div>				
			</div>
			<table class="table table-bordered table-hover">
				<thead>
					<tr>
						<th width="2%" class="center">
							<?php echo JText::_ ('#') ?>
						</th>					
						<th width="30%">
							<?php echo JText::_ ('COM_QAZAP_ORDER_ITEM_NAME') ?>
						</th>
						<th class="center">
							<?php echo JText::_ ('COM_QAZAP_PRODUCT_SKU') ?>
						</th>
						<th class="right">
							<?php echo JText::_ ('COM_QAZAP_BASEPRICE') ?>
						</th>
						<th class="center" width="15%">
							<?php echo JText::_ ('COM_QAZAP_QUANTITY_UPDATE_DELETE') ?>
						</th>
						<th class="center" width="15%">
							<?php echo JText::_ ('COM_QAZAP_ORDER_ITEM_STATUS') ?>
						</th>						
						<th class="right">
							<?php echo JText::_ ('COM_QAZAP_TAX_AMOUNT') ?>
						</th>
						<th class="right">
							<?php echo JText::_ ('COM_QAZAP_DISCOUNT') ?>
						</th>
						<th class="right">
							<?php echo JText::_ ('COM_QAZAP_TOTAL') ?>
						</th>						
					</tr>
				</thead>
				<?php if(count($order->products)) : ?>
				<tbody>
					<?php 
					$index = 1;
					foreach($order->products as $product) : ?>
					<tr <?php echo $product->deleted ? 'class="deleted"' : '' ?>>
						<td class="center">
							<?php echo $index ?>
						</td>
						<td>
							<?php echo JHTML::link(JRoute::_('index.php?option=com_qazap&task=product.edit&product_id=' . $product->product_id), $product->product_name, array('target'=>'_blank', 'title'=>$product->product_name)) ?>
							<?php if($varients = $product->getVarients()) : ?>
								<div class="product-varients"><?php echo $varients ?></div>
							<?php endif; ?>
							<?php if($this->params->get('downloadable')) : ?>
								<div class="product-download-info">
								<?php if($product->download_id > 0) : ?>
									<div class="download-name">
										<?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_DOWNLOADABLE_FILE') . ': ' . $product->downloadable_file ?>
									</div>
									<div class="start-date">
										<?php echo JText::_('COM_QAZAP_DOWNLOAD_START_DATE') . ': ' . QazapHelper::displayDate($product->download_start_date) ?>
									</div>
									<div class="expiry-date">
										<?php
										$validity = (int) $this->params->get('download_validity', 0);
										if($validity > 0) 
										{
											$date = JFactory::getDate(($product->download_start_date . '+ 1 days'), 'UTC');
											$expiryDate =  $date->format('Y-m-d H:i:s', true, false);
											echo JText::_('COM_QAZAP_DOWNLOAD_EXPIRY_DATE') . ': ' . QazapHelper::displayDate($expiryDate);
										}
										else
										{
											echo JText::_('COM_QAZAP_DOWNLOAD_EXPIRY_DATE') . ': ' . JText::_('COM_QAZAP_DOWNLOAD_LIFETIME');
										} ?>
									</div>
									<div class="download-count">
										<?php echo JText::_('COM_QAZAP_DOWNLOAD_COUNT') . ': ' . (int) $product->download_count ?>
									</div>
									<div class="last-downloaded">
										<?php echo JText::_('COM_QAZAP_LAST_DOWNLOAD_DATE') . ': ' . QazapHelper::displayDate($product->last_download) ?>
									</div>
									<div class="downloads-left">
										<?php
										$limit = (int) $this->params->get('download_limit', 0);
										if($limit > 0) 
										{
											$download_left = ($limit - (int) $product->download_count);
											echo JText::_('COM_QAZAP_DOWNLOADS_LEFT') . ': ' . $download_left;
										}
										else
										{
											echo JText::_('COM_QAZAP_DOWNLOADS_LEFT') . ': ' . JText::_('COM_QAZAP_DOWNLOAD_NOLIMIT');
										} ?>										
									</div>
									<div class="download-link">
										<?php 
										$download_url = JUri::root() . 'index.php?option=com_qazap&view=download&download_id=' . $product->download_id . '&passcode=' . $product->download_passcode;
										echo JHTML::link($download_url, JText::_('COM_QAZAP_LAST_DOWNLOAD_LINK'), array('target'=>'_blank', 'title'=>JText::_('COM_QAZAP_LAST_DOWNLOAD_LINK'))) ?>
									</div>																							
								<?php else : ?>
									<div class="no-download-file"><?php echo JText::_('COM_QAZAP_PRODUCT_MISSING_DOWNLOADABLE_FILE') ?></div>
								<?php endif; ?>
								</div>						
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo $product->product_sku ?>
						</td>	
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($product->product_basepricewithVariants, $this->ordergroup->order_currency) ?>
						</td>
						<td class="center">
							<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $this->ordergroup->ordergroup_id) ?>" class="qazap-qty-update qazap-inline-form form-inline"  method="post">
								<div class="input-append">
									<input type="text" name="quantity" class="inputbox input-mini" value="<?php echo $product->product_quantity ?>" <?php echo $product->deleted ? 'readonly="readonly"' : '' ?> />
									<input type="hidden" name="option" value="com_qazap"/>
									<input type="hidden" name="group_id" value="<?php echo $product->group_id ?>" />
									<input type="hidden" name="task" value="order.updateItemQuantity" />
									<?php echo JHtml::_('form.token'); ?>
									<button type="submit" class="qazap-qty-update-button btn btn-success" title="<?php echo JText::_('JAPPLY') ?>" <?php echo $product->deleted ? 'disabled="disabled"' : '' ?>>
										<?php echo JText::_('JAPPLY') ?>
									</button>																		
								</div>						
							</form>
							<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $this->ordergroup->ordergroup_id) ?>" class="qazap-product-remove qazap-inline-form form-inline" method="post">
								<button type="submit" class="qazap-remove-button btn" title="<?php echo  JText::_('COM_QAZAP_REMOVE_PRODUCT_CART') ?>" <?php echo $product->deleted ? 'disabled="disabled"' : '' ?>>
									<i class="icon-trash red"></i>
								</button>
								<input type="hidden" name="option" value="com_qazap"/>
								<input type="hidden" name="group_id" value="<?php echo $product->group_id ?>" />
								<input type="hidden" name="task" value="order.deleteItem" />
								<?php echo JHtml::_('form.token'); ?>									
							</form>						
						</td>	
						<td class="left">
							<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id=' . (int) $this->ordergroup->ordergroup_id) ?>" class="qazap-product-remove qazap-inline-form form-inline" method="post">
								<div class="input-append">
									<?php echo $this->renderItemStatusField($product->order_status, $product->deleted) ?>									
									<input type="hidden" name="option" value="com_qazap"/>
									<input type="hidden" name="group_id" value="<?php echo $product->group_id ?>" />
									<input type="hidden" name="task" value="order.updateItemStatus" />
									<?php echo JHtml::_('form.token'); ?>
									<button type="submit" class="qazap-item-status-update-button btn btn-success" title="<?php echo  JText::_('JAPPLY') ?>" <?php echo $product->deleted ? 'disabled="disabled"' : '' ?>>
										<?php echo  JText::_('JAPPLY') ?>
									</button>																
								</div>
							</form>
						</td>							
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($product->total_tax, $this->ordergroup->order_currency) ?>
						</td>	
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($product->total_discount, $this->ordergroup->order_currency) ?>
						</td>	
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($product->product_totalprice, $this->ordergroup->order_currency) ?>
						</td>					
					</tr>
					<?php 
					$index++;
					endforeach; ?>
					<!--Display products subtotal-->
					<tr>
						<td colspan="6" class="right">
							<?php echo JText::_('COM_QAZAP_PRODUCT_SUBTOTAL') ?>
						</td>
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($order->productTotalTax, $this->ordergroup->order_currency) ?>
						</td>
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($order->productTotalDiscount, $this->ordergroup->order_currency) ?>
						</td>
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($order->totalProductPrice, $this->ordergroup->order_currency) ?>
						</td>										
					</tr>
					<!--Display coupon discount-->
					<?php if($order->coupon_code) : ?>
					<tr>
						<td colspan="6" class="right">
							<?php echo $order->coupon_data->html ?>
						</td>
						<td></td>
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($order->coupon_discount, $this->ordergroup->order_currency) ?>
						</td>
						<td></td>										
					</tr>
					<?php endif; ?>			
					<!--Display cart discount before tax rules-->
					<?php if(count($order->CartDiscountBeforeTaxInfo)) : ?>
						<?php foreach($order->CartDiscountBeforeTaxInfo as $dbt) : ?>
							<tr>
								<td colspan="6" class="right">
									<?php echo JText::_($dbt->name) ?>
								</td>
								<td></td>
								<td class="right">
									<?php echo QazapHelper::orderCurrencyDisplay($dbt->total, $this->ordergroup->order_currency) ?>
								</td>
								<td></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display cart tax rules-->
					<?php if(count($order->CartTaxInfo)) : ?>
						<?php foreach($order->CartTaxInfo as $tax) : ?>
							<tr>
								<td colspan="6" class="right">
									<?php echo JText::_($tax->name) ?>
								</td>							
								<td class="right">
									<?php echo QazapHelper::orderCurrencyDisplay($tax->total, $this->ordergroup->order_currency) ?>
								</td>
								<td></td>
								<td></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display cart tax rules-->
					<?php if(count($order->CartDiscountAfterTaxInfo)) : ?>
						<?php foreach($order->CartDiscountAfterTaxInfo as $dat) : ?>
							<tr>
								<td colspan="6" class="right">
									<?php echo JText::_($dat->name) ?>
								</td>	
								<td></td>													
								<td class="right">
									<?php echo QazapHelper::orderCurrencyDisplay($dat->total, $this->ordergroup->order_currency) ?>
								</td>
								<td></td>												
							</tr>
						<?php endforeach; ?>				
					<?php endif; ?>
					<!--Display vendor cart total-->
					<tr class="success qazap-vendor-total">
						<td colspan="6" class="right">
							<?php echo JText::_('COM_QAZAP_ORDER_TOTAL') ?>
						</td>	
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($order->TotalTax, $this->ordergroup->order_currency) ?>
						</td>													
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($order->TotalDiscount, $this->ordergroup->order_currency) ?>
						</td>
						<td class="right">
							<?php echo QazapHelper::orderCurrencyDisplay($order->Total, $this->ordergroup->order_currency) ?>
						</td>												
					</tr>						
				</tbody>				
				<?php endif; ?>			
			</table>


		</div>
		<?php 
		$i++;
		endforeach; ?>	
	</div>
<?php endif; ?>