<?php
/**
 * membership.php
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
class QazapModelMembership extends JModelAdmin
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
	public function getTable($type = 'Membership', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.membership', 'membership', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.membership.data', array());

		if(empty($data)) 
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
	 * @since	1.0.0
	 */
	public function getItem($pk = null)
	{
		if($item = parent::getItem($pk)) 
		{
			$item->access_to_members = (!empty($item->access_to_members) && is_string($item->access_to_members)) ? json_decode($item->access_to_members, true) : null;
		}

		return $item;
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
				$db = $this->getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_memberships');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	public function save($data)
	{
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
				
		if($pk > 0 && ($data['state'] != 1) && !$table->checkForMembers($pk))
		{
			$this->setError($table->getError());
			return false;
		}
		
		if(!isset($data['access_to_members']) || empty($data['access_to_members']))
		{
			$data['access_to_members'] = '';
		}
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(m.id)')
					->from('#__qazap_memberships AS m')
					->where('m.plan_name = ' . $db->quote($data['plan_name']))
					->where('m.id <> ' . (int) $data['id']);
		
		try 
		{
			$db->setQuery($query);
			$result = $db->loadResult();
		} 
		catch (Expection $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(!empty($result))
		{
			$this->setError(JText::_('COM_QAZAP_MEMBERSHIP_ERROR_TITLE_EXISTS'));
			return false;
		}	
		
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models/');
		
		$jgroup_model = JModelLegacy::getInstance('group', 'UsersModel', array('ignore_request' => true));

		$groupData = array(
									"id" => $data['jusergroup_id'],
									"title" => $data['plan_name'],
									"parent_id" => 2,
									"metadata" => array("tags"=>'')
		 						);
		
		if(!$jgroup_model->save($groupData))
		{
			$this->setError($jgroup_model->getError());
			return false;
		}
		
		if(!$data['id'] || !$data['jusergroup_id'])
		{
			$query->clear()
						->select('max(id)')
						->from('#__usergroups');
						
			$db->setQuery($query);
			$data['jusergroup_id'] = $db->loadResult();			
		}		
				
		// Save View Level //	
		$jlevel_model = JModelLegacy::getInstance('level', 'UsersModel', array('ignore_request' => true));
		$levelData = array(
										"id" => $data['jview_id'],
										"title" => $data['plan_name'],
										"rules" => array(8, $data['jusergroup_id'])
									);
			   
		if(!$jlevel_model->save($levelData))
		{
			if(!$data['id'])
			{
				$jgroup_model->delete($data['jusergroup_id']);
			}
			$this->setError($jlevel_model->getError());
			return false;
		}
		 
		if(!$data['id'] || !$data['jview_id'])
		{
			$query->clear()
						->select('max(id)')
						->from('#__viewlevels');
			$db->setQuery($query);
			$data['jview_id'] = $db->loadResult();			
		}		
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapsystem');
		
		// Trigger the onEventBeforeSave event.
		$result = $dispatcher->trigger('onBeforeSave', array('membership', &$data, ($pk > 0)));

		// Proceed with the save
		return parent::save($data);
	}

	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;
		$db = $this->getDbo();
		
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models/');
		
		$jgroup_model = JModelLegacy::getInstance('group', 'UsersModel', array('ignore_request' => true));

		$query = $db->getQuery(true)
								->select('jusergroup_id')
								->from('#__qazap_memberships')
								->where('id IN (' . implode(',', $pks) . ')');
		try
		{
			$db->setQuery($query);
			$jgroupIds = $db->loadObjectList();			
		}						
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$jIds = array();
		foreach($jgroupIds as $jgroupId)
		{
			$jIds[] = $jgroupId->jusergroup_id;
		}
		
		if(!$jgroup_model->delete($jIds))
		{
			JError::raiseWarning(403, JText::_('COM_USERS_DELETE_ERROR_INVALID_GROUP'));
			return false;			
		}
		
		$user	= JFactory::getUser();
		$groups = JAccess::getGroupsByUser($user->get('id'));
		
		// Get a row instance.
		$table = $this->getTable();

		// Load plugins.
		JPluginHelper::importPlugin('user');
		$dispatcher = JEventDispatcher::getInstance();

		// Check if I am a Super Admin
		$iAmSuperAdmin	= $user->authorise('core.admin');

		// do not allow to delete groups to which the current user belongs
		foreach ($jIds as $i => $jId)
		{
			if (in_array($jId, $groups))
			{
				JError::raiseWarning(403, JText::_('COM_USERS_DELETE_ERROR_INVALID_GROUP'));
				return false;
			}
		}
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				$allow = $user->authorise('core.edit.state', 'com_qazap');
				// Don't allow non-super-admin to delete a super admin
				$query = $db->getQuery(true)
				->select('jusergroup_id')
				->from('#__qazap_memberships')
				->where('id = '.$pk);
				$db->setQuery($query);
				$jgrpId = $db->loadResult();		
				
				$allow = (!$iAmSuperAdmin && JAccess::checkGroup($jgrpId, 'core.admin')) ? false : $allow;

				if ($allow)
				{
					// Fire the onUserBeforeDeleteGroup event.
					$dispatcher->trigger('onUserBeforeDeleteGroup', array($table->getProperties()));
					//$this->setError(JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
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
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}
			} 
			else 
			{
				$this->setError($table->getError());
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Custom clean the cache of com_qazapt and access control plugin cache
	 *
	 * @since   1.0.0
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_qazap');
		parent::cleanCache('com_qazap_membership_access');
	}	
}