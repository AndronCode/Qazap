<?php
/**
 * vendor.php
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
class QazapModelVendor extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0.0
	 */
	protected $text_prefix = 'COM_QAZAP';

	/**
	* Returns a reference to the a Table object, always creating it.
	*
	* @param	type	The table type to instantiate
	* @param	string	A prefix for the table class name. Optional.
	* @param	array	Configuration array for model. Optional.
	* @return	JTable	A database object
	* @since	1.0.0
	*/
	public function getTable($type = 'Vendor', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_qazap.vendor', 'vendor', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.vendor.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();			

			if(isset($data->category_list) && is_string($data->category_list))
			{
				$data->category_list = json_decode($data->category_list);
			}

			if(isset($data->shipment_methods) && is_string($data->shipment_methods))
			{
				$data->shipment_methods = json_decode($data->shipment_methods);
			}
		}
		return $data;
	}

	/**
	* Method to get a single record.
	*
	* @param	integer	The id of the primary key.
	*
	* @return	mixed	Object on success, false on failure.
	* @since	1.0.0
	*/
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{
			//Do any procesing on fields here if needed
		}

		return $item;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$fieldsets = QazapHelper::getVendorFields();
		
		$form->setFields($fieldsets);
		
		parent::preprocessForm($form, $data, $group);
	}	

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_vendor');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	/**
	* 
	* @ Save vendor Fields
	* 
	*/
	public function save($data)	
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$table = $this->getTable();
		$key = $table->getKeyName();		
		$dispatcher = JEventDispatcher::getInstance();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		$oldState = null;
		$oldCategories = null;
		$oldGroup = null;
		$params = $this->getState('params') ? $this->getState('params') : QZApp::getConfig();
		
		JPluginHelper::importPlugin('qazapusers');
		
		if(isset($data['shipment_methods']) && !empty($data['shipment_methods']))
		{
			$data['shipment_methods'] = array_map('intval', explode(',', $data['shipment_methods']));			
		}

		if(isset($data['category_list']) && !empty($data['category_list']))
		{
			$data['category_list'] = array_map('intval', explode(',', $data['category_list']));			
		}				

		if(!$this->checkData($data))
		{
			$this->setError($this->getError());
			return false;
		}
			
		try
		{
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
				$oldGroup = $table->vendor_group_id;
				$oldCategories = $table->category_list;
				$oldState = $table->state;
			}

			if($table->get('state') != $data['state'])
			{
				$stateUpdate = true;
			}

			if($isNew && !$table->checkOwner($data['vendor_admin']))
			{
				$this->setError($table->getError());
				return false;			
			}
			
			if ($input->get('task') == 'save2copy')
			{
				list($title, $alias) = $this->generateNewTitleAlias($data['alias'], $data['shop_name']);
				$data['shop_name'] = $title;
				$data['alias'] = $alias;
				$data['state'] = 0;
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('vendor', &$data, $isNew));
			
			if (in_array(false, $result, true))
			{
				$this->setError($dispatcher->getError());
				return false;
			}			
					
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}
			
			if($app->isSite()&& !$isNew && $params->get('vendor_admin_approval', 1) && $oldState == 1)
			{
				if(($table->category_list != $oldCategories) || ($table->vendor_group_id != $oldGroup))
				{
					$table->state = 0;
				}
			}
			
			$this->prepareTable($table);

			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}			 
			
			if(!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			
			if(!$this->updateUsergroup($data, $oldGroup))
			{
				$this->setError($this->getError());
				return false;				
			}
					
			if($isNew || (!$isNew && $oldState != $table->state))
			{
				// Get Mail Model
				$mail = $this->getInstance('mail', 'QazapModel');
				// Try to send mail	
				$display_message = $app->isAdmin() ? true : false;
				$mailModel = QZApp::getModel('Mail', array('ignore_request' => true, 'display_message' => $display_message));
							
				if(!$mailModel->send('vendor', $data, $table->id))
				{
					$app->enqueueMessage($mailModel->getError(), 'error');
				}				
			}
			
			$this->cleanCache();			
			// Mail function call.
			
			// Trigger the onContentAfterSave event.
			$dispatcher->trigger('onAfterSave', array('vendor', $data, $isNew));		
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
	
	
	protected function updateUsergroup($data, $old_group_id)
	{
		$vendor_admin = (int) $data['vendor_admin'];
		$group_id = (int) $data['vendor_group_id'];
		
		if(!$vendor_admin)
		{
			$this->setError(JText::_('COM_QAZAP_VENDOR_ERROR_NO_VENDOR_ADMIN'));
			return false;
		}
		
		if(!$group_id)
		{
			$this->setError(JText::_('COM_QAZAP_VENDOR_ERROR_NO_VENDOR_GROUP'));
			return false;			
		}
		
		if(!$groups = $this->getGroups(array($old_group_id, $group_id)))
		{
			$this->setError($this->getError());
			return false;
		}

		$user = JFactory::getUser($vendor_admin);
		$old_usergroup = isset($groups[$old_group_id]) ? $groups[$old_group_id]->jusergroup_id : null;
		$new_usergroup = isset($groups[$group_id]) ? $groups[$group_id]->jusergroup_id : null;

		if(($old_usergroup == $new_usergroup) && in_array($new_usergroup, $user->groups))
		{
			return true;
		}
		
		if(($key = array_search($old_usergroup, $user->groups)) !== false) 
		{
			unset($user->groups[$key]);
		}
		
		if(!in_array($new_usergroup, $user->groups))
		{
			$user->groups[] = $new_usergroup;
		}		

		if(!$user->save())
		{
			$this->setError($user->getError());
			return false;
		}		
		
		return true;
	}
	
	protected function getGroups($group_ids)
	{
		$group_ids = (array) $group_ids;
		$group_ids = array_filter(array_unique($group_ids));
		$group_ids = array_values($group_ids);

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('vendor_group_id, jusergroup_id')
			->from('#__qazap_vendor_groups');
			
		if(count($group_ids) == 1)
		{
			$query->where('vendor_group_id = ' . (int) $group_ids[0]);	
		}
		else
		{
			$query->where('vendor_group_id IN (' . implode(',', $group_ids) . ')');
		}

		try 
		{
			$db->setQuery($query);
			$groups = $db->loadObjectList('vendor_group_id');				
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(empty($groups))
		{
			$this->setError(JText::_('COM_QAZAP_VENDOR_ERROR_INVALID_VENDOR_GROUP'));
			return false;			
		}
		
		return $groups;		
			
	}
	
	
	public function getCommission($vendor_id)
	{
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
					->select('a.commission')
					->from('#__qazap_vendor_groups AS a')
					->join('LEFT', '#__qazap_vendor AS b ON b.vendor_group_id = a.vendor_group_id')
					->where('b.id = '. (int) $vendor_id);
		try 
		{
			$db->setQuery($sql);
			$commission = $db->loadResult();				
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}

		return $commission;
	}

	/**
	* Activate or block products
	* 
	* @param primary key or Array of primary keys. $pks
	* @param active = 1, block = 0. $value
	* 
	* @return boolean (true/false)
	*/	
	public function activate(&$pks, $value = 1)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$pks = (array) $pks;
		$vendorMails = array();

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}
				
				if($table->state != $value)
				{
					$data = JArrayHelper::fromObject($table);
					$data['category_list'] = !empty($data['category_list']) && is_string($data['category_list']) ? 
												json_decode($data['category_list'], true) : array();
					$data['shipment_methods'] = !empty($data['shipment_methods']) && is_string($data['shipment_methods']) ? 
												json_decode($data['shipment_methods'], true) : array();
					$data['image'] = !empty($data['image']) && is_string($data['image']) ? 
										json_decode($data['image'], true) : array();
					$vendorMails[] = array('data' => (array) $data, 'id' => $table->id);
				}				
			}
		}

		// Attempt to change the state of the records.
		if (!$table->activate($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());
			return false;
		}
		
		if(!empty($vendorMails))
		{
			foreach($vendorMails as $vendorMail)
			{
				$display_message = $app->isAdmin() ? true : false;
				$mailModel = QZApp::getModel('Mail', array('ignore_request' => true, 'display_message' => $display_message));
			
				if(!$mailModel->send('vendor', $vendorMail['data'], $vendorMail['id']))
				{
					$app->enqueueMessage($mailModel->getError(), 'error');
				}				
			}
		}
		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
	
	protected function generateNewTitleAlias($alias, $title, $language = null)
	{
		// Alter the title & alias
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(alias)')
					->from($db->quoteName('#__vendor'))
					->where('alias = '. $db->quote($alias));
						
		try 
		{
			$db->setQuery($query);
			$result = $db->loadResult();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}	
				
		if ($result)
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}
		
		return array($title, $alias);
	}
	
	protected function checkData(&$data)
	{
		$existingAliases = $this->getAliasExists($data['id']);
		
		if($existingAliases === false)
		{
			$this->setError($this->getError());
			return false;
		}		
		
		if (trim($data['shop_name'] == ''))
		{
			$this->setError(JText::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));
			return false;
		}

		if (!isset($data['alias']) || empty($data['alias']))
		{
			$data['alias'] = $data['shop_name'];
		}

		$data['alias'] = JApplication::stringURLSafe($data['alias']);
		
		if(in_array($data['alias'], $existingAliases))
		{
			$data['alias'] = JString::increment($data['alias'], 'dash');
		}

		if (trim(str_replace('-', '', $data['alias'])) == '')
		{
			$data['alias'] = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
		
		return true;			
	}
	
	/**
	* Get existing Alias List 
	* 
	* @param For which table $type
	* @param Alias field name $field
	* @param language specific $langauge
	* 
	* @return
	*/	
	protected function getAliasExists($skipID = false)
	{
		$skipID = (int) $skipID;
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('alias')
					->from('#__qazap_vendor');
					
		if(!empty($skipID))
		{
			$query->where($db->quoteName('id').' <> ' . $skipID);
		}
		try 
		{
			$db->setQuery($query);
			$result = $db->loadColumn();		
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(empty($result))
		{
			return array();
		}
		
		return $result;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;

					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());
						return false;
					}
					
					// Hold the vendor admin user id and vendor group before we try to delete the vendor.
					$vendor_admin = $table->vendor_admin;
					$vendor_group_id = $table->vendor_group_id;
					
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
					
					// Get the Joomla user group with respected to the vendor group.
					$groups = $this->getGroups($vendor_group_id);
					
					if($groups !== false)
					{
						// Load the user
						$user = JFactory::getUser($vendor_admin);
						$usergroup = isset($groups[$vendor_group_id]) ? $groups[$vendor_group_id]->jusergroup_id : null;
						
						// Nothing to do if the user does not exists in Joomla User table or in absence of $usergroup
						if(!empty($user->id) && !empty($usergroup))
						{							
							if(($key = array_search($usergroup, $user->groups)) !== false) 
							{
								unset($user->groups[$key]);
								
								if(!$user->save())
								{
									$this->setError($user->getError());
									return false;
								}									
							}						
						}					
					}

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					if ($error)
					{
						JLog::add($error, JLog::WARNING, 'jerror');
						return false;
					}
					else
					{
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
						return false;
					}
				}

			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
	
}