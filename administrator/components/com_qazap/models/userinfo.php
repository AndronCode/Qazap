<?php
/**
 * userinfo.php
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
class QazapModelUserinfo extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0.0
	 */
	protected $text_prefix = 'COM_QAZAP';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = $app->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);
		
		// Set type
		$address_type = strtolower($app->input->getString('type'));
		$this->setState($this->getName() . '.address_type', $address_type);
		
		// Load the parameters.
		$params	= QZApp::getConfig();
		$this->setState('params', $params);
	}	
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0
	 */
	public function getTable($type = 'Userinfo', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.userinfo', 'userinfo', array('control' => 'jform', 'load_data' => $loadData));
		
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.userinfo.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();            
		}
		
		return $data;
	}
	
	/**
	 * Add dynamic Userinfo form fields
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$address_type = $this->getState($this->getName() . '.address_type');
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$object = null;

		if(!empty($data))
		{
			$object = (object) $data;

			if(empty($address_type))
			{
				$address_type = $object->address_type ? $object->address_type : $form->getValue('address_type', 'bt');
			}
			
			$address_type = !empty($address_type) ? $address_type : 'bt';
			
			if($address_type == 'bt' && ($user->get('id') > 0) && (!isset($object->user_id) || empty($object->user_id)))
			{
				$object->address_type = $address_type;
				
				if($app->isSite())
				{
					$object->user_id = $form->getValue('user_id') ? $form->getValue('user_id') : $user->get('id');
				}				
			}
		}

		$fieldsets = QazapHelper::getUserFields($address_type);
		$form->setFields($fieldsets);
		
		if(($object !== null) && $form->getField('email') && ($user->get('id') > 0))
		{	
			if(($object->user_id > 0) && ($object->id == 0) && ($object->address_type == 'bt'))
			{
				$user = JFactory::getUser($object->user_id);
				$form->setValue('email', null, $user->get('email'));
				$form->setFieldAttribute('email', 'default', $user->get('email'), null);
				$form->setValue('user_id', null, $object->user_id);
			}
			elseif($object->id == 0 && $object->address_type == 'bt')
			{
				$form->setFieldAttribute('email', 'required', 'false', null);
				$form->setFieldAttribute('email', 'readonly', 'true', null);
				$form->setFieldAttribute('email', 'hint', 'COM_QAZAP_FIELD_EDITABLE_AFTER_SAVE', null);
			}
		}			
						
		parent::preprocessForm($form, $data, $group);
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
			$address_type = $this->getState($this->getName() . '.address_type');
			
			if($item->id > 0 && empty($address_type))
			{
				$this->setState($this->getName() . '.address_type', $item->address_type);
			}			
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
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_userinfos');
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
		$address_type = (!empty($data['address_type'])) ? $data['address_type'] : (int) $this->getState($this->getName() . '.address_type');
		$isNew = true;		
				
		try
		{
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
			if($isNew && $data['address_type'] == 'bt' && !$table->checkUserbillto($data['user_id']))
			{
				$this->setError($table->getError());
				return false;			
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('userinfo', &$data, $isNew));
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
			$this->prepareTable($table);

			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}			 
			
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}			

			$this->cleanCache();
			
			// Trigger the onContentAfterSave event.
			$dispatcher->trigger('onAfterSave', array('userinfo', $data, $isNew));		
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
}