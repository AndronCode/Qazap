<?php
/**
 * select_address.php
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
	<div class="span6">
		<?php echo QZLogin::getForm('cart'); ?>
	</div>
	<div class="span6">
		
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<form id="member-profile" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
		<?php foreach ($this->BTForm->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
			<?php $fields = $this->BTForm->getFieldset($fieldset->name);?>
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
				<div class="form-actions">
					<button type="submit" class="btn btn-primary validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
					<a class="btn" href="<?php echo JRoute::_('index.php?option=com_qazap&view=cart', false) ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
					<input type="hidden" name="qzform[id]" value="<?php echo $this->BTForm->getValue('id') ?>" />
					<input type="hidden" name="qzform[address_type]" value="bt" />
					<input type="hidden" name="option" value="com_qazap" />
					<input type="hidden" name="task" value="cart.usersave" />
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</form>		
		
	</div>
</div>

