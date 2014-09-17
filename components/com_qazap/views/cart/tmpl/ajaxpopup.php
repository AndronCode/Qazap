<?php
/**
 * ajaxpopup.php
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

?>
<?php if(!empty($this->result)) : ?>
	<div class="qazap-popup ajax-add-to-cart-box">
		<div class="qazap-popup-title">
			<?php if(empty($this->result->error)) : ?>
				<h3><?php echo JText::_('COM_QAZAP_CART_PRODUCT_ADD_SUCCESS') ?></h3>
			<?php else : ?>
				<h3><?php echo JText::_('COM_QAZAP_CART_PRODUCT_ADD_ERROR') ?></h3>
			<?php endif; ?>
		</div>		
		<div class="qazap-popup-content">
			<p class="<?php echo empty($this->result->error) ? 'text-success' : 'text-error'; ?>">
				<?php echo JText::_($this->result->message); ?>				
			</p>
	  </div>
		<div class="qazap-popup-footer">
			<a class="btn" href="<?php echo $this->continue_url ?>" title="<?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?>"><?php echo JText::_('COM_QAZAP_CART_CONTINUE_SHOPPING') ?></a>
			<?php if(!$this->result->error) : ?>	
				<a class="btn btn-primary" href="<?php echo $this->cart_url ?>" title="<?php echo JText::_('COM_QAZAP_CART_CHECKOUT') ?>"><?php echo JText::_('COM_QAZAP_CART_CHECKOUT') ?></a>
			<?php endif; ?>					
	  </div>
	</div>
<?php endif; ?>