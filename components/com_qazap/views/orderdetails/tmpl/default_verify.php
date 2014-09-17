<?php
/**
 * default_verify.php
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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$doc = JFactory::getDocument();
?>
<div class="orderdetails-validate">
	<div class="qz-page-header">
		<h2><?php echo JText::_('COM_QAZAP_ORDERDETAILS_VALIDATE') ?></h2>
	</div>
	<form name="orderdetailsForm" id="orderdetails-validate-form" action="<?php echo JRoute::_(QazapHelperRoute::getOrderdetailsRoute($this->orderDetails->ordergroup_id)); ?>" method="post"  class="form-horizontal form-validate">
	  <div class="control-group">
	    <label class="control-label hasTooltip" for="orderdetails_email" title="<?php echo JText::_('COM_QAZAP_ORDERDETAILS_EMAIL_DESC') ?>">
	    	<?php echo JText::_('COM_QAZAP_EMAIL') ?>:<span class="star">&nbsp;*</span>	    		
			</label>
	    <div class="controls">
	      <input type="email" id="orderdetails_email" name="email" value="" placeholder="<?php echo JText::_('COM_QAZAP_EMAIL') ?>" required="required"/> 
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label hasTooltip" for="orderdetails_accesskey" title="<?php echo JText::_('COM_QAZAP_ORDERGROUP_ACCESS_KEY_DESC') ?>">
	    	<?php echo JText::_('COM_QAZAP_ORDERGROUP_ACCESS_KEY_LABEL') ?>:<span class="star">&nbsp;*</span>	    		
			</label>
	    <div class="controls">
	      <input type="text" id="orderdetails_access_key" name="access_key" value="" placeholder="<?php echo JText::_('COM_QAZAP_ORDERGROUP_ACCESS_KEY_LABEL') ?>" required="required" /> 
	    </div>
	  </div>	
		<div class="control-group">
		  <div class="controls">
		    <button type="submit" class="btn btn-primary validate"><?php echo JText::_('COM_QAZAP_GLOBAL_SUBMIT') ?></button>
		  </div>
		</div>	
		<input type="hidden" name="task" value="orderdetails.validate" />
		<input type="hidden" name="option" value="com_qazap" />	
		<?php echo JHtml::_('form.token'); ?>	
	</form>
</div>