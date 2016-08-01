<?php
/**
 * vendor_group.php
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
class QazapModelVendor_group extends JModelAdmin
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
	public function getTable($type = 'Vendor_group', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.vendor_group', 'vendor_group', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.vendor_group.data', array());

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

	/**
	* Prepare and sanitise the table prior to saving.
	*
	* @since	1.0.0
	*/
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->vendor_group_id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_vendor_groups');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	public function save($data)
	{	
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(v.vendor_group_id)')
					->from('#__qazap_vendor_groups AS v')
					->where('v.title = '. $db->quote($data['title']))
					->where('v.vendor_group_id != ' . (int) $data['vendor_group_id']);
		
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
		
		if ($result)
		{
			$this->setError(JText::_('COM_QAZAP_MEMBERSHIP_ERROR_TITLE_EXISTS'));
			return false;
		}	
		
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models/');
		
		$jgroup_model = JModelLegacy::getInstance('group', 'UsersModel', array('ignore_request' => true));		
		$jgroup_table = $this->getTable('Usergroup', 'JTable');
		
		$groupData = array(
										"id" => $data['jusergroup_id'],
										"title" => $data['title'],
										"parent_id" => 4,
										"metadata" => array("tags"=>'')
									);
		
		if(!$jgroup_model->save($groupData))
		{
			$this->setError($jgroup_model->getError());
			return false;
		}
		
		if(!$data['vendor_group_id'] || !$data['jusergroup_id'])
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
			"title" => $data['title'],
			"rules" => array(8, (int)$data['jusergroup_id'])
		);
			   
		if(!$jlevel_model->save($levelData))
		{
			if(!$data['vendor_group_id'])
			{
				$jgroup_model->delete($data['jusergroup_id']);
			}
			
			$this->setError($jlevel_model->getError());
			return false;
		}
		 
		if(!$data['vendor_group_id'] || !$data['jview_id'])
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
		$result = $dispatcher->trigger('onBeforeSave', array('vendor_group', &$data, $isNew));
		
		if (in_array(false, $result, true))
		{
			$this->setError($dispatcher->getError());
			return false;
		}


		// Proceed with the save
		return parent::save($data);
	}

	public function delete(&$pks)
	{
			
		// Typecast variable.
		$pks = (array) $pks;
		
		$db = JFactory::getDBO();
		if(!class_exists('UsersModelGroup'))require(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_users'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'group.php');
		
		$jgroup_model = new UsersModelGroup();	
		
		$query = $db->getQuery(true)
					->select('jusergroup_id')
					->from('#__qazap_vendor_groups')
					->where('vendor_group_id IN ('.implode(',',$pks).')');
		$db->setQuery($query);
		$jgroupIds = $db->loadObjectList();
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
							->from('#__qazap_vendor_groups')
							->where('vendor_group_id = '.$pk);
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
}
