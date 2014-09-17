<?php
/**
 * default_notify.php
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

?>
<div class="qazap-popup">
	<div class="qazap-popup-title">
		<h3><?php echo JText::_('COM_QAZAP_NOTIFY_ME') ?></h3>
	</div>	
	<form name="qazap-notify-form" class="form-inline" action="<?php echo JRoute::_($this->product_url)?>" method="post">
		<div class="qazap-popup-content">
			<div class="input-prepend">
				<span class="add-on">@</span>
		  	<input type="email" name="qzform[user_email]" id="qzform_user_email" value="<?php echo $this->user->email?>" placeholder="<?php echo JText::_('JGLOBAL_EMAIL') ?>" required="true" />
		  </div>
	  </div>
		<div class="qazap-popup-footer">
			<button type="button" class="qazap-popup-close btn"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE') ?></button>	
			<button type="submit" class="btn btn-primary"><?php echo JText::_('JSUBMIT') ?></button>					
	  </div> 	  
		<input type="hidden" name="option" value="com_qazap"/>
		<input type="hidden" name="task" value="product.notify" />
		<input type="hidden" name="return" value="<?php echo base64_encode($this->product_url) ?> "/>
		<input type="hidden" name="qzform[product_id]" value="<?php echo $this->item->product_id ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>



