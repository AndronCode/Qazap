<?php
/**
 * orderdetails.php
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

jimport('joomla.application.component.modelitem');
/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelOrderdetails extends JModelItem 
{
	protected $do_redirect = true;
	protected $session = null;
	
	
	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @since	1.0.0
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('ordergroup_id');
		$this->setState('ordergroup.id', $pk);				

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	}
	
	/**
	 * Method to get ordergroup data.
	 *
	 * @param integer The ordergroup_id of the ordergroup.
	 *
	 * @return  mixed  Ordergroup data object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('ordergroup.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if (!isset($this->_item[$pk]))
		{
			$model	= QZApp::getModel('Order', array('ignore_request'=>true));
			$user		= JFactory::getUser();
			$app		= JFactory::getApplication();
			
			try
			{				
				$ordergroup = $model->getOrdergroupByID($pk);		
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
				
			if($ordergroup === false)
			{
				$this->setError($model->getError());
				return false;				
			}
			elseif(empty($ordergroup) || empty($ordergroup->ordergroup_id))
			{
				return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_ORDERGROUP_NOT_FOUND'));
			}
			
			if($user->guest && ($ordergroup->user_id > 0))
			{
				if(!$this->do_redirect)
				{
					$this->setError(JText::_('COM_QAZAP_ERROR_ORDERGROUP_DOES_NOT_BELONG_TO_THIS_USER'));
				}
				else
				{
					// We need to ask the user to login to check if this order belongs to this user
					$app->enqueueMessage(JText::_('COM_QAZAP_MSG_LOGIN_TO_VIEW_ORDERDETAILS'));
					$return = JRoute::_(QazapHelperRoute::getOrderdetailsRoute($ordergroup->ordergroup_id));
					$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($return)));
				}

				return false;
			}
			elseif(($ordergroup->user_id > 0) && ($ordergroup->user_id != $user->get('id')) )
			{
				return JError::raiseError(403, JText::_('COM_QAZAP_DO_NOT_ACCESS_VIEW_THIS_ORDERGROUP'));
			}			
			
			$this->_item[$pk] = $ordergroup;
		}
		
		return $this->_item[$pk];
	}

	protected function getSession()
	{
		if($this->session === null)
		{
			$session = JFactory::getSession();
			$this->session = $session->get('QazapOrderdetails', array(), 'qazap');			
		}
		
		return $this->session;	
	}
		
	public function getCanSee()
	{
		$session = $this->getSession();
		$ordergroup = $this->getItem();
		$user		= JFactory::getUser();
		
		if(empty($ordergroup))
		{
			return false;
		}

		if($user->guest && in_array($ordergroup->ordergroup_id, $session))
		{
			return true;
		}
		elseif(!$user->guest && ($ordergroup->user_id == $user->get('id')))
		{
			return true;
		}
		
		return false;
	}
	
	public function add($ordergroup_id)
	{
		$session = JFactory::getSession();
		$list = $this->getSession();
		
		if(!in_array($ordergroup_id, $list))
		{
			$list[] = $ordergroup_id;
		}
		
		$session->set('QazapOrderdetails', $list, 'qazap');		
		return true;
	}
}