<?php
/**
 * profile.php
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

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_qazap/models/userinfo.php';
/**
 * Profile model class for Users.
 *
 * @package     Joomla.Site
 * @subpackage  com_users
 * @since      1.0.0
 */
class QazapModelProfile extends QazapModelUserinfo
{
	protected $_cache = array();	
	protected $_btAddress = array();
	protected $_ordergroup = array();
	protected $_stAddresses = null;
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */	
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = $app->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);
	
		// Set type
		$address_type = strtolower($app->input->getString('type'));
		$this->setState($this->getName() . '.address_type', $address_type);
		
		$ordergroup_id = $input->getInt('ordergroup_id');
		$this->setState('profile.ordergroup_id', $ordergroup_id); 
		
		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);
	}	
	
	
	public function getTable($type = 'Userinfo', $prefix = 'QazapTable', $config = array())
	{
		JTable::addIncludePath(QZPATH_TABLE_ADMIN);
		return JTable::getInstance($type, $prefix, $config);
	}
	
	
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'fields');
		
		$form = $this->loadForm('com_qazap.profile', 'profile', array('control' => 'qzform', 'load_data' => $loadData));
	
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_qazap.edit.profile.data', array());

		if(empty($data)) 
		{
			$data = $this->getItem();            
		}

		return $data;
	}
	


	public function getItem($pk = NULL) 
	{
		$user = JFactory::getUser();
		
		if($user->guest)
		{
			$table = $this->getTable();
			// Load the data from cart session
			$cartModel = QZApp::getModel('Cart', array(), false);
			$cart = $cartModel->getCart();
			$address_type = $this->getState($this->getName() . '.address_type');			

			if($address_type == 'st' && !empty($cart->shipping_address))
			{
					$item = JArrayHelper::toObject($cart->shipping_address, 'JObject');
			}
			elseif(!empty($cart->billing_address))
			{
					$item = JArrayHelper::toObject($cart->billing_address, 'JObject');
			}			
			else
			{
				$return = $table->load(0);							
				// Check for a table object error.
				if ($return === false && $table->getError())
				{
					$this->setError($table->getError());
					return false;
				}
				// Convert to the JObject before adding other data.
				$properties = $table->getProperties(1);
				$item = JArrayHelper::toObject($properties, 'JObject');							
			}				
		}
		else
		{
			$address_type = $this->getState($this->getName() . '.address_type');

			if($address_type != 'st')
			{
				$qzuser = QZUser::get(null, 'bt');
				$pk = (int) $qzuser->get('id');
			}
			
			$item = parent::getItem($pk);
			
			if($item->id > 0 && $item->user_id != $user->get('id'))
			{
				return JError::raiseError(403, JText::_('COM_QAZAP_ERROR_NO_ACCESS'));
			}
			
			$item->address_type = $address_type;
		}
				
		return $item;		
	}

	public function save($data)
	{
		$return = parent::save($data);
		
		return $return;
	}
	
	public function getUserFields($type = 'bt')
	{
		$type = strtolower($type);
				
		if(!in_array($type, array('bt', 'st')))
		{
			$this->setError('"'. $type .'" is an invalid address type.');
			return false;
		}		

		$common_fields = array('id', 'address_type', 'email');
		
		if($type == 'st')
		{
			$common_fields[] = 'address_name';
		}		

		try 
		{							
			$user_fields = QZUser::getUserFields($type);
		}	
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}				
			
		$fields = array_merge($common_fields, $user_fields);
		
		return array_unique($fields);	
	}
	
	
	public function getBTAddress($user_id = 0) 
	{
		$user_id = (int) $user_id ? $user_id : JFactory::getUser()->get('id');
		
		if(!isset($this->_btAddress[$user_id]))
		{				
			$db = $this->getDbo();
			
			if(!$fields = $this->getUserFields('bt'))
			{
				$this->setError($this->getError());
				return false;
			}
			
			// Set a empty object 
			$this->_btAddress[$user_id] = (object) array_flip($fields);
			
			// Add the table prefix to fields
			$fields = array_map(function($val) { return 'a.'.$val;}, $fields);
			
			$sql = $db->getQuery(true)
				 ->select($fields)
				 ->select(array('c.country_name','s.state_name'))
				 ->from('#__qazap_userinfos AS a')
				 ->leftjoin('#__qazap_countries AS c ON a.country = c.id')
				 ->leftjoin('#__qazap_states AS s ON a.states_territory = s.id')			 
				 ->where('a.user_id = '. (int) $user_id)
				 ->where('a.address_type = ' . $db->quote('bt'))
				 //->where('a.state = 1')
				 ->group($fields);

			try 
			{
				$db->setQuery($sql);
				$result = $db->loadObject();								
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}

			$this->_btAddress[$user_id] = $result;			
		}

		return $this->_btAddress[$user_id];		
	}
	
	public function getSTAddresses($user_id = 0) 
	{
		$user_id = (int) $user_id ? $user_id : JFactory::getUser()->get('id');
		
		if(!isset($this->_stAddresses[$user_id]))
		{
			$db = $this->getDbo();
			
			if(!$fields = $this->getUserFields('st'))
			{
				$this->setError($this->getError());
				return false;
			}
			
			// Set a empty object 
			$this->_stAddresses[$user_id] = array((object) array_flip($fields));
			
			// Add the table prefix to fields
			$fields = array_map(function($val) { return 'a.'.$val;}, $fields);
			
			$sql = $db->getQuery(true)
				 ->select($fields)
				 ->select(array('c.country_name','s.state_name'))
				 ->from('#__qazap_userinfos AS a')
				 ->leftjoin('#__qazap_countries AS c ON a.country = c.id')
				 ->leftjoin('#__qazap_states AS s ON a.states_territory = s.id')			 
				 ->where('a.user_id = ' . (int) $user_id)
				 ->where('a.address_type = ' . $db->quote('st'))
				 //->where('a.state = 1')
				 ->group($fields);
				 
			try 
			{
				$db->setQuery($sql);
				$result = $db->loadObjectList();								
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$this->_stAddresses[$user_id] = $result;			
		}

		return $this->_stAddresses[$user_id];	
	}	
	
	
	public function getOrder($ordergroup_id = null)
	{
		$user = JFActory::getUser();
		$ordergroup_id = $ordergroup_id ? $ordergroup_id : $this->getState('profile.ordergroup_id', 0);
		
		if(!isset($this->_ordergroup[$ordergroup_id]))
		{
			$orderModel = QZApp::getModel('order', array('ignore_request' => true));		
			$ordergroup = $orderModel->getOrdergroupByID($ordergroup_id);			

			if(!$ordergroup && $orderModel->getError())
			{
				$this->setError($orderModel->getError());
				return false;
			}
			
			if(empty($ordergroup) || empty($ordergroup->ordergroup_id))
			{
				return JError::raiseError(404, JText::_('COM_QAZAP_ORDERGROUP_ID_NOT_FOUND'));
			}	
			if($ordergroup->user_id != $user->get('id'))
			{
				return JError::raiseError(403, JText::_('COM_QAZAP_DO_NOT_ACCESS_VIEW_THIS_ORDERGROUP'));
			}
			
			$this->_ordergroup[$ordergroup_id] = $ordergroup;		
		}		
		
		return $this->_ordergroup[$ordergroup_id];
	}
	/*
	*
	* Delete WishList Product
	* 
	*/
	public function deleteWishList($id)
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'tables');
		$table = JTable::getInstance('Wishlist', 'QazapTable');
		
		if(!$table->delete($id))
		{
			$this->setError($table->getError());
			return false;
		}
		return true;
	}

/*
	*
	* Delete WaitingList Product
	* 
	*/
	public function deleteWaitingList($id)
	{
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'tables');
		$table = JTable::getInstance('Waitinglist', 'QazapTable');
		
		if(!$table->delete($id))
		{
			$this->setError($table->getError());
			return false;
		}
		return true;
	}	
	
}
