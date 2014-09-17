<?php
/**
 * contact_form.php
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

$product			= $displayData['product'];
$params				= $displayData['params'];	
$product_url	= $displayData['product_url'];
$user					= $displayData['user'];
?>
<div class="qazap-popup">
	<div class="qazap-popup-title">
		<h3><?php echo JText::_('COM_QAZAP_CONTACT_FOR_PRICE') ?></h3>
	</div>	
	<form name="qazap-notify-form" class="form-vertical" action="<?php echo JRoute::_($product_url)?>" method="post">
		<div class="qazap-popup-content">
			<div class="control-group">
				<label class="control-label" for="qzform_user_name1"><?php echo JText::_('COM_QAZAP_CONTACT_NAME') ?></label>
				<div class="controls">
					<input type="text" name="qzform[user_name]" id="qzform_user_name1" value="<?php echo $user->name?>" placeholder="<?php echo JText::_('COM_QAZAP_CONTACT_NAME') ?>" required="true" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="qzform_user_email1"><?php echo JText::_('COM_QAZAP_CONTACT_EMAIL') ?></label>
				<div class="controls">
					<input type="email" name="qzform[user_email]" id="qzform_user_email1" value="<?php echo $user->email?>" placeholder="<?php echo JText::_('COM_QAZAP_CONTACT_EMAIL') ?>" required="true" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="qzform_question1"><?php echo JText::_('COM_QAZAP_CONTACT_COMMENT') ?></label>
				<div class="controls">
					<textarea name="qzform[question]" id="qzform_question1" rows="5" required="true"></textarea>
				</div>
			</div>
	  </div>
		<div class="qazap-popup-footer">
			<button type="button" class="qazap-popup-close btn"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?></button>	
			<button type="submit" class="btn btn-primary"><?php echo JText::_('JSUBMIT') ?></button>					
	  </div> 	  
		<input type="hidden" name="option" value="com_qazap"/>
		<input type="hidden" name="task" value="product.askquestion" />
		<input type="hidden" name="return" value="<?php echo base64_encode($product_url) ?> "/>
		<input type="hidden" name="qzform[product_id]" value="<?php echo $product->product_id ?>" />
		<input type="hidden" name="qzform[vendor_id]" value="<?php echo $product->vendor ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

