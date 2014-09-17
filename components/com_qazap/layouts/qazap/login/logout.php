<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
$params = $displayData['params'];
$return = $displayData['return'];
$twofactormethods = $displayData['twofactormethods'];
$user = $displayData['user'];
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" class="form-vertical">
	<div class="login-greeting">
	<?php if ($params->get('name') == 0) : {
		echo JText::sprintf('COM_QAZAP_HINAME', htmlspecialchars($user->get('name')));
	} else : {
		echo JText::sprintf('COM_QAZAP_HINAME', htmlspecialchars($user->get('username')));
	} endif; ?>
	</div>
	<div class="logout-button">
		<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGOUT'); ?>" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
