<?php
/**
 * edit_shipto.php
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
QZApp::loadJS();
QZApp::loadCSS();
?>
<div class="cart-shipping-address-page">
	<div class="page-header">
		<h1><?php echo JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS') ?></h1>
	</div>
	<form id="shipto-form" method="post" class="form-validate form-horizontal" action="<?php echo JRoute::_('index.php?option=com_qazap&view=cart&layout=edit_shipto') ?>">
	<?php foreach ($this->STForm->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
		<?php $fields = $this->STForm->getFieldset($fieldset->name);?>
		<?php if (count($fields)):?>
			<fieldset>
			<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.
			?>
				<legend><?php echo JText::_($fieldset->label);?></legend>
			<?php endif;?>
			<?php foreach ($fields as $field) :// Iterate through the fields in the set and display them.?>
				<?php if ($field->hidden):// If the field is hidden, just display the input.?>
					<?php echo $field->input;?>
				<?php else:?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input;?>
						</div>
					</div>
				<?php endif;?>
			<?php endforeach;?>
			</fieldset>
		<?php endif;?>
	<?php endforeach;?>	
		<div class="cart-continue-area">
			<div class="row-fluid">
				<div class="span6">
					<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute()) ?>" class="cart-continue-button btn btn-large">
						<?php echo JText::_('COM_QAZAP_CART_GO_BACK') ?>
					</a>	
				</div>	
				<div class="span6">
					<?php echo $this->confirmButton ?>
				</div>
			</div>
		</div>
	</form>			
</div>

