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
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

// Include Qazap application platform
if (!class_exists('QZApp')) 
{	
	require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'app.php';
}

// Make sure that the Joomla Platform has been successfully loaded.
if (!class_exists('QZApp'))
{
	throw new Exception('Qazap Application Platform is not loaded.');
}

// Setup Qazap for autload classes
QZApp::setup();

// Include dependancies
jimport('joomla.application.component.controller');

$app = JFactory::getApplication();
$view = $app->input->getCmd('view') ? strtolower($app->input->getCmd('view')) : null;
$task = $app->input->get('task') ? strtolower($app->input->get('task')) : null;
$callSubcontroller = false;

if(($task && (strpos($task, '.') === false)))
{
	$controllerClass = 'QazapController' . ucfirst($view);
	$controllerFile = $view . '.php';
	
	if(is_file(QZPATH_CONTROLLER . DS . $controllerFile) && !class_exists($controllerClass)) 
	{
	  require_once(QZPATH_CONTROLLER . DS . $controllerFile);
	}
	
	if(class_exists($controllerClass))
	{
		$controller = new $controllerClass;
		$callSubcontroller = true;
	}	
}

if(!$callSubcontroller)
{
	$controller	= JControllerLegacy::getInstance('Qazap');
	
	if($task && strpos($task, '.'))
	{
		$split = explode('.', $task);
		$task = isset($split[1]) ? $split[1] : $task;
	}
}

// Execute the task.
$controller->execute($task);
$controller->redirect();
