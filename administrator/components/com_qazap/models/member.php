<?php
/**
 * member.php
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
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
* Qazap model.
*/
class QazapModelMember extends JModelAdmin
{
	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';
	protected $_membersToExpire = array();
	protected $_membershipHistory = array();

	/**
	* Returns a reference to the a Table object, always creating it.
	*
	* @param	type	The table type to instantiate
	* @param	string	A prefix for the table class name. Optional.
	* @param	array	Configuration array for model. Optional.
	* @return	JTable	A database object
	* @since	1.0.0.0
	*/
	public function getTable($type = 'Member', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	* Method to get the record form.
	*
	* @param	array	$data		An optional array of data for the form to interogate.
	* @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	* @return	JForm	A JForm object on success, false on failure
	* @since	1.0.0.0
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_qazap.member', 'member', array('control' => 'jform', 'load_data' => $loadData));

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
	* @since	1.0.0.0
	*/
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.member.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();            
		}

		return $data;
	}

	/**
	* Method to get a single record.
	*
	* @param	integer	The id of the primary key.
	*
	* @return	mixed	Object on success, false on failure.
	* @since	1.0.0.0
	*/
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{
			//Do any procesing on fields here if needed
		}
		
		return $item;
	}
		
	public function changeStatus($pks, $status)
	{
		$pks = (array) $pks;
		$pks = array_map('intval', $pks);
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->update('#__qazap_members')
			->set('status = ' . (int) $status)
			->where('id IN (' . implode(',', $pks) . ')');
		
		try
		{
			$db->setQuery($query);
			$db->execute();
		}		
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		return true;
	}
	
	
	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;
		$table = $this->getTable();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		// Load plugins.
		JPluginHelper::importPlugin('user');
		$iAmSuperAdmin	= $user->authorise('core.admin');
		$dispatcher = JEventDispatcher::getInstance();
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				$allow = $user->authorise('core.edit.state', 'com_qazap');
				$allow = (!$iAmSuperAdmin && $table->user_id == $user->id) ? false : $allow;
				if ($allow)
				{
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					} 
					else 
					{
						// Trigger the onUserAfterDeleteGroup event.
						$dispatcher->trigger('onUserAfterDeleteGroup', array($table->getProperties(), true, $this->getError()));
					}	
				}
				else
				{
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
					return FALSE;
				}
				
				$query = $db->getQuery(true);
				$query->clear()
							->delete($db->quoteName('#__user_usergroup_map'))
							->where('user_id = ' . (int) $table->user_id.' AND group_id = ' . (int) $table->jusergroup_id);
				try
				{
					$db->setQuery($query);
					$db->execute();
				}
				catch(Exception $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}
			else
			{
			 $this->setError($table->getError());
			 return false;
			}
		}	
		return TRUE;
	}
	/*
	*
	* Save All Product Data
	*
	*/		
	public function save($data)	
	{	
		$table = $this->getTable();
		$membershipsTable = $this->getTable('membership');
		
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		$date = JFactory::getDate();
		$date = $date->toSQL();
		$user = JFactory::getUser();
	
		JPluginHelper::importPlugin('content');
				
		try
		{
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
				$data['modified_on'] = $date;
				$data['modified_by'] = $user->get('id');
			}				
			if($isNew)
			{
				$data['created_on'] = $date;
				$data['created_by'] = $user->get('id');
				$data['modified_on'] = $date;
				$data['modified_by'] = $user->get('id');
				if(!isset($data['membership_id']) || !$data['membership_id'])
				{
					$this->setError('Please select a Membership Plan');
					return false;					
				}
				$from_date = strtotime($data['from_date']);
				if(isset($data['duration']))
				{
					$end_date = strtotime('+'.$data['duration'].' day', $from_date);
				}
				else
				{
					$membershipsTable->load($data['membership_id']);				
					$end_date = strtotime('+'.$membershipsTable->plan_duration.' day', $from_date);					
				}
				$data['to_date'] = date('Y-m-d H:i', $end_date);
			}
			elseif(isset($data['duration']))
			{
				if($data['duration'] > 0)
				{
					$from_date = strtotime($data['from_date']);
					$end_date = strtotime('+'.$data['duration'].' day', $from_date);		
					$data['to_date'] = date('Y-m-d H:i', $end_date);
				}
				else
				{
					$end_date = isset($data['to_date']) ? strtotime($data['to_date']) : time();
					$end_date = strtotime('+'.$data['duration'].' day', $end_date);
					$data['to_date'] = date('Y-m-d H:i', $end_date);
				}
			}
			
			if(!isset($data['status']) || empty($data['status']))
			{
				$date = time();
				$end_date = strtotime($data['to_date']);
				$data['status'] = ($end_date <= $date) ? 0 : 1;
			}
			
			if(!isset($data['jusergroup_id']) || !$data['jusergroup_id'])
			{
				if(!$membershipsTable->load($data['membership_id']))
				{
					$this->setError($membershipsTable->getError());
					return false;
				}
				
				$data['jusergroup_id'] = $membershipsTable->jusergroup_id;
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('member', &$data, $isNew));
		
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}

			$this->prepareTable($table);

			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}			 
			
			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}
					
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			if(!$this->updateJGroupMap($table->status, $table->user_id, $table->jusergroup_id))
			{
				$this->setError(JText::_('COM_QAZAP_USER_ERROR'));
				return false;
			}	
			
			if (!$this->saveHistory($table->id, $table->status))
			{
				$this->setError($this->getError());
				return false;
			}
			
			// Lets send the emails related to the subscription
			$this->sendMail(array('data' => $table, 'isNew' => $isNew, 'multiple' => false), true);	
			
			$this->cleanCache();
			
			// Trigger the onContentAfterSave event.
			$dispatcher->trigger('onAfterSave', array('member', $data, $isNew));
	
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) 
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);
		
		return true;		
	}
	
	
	protected function updateJGroupMap($status, $userid, $jgroup_id)
	{
		$db = $this->getDbo();
		
		if($status == 1)
		{
			$query = $db->getQuery(true)
						->select('COUNT(user_id)')
						->from('`#__user_usergroup_map`')
						->where('user_id = ' . (int) $userid . ' AND group_id =' . (int) $jgroup_id);			
			$db->setQuery($query);
			$countEntry = $db->loadResult();
		
			if($countEntry == 0)
			{
				$query->clear()
					  ->insert($db->quoteName('#__user_usergroup_map'))
					  ->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')))
					  ->values((int) $userid . ', ' . (int) $jgroup_id);
				try
				{
					$db->setQuery($query);
					$db->execute();
				}
				catch(Exception $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}			
		}
		else
		{
			$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_usergroup_map'))
					->where('user_id='.$userid.' AND group_id='.$jgroup_id);
			try
			{
				$db->setQuery($query);
				$db->execute();
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}	
		}

		return true;
	}	
	

	protected function saveHistory($id, $status)
	{		
		$db = $this->getDBO();		
		$sql = $db->getQuery(true)
			 ->select('status')
			 ->from('#__qazap_membership_history')
			 ->where('id = '. (int) $id);		
		try
		{
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			$this->setError(JText::_('COM_QAZAP_MEMBERSHIP_HISTORY_SAVE_FAILED'));
			return false;
		}
		
		if(!empty($result))
		{
			$lastHistory = end($result);
			if($lastHistory->status == $status)
			{
				return true;
			}
		}		
		
		$insertColumns = array('id', 'status', 'date', 'created_by');
		$date = JFactory::getDate();
		$date = $date->toSQL();		
		$values =  array($id, $status, $db->Quote($date), JFactory::getUser()->id);
		$insertValues = (implode(',',$values));

		$query = $db->getQuery(true) 
		    	->insert($db->quoteName('#__qazap_membership_history'))
		    	->columns($db->quoteName($insertColumns));	
		$query->values($insertValues);
		$db->setQuery($query);
		try 
		{
			$result = $db->execute();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			$this->setError(JText::_('COM_QAZAP_MEMBERSHIP_HISTORY_SAVE_FAILED'));
			return false;
		}	
		return true;			
	} 
	
	/**
	* Method to save members/subscription when a order group is saved
	* 
	* @param array $products
	* @param integer $user_id
	* 
	* @return boolean
	*/	
	public function saveMembers($products, $user_id)
	{
		$db = $this->getDbo();
		$date = JFactory::getDate();
		$params = QZApp::getConfig();
		
		$sql = $db->getQuery(true)
			 ->select('*')
			 ->from('#__qazap_members')
			 ->where('user_id = ' . (int) $user_id);
		try 
		{
			$db->setQuery($sql);
			$datas = $db->loadAssocList('membership_id');
		} 
		catch(Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}

		$save = array();
		$checked = array();

		foreach($products as $product)
		{
			if(empty($product->product_membership))
			{
				continue;	
			}
			
			$product->product_membership = (object) $product->product_membership;
			$plan_id = $product->product_membership->id;			
			$datas[$plan_id] = isset($datas[$plan_id]) ? $datas[$plan_id] : array();
			
			if(empty($datas[$plan_id]))
			{
				$datas[$plan_id]['id'] = 0;
				$datas[$plan_id]['from_date'] = date('Y-m-d H:i', time());
				$datas[$plan_id]['membership_id'] = $plan_id;
				$datas[$plan_id]['effected_items'] = null;
			}
			
			$datas[$plan_id]['user_id'] = $user_id;
			
			if(!in_array($plan_id, $checked))
			{
				$checked[] = $plan_id;
				
				$datas[$plan_id]['duration'] = (isset($datas[$plan_id]['to_date']) && strtotime($datas[$plan_id]['to_date']) > time())? abs(strtotime($datas[$plan_id]['to_date']) - time())/60/60/24 : 0;
				
				$datas[$plan_id]['duration'] = ceil($datas[$plan_id]['duration']);
				
				if(!empty($datas[$plan_id]['effected_items']))
				{
					if(is_string($datas[$plan_id]['effected_items']))
					{
						$datas[$plan_id]['effected_items'] = json_decode($datas[$plan_id]['effected_items'], true);
					}					
				}
				else
				{
					$datas[$plan_id]['effected_items'] = array();
				}			
			}
			
			// Activate/effect Membership
			if($product->order_status == $params->get('order_states_membership_activation') 
					&& !in_array($product->order_items_id, $datas[$plan_id]['effected_items']))
			{
				if($params->get('membership_process', 'join'))
				{
					$datas[$plan_id]['duration'] = $datas[$plan_id]['duration'] + $product->product_membership->plan_duration;
				}
				else
				{	
					$datas[$plan_id]['from_date'] = date('Y-m-d H:i', time());
					$datas[$plan_id]['duration'] = $product->product_membership->plan_duration;
				}
				
				$datas[$plan_id]['effected_items'][] = $product->order_items_id;

				if(!in_array($plan_id, $save))
				{
					$save[] = $plan_id;
				}				
			}
			
			// Disable/expire membership
			elseif($product->order_status != $params->get('order_states_membership_activation') 
							&& in_array($product->order_items_id, $datas[$plan_id]['effected_items']))
			{			
				$datas[$plan_id]['duration'] = ( $product->product_membership->plan_duration * -1 );

				$key = array_search($product->order_items_id, $datas[$plan_id]['effected_items']);
				unset($datas[$plan_id]['effected_items'][$key]);
				
				if(!in_array($plan_id, $save))
				{
					$save[] = $plan_id;
				}
			}	
		}

		foreach($datas as $key => $data)
		{
			if(in_array($key, $save) && !$this->save($data))
			{
				$this->setError($this->getError());
				return false;
			}			
		}
				
		return true;
	}	
	// Get Membership History //
	
	public function getMembershipHistory($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		
		if(!isset($this->_membershipHistory[$pk]))
		{			
			$db = $this->getDbo();			
			$sql = $db->getQuery(true)
				 ->select(array('a.*', 'c.name'))
				 ->from('#__qazap_membership_history AS a')
				 ->join('LEFT', '#__users AS c ON c.id = a.created_by')
				 ->where('a.id = ' . (int) $pk)
				 ->order('a.id ASC');
			try
			{
				$db->setQuery($sql);
				$this->_membershipHistory[$pk] = $db->loadObjectList();			
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
		}

		return $this->_membershipHistory[$pk];
	}
	
	/**
	* Method to get expired members member id
	* 
	* @param	integer 				$time	Time in format of hours
	* @return	mixed (array/boolean)	Array or false in case of failure
	* @since	1.0.0
	*/
	public function getMembersByExpiry($time = null)
	{
		$hash = md5((string) $time);
		
		if(!isset($this->_membersToExpire[$hash]))
		{
			$app = JFactory::getConfig();
			$date = JFactory::getDate();
			//$date->setTimezone();
			$db = $this->getDbo();
			
			$sql = $db->getQuery(true)
				 ->select('a.id, a.user_id, a.membership_id, a.jusergroup_id, a.from_date, a.to_date, a.status, a.notified_1, a.notified_2')
				 ->from('#__qazap_members AS a')
				 ->select('b.plan_name')
				 ->join('LEFT', '#__qazap_memberships AS b ON b.id = a.membership_id');
				 
			if($time)
			{
				$to_date = date("Y-m-d H:i:s", strtotime('+' . $time . 'days'));
				$sql->where('a.to_date BETWEEN ' . $db->quote($date->toSql()). ' AND ' . $db->quote($to_date));
			}
			else
			{
				$sql->where('a.to_date < ' . $db->quote($date->toSql()));
			}
			
			$sql->where('a.status = 1');
			
			try
			{
				$db->setQuery($sql);
				$members = $db->loadObjectList('id');
			} 
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(count($members))
			{
				$this->_membersToExpire[$hash] = $members;
			}
			else
			{
				$this->_membersToExpire[$hash] = false;
			}		
		}
		
		return $this->_membersToExpire[$hash];
	}
	
	/**
	* Method to expire members
	* 
	* @param	array	$members	Array of member objects
	* @return	boolean	
	* @since	1.0.0
	*/
	public function expireMembers($members)
	{
		if(!count($members))
		{
			return true;
		}
				
		$pks = array_map('intval', array_keys($members));
		
		if(count($pks) > 1)
		{
			$where = 'id IN ('.implode(',', $pks). ')';
		}
		else
		{
			$where = 'id ='. (int) $pks[0];
		}			

		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			->update('#__qazap_members')
			->set('status = 0')
			->where($where);
	
		try
		{
			$db->setQuery($sql);
			$db->execute();
		}	 
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		// Update Joomla usergroup map table
		if(!$this->deleteUsersFromJUsergroup($members))
		{
			$this->setError($this->getError());
			return false;
		}
		
		foreach($members as &$member)
		{
			$member->status = 0;
		}	

		// Lets send the emails related to the subscription
		$this->sendMail(array('data' => $members, 'isNew' => false, 'multiple' => true));	
		
		return true;		 
	}
	
	public function notifyMembers($members)
	{
		if(!count($members))
		{
			return true;
		}

		// Lets send the emails related to the subscription
		$this->sendMail(array('data' => $members, 'isNew' => false, 'multiple' => true));
				
		return true;		
	}
	
	protected function deleteUsersFromJUsergroup($members)
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$query = $db->getQuery(true)
				->delete('#__user_usergroup_map');
				
		foreach($members as $member)
		{
			$query->where('user_id = ' . (int) $member->user_id . ' AND group_id = ' . (int) $member->jusergroup_id, 'OR');
		}
		
		try
		{
			$db->setQuery($query);
			$db->execute();
		}		
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;	
	}	
	
	public function setNotified($notified)
	{
		$date = JFactory::getDate();
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			 ->update('#__qazap_members');
		
		$when = array();
		
		foreach($notified as $id => $field_name)
		{
			$ids[] = $id;
			if(!isset($when[$field_name]))
			{
				$when[$field_name] = '';
			}			
			$when[$field_name] .= sprintf('WHEN %d THEN %d ', $id, 1);
		}
		
		foreach($when as $k=>$v)
		{
			$sql->set($db->quoteName($k) . ' = CASE '.$db->quoteName('id') . ' ' . $v . ' END');
		}
		
		$sql->set($db->quoteName('last_notified') . ' = ' . $db->quote($date->toSql()))		
			->where($db->quoteName('id').' IN ('.implode(',', $ids).')');
		
		try
		{
			$db->setQuery($sql);
			$db->execute();
		}
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}
	
	protected function sendMail($members, $onSave = false)
	{
		$data 		= isset($members['data']) ? $members['data'] : null;
		$isNew 		= isset($members['isNew']) ? $members['isNew'] : false;
		$miltple 	= isset($members['multiple']) ? $members['multiple'] : false;

		if($data)
		{
			$config = QZApp::getConfig();
			$app = JFactory::getApplication();
			$display_message = $app->isAdmin() ? true : false;			
			$mailModel = QZApp::getModel('Mail', array('ignore_request'=>true, 'display_message' => $display_message)); 
			
			$adminOnNew = $config->get('new_member_mail_to_admin', 0);
			$adminOnNotify = $config->get('member_notify_mail_to_admin', 0);
			$adminOnUpdate = $config->get('member_update_mail_to_admin', 0);
			$adminOnExpire = $config->get('member_expiration_mail_to_admin', 0);
			
			if($miltple)			
			{
				foreach($data as $member)
				{
					if(!$isNew && $member->status == 1)
					{
						if($onSave)
						{
							$mail_type = 'update';
							$sendAdmin = $adminOnUpdate;							
						}
						else
						{
							$mail_type = 'notify';
							$sendAdmin = $adminOnNotify;							
						}
					}
					elseif(!$isNew && $member->status == 0)
					{
						$mail_type = 'delete';
						$sendAdmin = $adminOnExpire;
					}
					else
					{
						$mail_type = 'new';
						$sendAdmin = $adminOnNew;
					}
					
					$user = JFactory::getUser($data->user_id);
					$mailData = array();
					$mailData['type'] = $mail_type;
					$mailData['data'] = (object)(array) $member;
					$mailData['email'] = $user->email;
					$mailData['data']->name = $user->name;
					if(!isset($mailData['data']->plan_name))
					{
						$mailData['data']->plan_name = QZDisplay::getMembershipNameByID($mailData['data']->membership_id);
					}	
					
					if(!$mailModel->send('Member', $mailData, $isNew))
					{
						JError::raiseWarning (1, $mailModel->getError());
					}
					
					if($sendAdmin)
					{
						$mailData['email'] = $app->getCfg('mailfrom');
						
						if(!$mailModel->send('Member', $mailData, $isNew))
						{
							JError::raiseWarning (1, $mailModel->getError());
						}						
					}
				}				
			}
			else
			{
				if(!$isNew && $data->status == 1)
				{
					if($onSave)
					{
						$mail_type = 'update';
						$sendAdmin = $adminOnUpdate;							
					}
					else
					{
						$mail_type = 'notify';
						$sendAdmin = $adminOnNotify;							
					}
				}
				elseif(!$isNew && $data->status == 0)
				{
					$mail_type = 'delete';
					$sendAdmin = $adminOnExpire;
				}
				else
				{
					$mail_type = 'new';
					$sendAdmin = $adminOnNew;
				}
				
				$user = JFactory::getUser($data->user_id);
				$mailData = array();
				$mailData['type'] = $mail_type;
				$mailData['data'] = (object)(array) $data;
				$mailData['email'] = $user->email;
				$mailData['data']->name = $user->name;		
				if(!isset($mailData['data']->plan_name))
				{
					$mailData['data']->plan_name = QZDisplay::getMembershipNameByID($mailData['data']->membership_id);
				}
									
				if(!$mailModel->send('Member', $mailData, $isNew))
				{
					JError::raiseWarning (1, $mailModel->getError());
				}
				
				if($sendAdmin)
				{
					$mailData['email'] = $app->getCfg('mailfrom');
					
					if(!$mailModel->send('Member', $mailData, $isNew))
					{
						JError::raiseWarning (1, $mailModel->getError());
					}						
				}						
			}
		}	
	}
}