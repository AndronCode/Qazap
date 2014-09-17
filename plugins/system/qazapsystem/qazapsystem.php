<?php
/**
 * qazapsystem.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage System Qazapsystem Plugin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

if(!class_exists('plgQazapSystemHelper'))
{
	require(dirname(__FILE__) . '/helpers/system.php');		
}

class PlgSystemQazapSystem extends JPlugin
{
	private $_helper;
	
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		if(!class_exists('plgQazapSystemHelper'))
		{
			return;
		}
		
		$this->_helper = new plgQazapSystemHelper();
	}
		
	public function onAfterInitialise() 
	{
		if(!($this->_helper instanceof plgQazapSystemHelper))
		{
			return;
		}
		
		$this->_helper->overrideTag();
	}
	
	public function onAfterRoute()
	{
		if(!($this->_helper instanceof plgQazapSystemHelper))
		{
			return;
		}
		
		if($this->_helper->controlMembership() === false)
		{
			JError::raiseWarning(1, $this->_helper->getError());
		}		
		
		$this->_helper->setNewCurrency();
		$result = $this->_helper->viewAccess();
	}
	
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		if(!($this->_helper instanceof plgQazapSystemHelper))
		{
			return;
		}
		
		if(!$this->_helper->updateUser($user, $isnew, $success, $msg))
		{
			JError::raiseWarning(1, $this->_helper->getError());
			return false;
		}	
	}	
	
	public function onUserBeforeDelete($user)
	{
		if(!($this->_helper instanceof plgQazapSystemHelper))
		{
			return;
		}
		
		if(!$this->_helper->checkUserForDelete($user))
		{
			$app = JFactory::getApplication('administrator');
			
			JLog::add($this->_helper->getError(), JLog::WARNING, 'jerror');
			$app->redirect(JRoute::_('index.php?option=com_users&view=users', false));
			return false;
		}		
	}

}
