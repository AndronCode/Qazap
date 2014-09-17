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
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

QZApp::loadCSS();			
QZApp::loadJS();
require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';
$twofactormethods = UsersHelper::getTwoFactorMethods();
?>
<div class="shop-offline-page">
	<h1>
		<?php echo $this->escape($this->store->name); ?>
	</h1>
	<?php if(str_replace(' ', '', $this->params->get('offline_message')) != '') : ?>
		<p>
			<?php echo $this->params->get('offline_message'); ?>
		</p>
	<?php elseif (str_replace(' ', '', JText::_('COM_QAZAP_SHOP_OFFLINE_MESSAGE')) != '') : ?>
		<p>
			<?php echo JText::_('COM_QAZAP_SHOP_OFFLINE_MESSAGE'); ?>
		</p>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login" role="form">
		<fieldset>
			<div class="control-group">
				<label class="control-label" for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
				<div class="controls">
					<input name="username" id="username" type="text" class="inputbox" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="18" />
				</div>
			</div>	
			<div class="control-group">
				<label class="control-label" for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
				<div class="controls">
					<input type="password" name="password" class="inputbox" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" id="passwd" />
				</div>
			</div>
			<?php if (count($twofactormethods) > 1) : ?>
				<div class="control-group">
					<label class="control-label" for="secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
					<div class="controls">
						<input type="text" name="secretkey" class="inputbox" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" id="secretkey" />
					</div>
				</div>
			<?php endif; ?>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox" for="remember">
						<input type="checkbox" name="remember" value="yes" alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME'); ?>" id="remember" /> <?php echo JText::_('JGLOBAL_REMEMBER_ME'); ?>
					</label>
					<button type="submit" class="btn btn-primary"><?php echo JText::_('JLOGIN'); ?></button>
				</div>
			</div>
		</fieldset>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_('index.php', false)); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>