<?php
/**
 * system.php
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

if(!class_exists('QZApp'))
{
	if(file_exists(JPATH_ADMINISTRATOR . '/components/com_qazap/app.php'))
	{
		require(JPATH_ADMINISTRATOR . '/components/com_qazap/app.php');		
	}
	else
	{
		// This means we do nto have Qazap installed in this site.
		return;
	}
}

jimport('joomla.table.table');

class plgQazapSystemHelper extends JObject
{
	
	protected static $_member_access = null;
	
	public function __construct()
	{
		$jlang = JFactory::getLanguage();
		$filename = 'com_qazap';
		$jlang->load($filename, JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load($filename, JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load($filename, JPATH_ADMINISTRATOR, null, true);
				
		// Setup Qazap for autload classes
		QZApp::setup();	
	}
		
	public function overrideTag() 
	{
		if(!file_exists(JPATH_ADMINISTRATOR . '/components/com_qazap/helpers/overrides.php'))
		{
			return;
		}
		
		// Need to override new system for proper product tag search.
		QZOverrides::setup();
	}
	
	public function controlMembership()
	{
		$app = JFactory::getApplication();		
		
		// We do not need it for backend.
		if($app->isAdmin())
		{
			return;
		}		
		
		return $this->membershipControl();
	}
	
	public function setNewCurrency($currency_id = null)
	{
		$app = JFactory::getApplication();		
		
		// We do not need it for backend.
		if($app->isAdmin())
		{
			return;
		}	

		if(!file_exists(JPATH_SITE . '/components/com_qazap/helpers/helper.php'))
		{
			return;
		}
		
		$currency_id = (int) !empty($currency_id) ? $currency_id : $app->input->post->getInt('qazap_currency_id', 0);
				
		if($currency_id > 0)
		{
			// Set the new currency
			QZHelper::setDisplayCurrency($currency_id);
		}
	}
	
	public function updateUser($user, $isnew, $success, $msg)
	{
		if($success && isset($user['id']) && ($user['id'] > 0))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_qazap/tables');
			$table = JTable::getInstance('userinfo', 'QazapTable', array());			
			$result = $table->load(array('user_id' => $user['id']));
			
			if($result === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
			
			if($result && ($table->id > 0))
			{
				$table->set('email', $user['email']);
				
				if(!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}				
			}
		}		
		
		return true;
	}
	
	public function checkUserForDelete($user)
	{
		if(isset($user['id']) && ($user['id'] > 0))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_qazap/tables');
			
			// Check if this user is registered as Qazap vendor.
			$vendorTable = JTable::getInstance('vendor', 'QazapTable', array());
			$result = $vendorTable->load(array('vendor_admin' => $user['id']));

			if(($result === false) && $vendorTable->getError())
			{
				$this->setError($vendorTable->getError());
				return false;					
			}
			
			if($result && ($vendorTable->id > 0))
			{
				$this->setError(JText::sprintf('COM_QAZAP_USER_DELETE_ERROR_IS_VENDOR', $user['name']));
				return false;					
			}
			
			// Check if this user has some billing or shipping address saved in Qazap
			$userTable = JTable::getInstance('userinfo', 'QazapTable', array());			
			$result = $userTable->load(array('user_id' => $user['id']));
			
			if(($result === false) && $userTable->getError())
			{
				$this->setError($userTable->getError());
				return false;					
			}
			
			if($result && ($userTable->id > 0))
			{
				$this->setError(JText::sprintf('COM_QAZAP_USER_DELETE_ERROR_IS_REGISTERED_USER', $user['name']));
				return false;					
			}				
		}
		
		return false;
	}
	
	
	public function viewAccess()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$input = $app->input;
		
		if($app->isAdmin())
		{
			return;
		}
		
		// Get raw post
		$post = JRequest::get('POST', 2);	
		
		if(!empty($post))
		{
			return;
		}
				
		if($doc->_type != 'html')
		{
			return;
		}
		
		$option = $input->getCmd('option', '');
		$view = $input->getCmd('view', '');
		$user = JFactory::getUser();
		$cache = JFactory::getCache('com_qazap_membership_access', 'callback');
		$cache->setCaching(1);	
		$accesses = $cache->call(array('plgQazapSystemHelper', 'getMemberAccesses'), $user->get('id'));	

    if(!empty($accesses))
    {
      foreach($accesses as &$access)
      {
        $access_to_members = json_decode(base64_decode($access->access_to_members), true);
        
        if(empty($access_to_members) || !isset($access_to_members['option']) || !isset($access_to_members['view']))
        {
					continue;
				}
        
        if(($option != $access_to_members['option']) || ($view != $access_to_members['view']))
        {
        	continue;
        }
        
	    	if($user->guest)
	    	{
	    		$app->enqueueMessage(JText::sprintf('COM_QAZAP_MEMBER_SPECIAL_ACCESS_REDIRECT_MESSAGE', htmlspecialchars($access->plan_name)));
					$app->redirect($this->getRedirectPage());
					return;
				}
				else
				{
					$authorisedLevels = (array) $user->getAuthorisedViewLevels();
					
					if(!empty($authorisedLevels) && !in_array($access->jview_id, $authorisedLevels))
					{
						$app->enqueueMessage(JText::sprintf('COM_QAZAP_MEMBER_SPECIAL_ACCESS_REDIRECT_MESSAGE', htmlspecialchars($access->plan_name)));
						$app->redirect($this->getRedirectPage());
						return;						
					}
				}
      }      
    }		
	}

	protected function getRedirectPage()
	{
		$config = QZApp::getConfig();
		$redirect = $config->get('non_member_redirection_url', null);
		
		if((strpos($redirect, 'http://') !== 0) || (strpos($redirect, 'https://') !== 0))
		{
			$redirect = JRoute::_(trim($redirect), false);
		}

		if(empty($redirect) || !JUri::isInternal($redirect))
		{
			return JUri::base();
		}
		else
		{
			return $redirect;
		}
	}

	public static function getMemberAccesses()
	{
		if(static::$_member_access === null)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
									->select('a.plan_name, a.access_to_members, jusergroup_id, jview_id')
									->from('#__qazap_memberships AS a')
									->where('a.access_to_members IS NOT NULL')
									->where('trim(coalesce(a.access_to_members, "")) <> ""')
									->where('a.state = 1');

			$db->setQuery($query);
			$plans = $db->loadObjectList();
			
			if(empty($plans))
			{
				static::$_member_access = array();
			}
			else
			{
				static::$_member_access = $plans;
			}			
		}
		
		return static::$_member_access;
	}	
	/**
	* Method to control memberships
	* 
	* @return boolean False in case of failure
	*/
	protected function membershipControl()
	{		
		$membersToNotify = array();
		$notified = array();
		$model = QZApp::getModel('member');	
		
		if(!($model instanceof QazapModelMember))
		{
			// If model not due to incomplete installation return from here.
			return;
		}
			
		$config = QZApp::getConfig('com_qazap');
		
		// Check for expired members 
		if(!($expiredMembers = $model->getMembersByExpiry()) && $model->getError())
		{			
			$this->setError($model->getError());
			return false;			
		}						

		if($expiredMembers && !$model->expireMembers($expiredMembers) && $model->getError())
		{				
			$this->setError($model->getError());
			return false;			
		}			

		
		// First notification to the members who are going to be expired 		
		if(!($membersToNotify1 = $model->getMembersByExpiry($config->get('first_notification_time'))) && $model->getError())
		{			
			$this->setError($model->getError());
			return false;			
		}
			
		if($membersToNotify1)
		{
			foreach($membersToNotify1 as $member)
			{
				if(!$member->notified_1 && !array_key_exists($member->id, $membersToNotify))
				{
					$membersToNotify[$member->id] = $member;
					$notified[$member->id] = 'notified_1';					
				}
			}			
		}

		// Second notification to the members who are going to expire		
		if(!($membersToNotify2 = $model->getMembersByExpiry($config->get('second_notification_time'))) && $model->getError())
		{			
			$this->setError($model->getError());
			return;			
		}			

		if($membersToNotify2)
		{
			foreach($membersToNotify2 as $member)
			{
				if(!$member->notified_2 && !array_key_exists($member->id, $membersToNotify))
				{
					$membersToNotify[$member->id] = $member;
					$notified[$member->id] = 'notified_2';					
				}
			}			
		}

		if(count($membersToNotify) && !$model->notifyMembers($membersToNotify) && $model->getError())
		{				
			$this->setError($model->getError());
			return false;			
		}
		
		if(count($notified) && !$model->setNotified($notified) && $model->getError())
		{
			$this->setError($model->getError());
			return false;				
		}		
		
		return true;
	}

}
