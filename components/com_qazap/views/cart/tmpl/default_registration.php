<?php
/**
 * default_registration.php
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
<div class="cart-user-registration">
	<form id="member-registration" action="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('task'=>'cart.register'))); ?>" method="post" class="form-validate form-horizontal form-small" enctype="multipart/form-data">
		<?php foreach ($this->registrationForm->getFieldsets() as $fieldset) : ?>
			<?php $fields = $this->registrationForm->getFieldset($fieldset->name); ?>
			<?php if(count($fields)): ?>
				<fieldset>
				<?php foreach ($fields as $field) : ?>
					<?php if($field->hidden): ?>
						<?php echo $field->input;?>
					<?php elseif(strtolower($field->type) != 'spacer') : ?>
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
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary validate"><?php echo JText::_('JREGISTER');?></button>
				<input type="hidden" name="option" value="com_qazap" />
				<input type="hidden" name="task" value="cart.register" />
				<?php echo JHtml::_('form.token');?>				
			</div>
		</div>
	</form>
</div>
