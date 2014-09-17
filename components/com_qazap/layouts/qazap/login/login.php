<?php
/**
 * login.php
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

require_once JPATH_SITE.'/components/com_users/helpers/route.php';

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
$params = $displayData['params'];
$return = $displayData['return'];
$twofactormethods = $displayData['twofactormethods'];
$user = $displayData['user'];
?>

<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" class="form-horizontal form-small">
	<div class="userdata">
		<div id="form-login-username" class="control-group">
			<div class="control-label">
				<label for="modlgn-username"><?php echo JText::_('JGLOBAL_USERNAME') ?></label>				
			</div>
			<div class="controls">
				<input id="modlgn-username" type="text" name="username" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_USERNAME') ?>" />
			</div>
		</div>
		<div id="form-login-password" class="control-group">
			<div class="control-label">
				<label for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD'); ?>
			</div>
			<div class="controls">
				<input id="modlgn-passwd" type="password" name="password" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
			</div>
		</div>
		<?php if (count($twofactormethods) > 1): ?>
		<div id="form-login-secretkey" class="control-group">
			<div class="control-label">
				<label for="modlgn-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
			</div>
			<div class="controls">
				<div class="input-append">
					<input id="modlgn-secretkey" autocomplete="off" type="text" class="input-small" name="secretkey" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" />
					<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<span class="icon-help"></span>
					</span>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
		<div id="form-login-remember" class="control-group">
			<label for="modlgn-remember" class="control-label"><?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME') ?></label>
			<div class="controls">
				<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
			</div>			
		</div>
		<?php endif; ?>
		<div id="form-login-submit" class="control-group">
			<div class="controls">
				<button type="submit" tabindex="0" name="Submit" class="btn btn-primary"><?php echo JText::_('JLOGIN') ?></button>
			</div>
		</div>
		
		<?php
			$usersConfig = JComponentHelper::getParams('com_users'); ?>
			<div class="control-group">
				<div class="controls">
					<ul class="unstyled">
						<li>
							<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind&Itemid='.UsersHelperRoute::getRemindRoute()); ?>">
							<?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?></a>
						</li>
						<li>
							<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset&Itemid='.UsersHelperRoute::getResetRoute()); ?>">
							<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a>
						</li>
					<?php if ($usersConfig->get('allowUserRegistration')) : ?>
						<li>
							<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration&Itemid='.UsersHelperRoute::getRegistrationRoute()); ?>">
							<?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?></a>
						</li>
					<?php endif; ?>				
					</ul>
				</div>
			</div>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
