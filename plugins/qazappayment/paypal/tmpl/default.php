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
 * @subpackage Qazappayment Paypal Plugin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die();

$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
jQuery(document).ready(function($){
	$('#paypal-paymentform').submit();
});
");
?>
<div class="payment-redirect-form">
	<h1><?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_REDIRECTING_HEADER') ?></h1>
	<p><?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_REDIRECTING_BODY') ?></p>
	<br/>
	<form action="<?php echo $displayData['url'] ?>" method="post" id="paypal-paymentform" accept-charset="UTF-8">
		<?php foreach($displayData['data'] as $name => $value) : ?>
			<input type="hidden" name="<?php echo $name ?>" value="<?php echo htmlspecialchars($value) ?>" />
		<?php endforeach; ?>
		<input type="submit" class="btn btn" value="<?php echo JText::_('PLG_QAZAPPAYMENT_PAYPAL_REDIRECTING_BUTTON_TEXT') ?>" />
	</form>
</div>