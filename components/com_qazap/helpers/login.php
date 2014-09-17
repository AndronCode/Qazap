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

/**
 * Helper for mod_login
 *
 * @package     Joomla.Site
 * @subpackage  mod_login
 * @since       1.5
 */
class QZLogin
{
	public static function getReturnURL($view = null, $useMenu = false)
	{
		$app	= JFactory::getApplication();
		$router = $app->getRouter();
		// Stay on the same page
		$uri = clone JUri::getInstance();
		$vars = $router->parse($uri);
		
		if(isset($vars['lang']))
		{
			unset($vars['lang']);
		}
		
		if($view)
		{
			$vars['view'] = $view;
		}		
		
		if ($router->getMode() == JROUTER_MODE_SEF)
		{
			if (isset($vars['Itemid']) && $useMenu)
			{
				$itemid = $vars['Itemid'];
				$menu = $app->getMenu();
				$item = $menu->getItem($itemid);
				unset($vars['Itemid']);
				
				if (isset($item) && $vars == $item->query)
				{
					$url = 'index.php?Itemid='.$itemid;
				}
				else 
				{
					$url = 'index.php?'.JUri::buildQuery($vars).'&Itemid='.$itemid;
				}
			}
			else
			{
				$url = 'index.php?'.JUri::buildQuery($vars);
			}
		}
		else
		{
			$url = 'index.php?'.JUri::buildQuery($vars);
		}

		return base64_encode($url);
	}


	public static function getTwoFactorMethods()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';
		return UsersHelper::getTwoFactorMethods();
	}
	
	public static function getForm($view = null, $useMenu = false)
	{
		$user = JFactory::getUser();
		$data = array();
		$data['params'] = QZApp::getConfig();
		$data['return'] = QZLogin::getReturnURL($view, $useMenu);
		$data['twofactormethods'] = QZLogin::getTwoFactorMethods();
		$data['user'] = $user;

		if (!$user->guest)
		{
			$layout = new JLayoutFile('qazap.login.logout');
		}
		else
		{
			$layout = new JLayoutFile('qazap.login.login');
		}
		
		return $layout->render($data);		
	}
}
