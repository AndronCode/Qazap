<?php
/**
 * default_coupon.php
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

// no direct access
defined('_JEXEC') or die;

//QZApp::dump($this->cart);exit;
?>
<div class="row-fluid">
	<form id="order-confirmation-form" method="post" action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart') ?>" class="form-validate form-inline">
		<div class="input-append">
			<input class="input-large" name="coupon_code" id="qzform_coupon_code" type="text" placeholder="<?php echo $this->coupon_input_text ?>" value=""/>
			<button type="submit" class="add-coupon-button btn">
				<span><?php echo JText::_('JSUBMIT'); ?></span>
			</button>
		</div>
		<input type="hidden" name="option" value="com_qazap" />
		<input type="hidden" name="task" value="cart.addcoupon" />
		<?php echo JHtml::_('form.token'); ?>		
	</form>
</div>


