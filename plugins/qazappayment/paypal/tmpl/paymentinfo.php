<?php 
/**
 * paymentinfo.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazappayment Paypal Plugin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die();

$data = $displayData['data'];
$payment = $displayData['payment'];
$ordergroup = $displayData['ordergroup'];
?>
<div class="control-group">
	<div class="control-label"><?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_TRANSACTION_ID') ?></div>
	<div class="controls"><?php echo $data->txn_id ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_PROTECTION_ELIGIBILITY') ?></div>
	<div class="controls"><?php echo $data->protection_eligibility ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_PAYER_EMAIL') ?></div>
	<div class="controls"><?php echo $data->payer_email ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_PAYER_STATUS') ?></div>
	<div class="controls"><?php echo $data->payer_status ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_PAYMENT_STATUS') ?></div>
	<div class="controls"><?php echo $data->payment_status ?></div>
</div>