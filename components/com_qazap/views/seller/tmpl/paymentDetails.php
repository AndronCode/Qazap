<?php
/**
 * paymentDetails.php
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
JHtml::_('behavior.caption');
JHtml::_('behavior.framework');
QZApp::loadJS();
QZApp::loadCSS();
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction'); 
echo $this->menu;
if($this->isVendor): 
?>
<div class="seller-<?php echo $this->layout . $this->pageclass_sfx ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<header class="qz-page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
			<?php if($this->isVendor) : ?>
				<?php if(!$this->activeVendor) : ?>
					<span class="label label-important toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_UNAPPROVED')?></span>
				<?php else : ?>
					<span class="label label-success toupper"><?php echo JText::_('COM_QAZAP_GLOBAL_APPROVED')?></span>
				<?php endif; ?>
			<?php endif;?>			
		</h1>
	</header>
	<?php endif; ?>

	<table class="table table-striped">
		<tbody>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_ID') ?></td>
				<td><?php echo $this->paymentDetails->payment_id ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('JDATE')?></td>
				<td><?php echo QZHelper::displayDate($this->paymentDetails->date);?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_METHOD')?></td>
				<td>
					<?php
						if(!empty($this->paymentDetails->payment_method)):
							echo $this->paymentDetails->method_name;
						else:
							echo JText::_('COM_QAZAP_MANUAL_PAYMENT');
						endif;
					?>
				</td>
			</tr>			
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_STATUS')?></td>
				<td><?php echo $this->paymentDetails->payment_status ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_ORDER_VALUE')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->total_order_value) ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_CONFIRMED_ORDER_VALUE')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->total_confirmed_order) ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_TOTAL_COMMISSION')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->total_commission_value) ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_CONFIRMED_COMMISSION')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->total_confirmed_commission) ?></td>
			</tr>	
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_LAST_PAYMENT_AMOUNT')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->last_payment_amount) ?></td>
			</tr>	
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_LAST_PAYMENT_DATE')?></td>
				<td><?php echo QZHelper::displayDate($this->paymentDetails->last_payment_date) ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_TOTAL_PAID_AMOUNT')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->total_paid_amount) ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_TOTAL_BALANCE')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->total_balance) ?></td>
			</tr>
			<tr>
				<td><strong><?php echo JText::_('COM_QAZAP_PAYMENT_AMOUNT')?></strong></td>
				<td><strong><?php echo QZHelper::currencyDisplay($this->paymentDetails->payment_amount) ?></strong></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_QAZAP_PAYMENT_BALANCE')?></td>
				<td><?php echo QZHelper::currencyDisplay($this->paymentDetails->balance) ?></td>
			</tr>																			
		</tbody>
	</table>
	<?php endif;?>
</div>		