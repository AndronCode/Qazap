<?php
/**
 * qazap.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_qazap')) 
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include Qazap platform
if (!class_exists('QZApp')) 
{	
	require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'app.php';
}

// Make sure that the Joomla Platform has been successfully loaded.
if (!class_exists('QZApp'))
{
	throw new Exception('Qazap Platform not loaded.');
}

// Setup Qazap for autload classes
QZApp::setup();

// Load required JS and CSS files
QZApp::loadJS();
QZApp::loadCSS();

// Include dependancies
jimport('joomla.application.component.controller');

$app = JFactory::getApplication();
$view = $app->input->getCmd('view') ? strtolower($app->input->getCmd('view')) : null;
$installerDummy = JPath::clean(JPATH_ROOT . '/administrator/components/com_qazap/installer.log.ini');

if(is_file($installerDummy) || $view == 'install')
{
	JFactory::getLanguage()->load('com_qazap.instl');
	$app->input->set('view', 'install');
	$controllerClass = 'QazapControllerInstall';
	$controllerFile = 'install.php';
	
	if(is_file(QZPATH_CONTROLLER_ADMIN . DS . $controllerFile) && !class_exists($controllerClass)) 
	{
	  require_once(QZPATH_CONTROLLER_ADMIN . DS . $controllerFile);
	}
	
	if(class_exists($controllerClass))
	{
		$controller = new $controllerClass;
	}		
}
else
{
	$controller	= JControllerLegacy::getInstance('Qazap');
}

$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
