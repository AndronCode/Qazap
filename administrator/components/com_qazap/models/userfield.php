<?php
/**
 * userfield.php
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
class QazapModelUserfield extends JModelAdmin
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
	public function getTable($type = 'Userfield', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.userfield', 'userfield', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.userfield.data', array());

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

		if (empty($table->id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_userfields');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	
	/*
	*
	* Save Userfield
	*
	*/		
	public function save($data)	
	{		
		$table = $this->getTable();

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		
		$timeStamp = time();
		$field_title = strtolower(str_replace(' ','_',$data['field_name']));
		$field_title = preg_replace('/[^A-Za-z0-9_]/', '', $field_title);
		
		if(empty($field_title)) 
		{
			$field_title = 'userfield_'.$timeStamp;
		}
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapsystem');
		//JPluginHelper::importPlugin('qazapuserfield');
				
		try
		{
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
						
			if($isNew) 
			{
				$data['field_title'] = $field_title;
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
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('userfield', &$data, $isNew));
			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}	
			
			// Also create new column in Userinfo table
			if($isNew && $data['field_type'] != 'fieldset') 
			{
				if(!$this->addColoumn($data))
				{
					$this->setError($this->getError());
					return false;
				}					
			}					
			
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}			
			

			$this->cleanCache();
			
			// Trigger the onContentAfterSave event.
			$dispatcher->trigger('onAfterSave', array('userfield', $data, $isNew));
			
			// Trigger the onContentAfterSave event.
			//$dispatcher->trigger('onAfterUserfieldSave', array($this->option . '.' . $this->name, $table, $isNew));		
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
	
	// Add Column in Userinfo Table
	
	protected function addColoumn($array)
	{
		$db = $this->getDbo();
		$max_length = isset($array['max_length']) ? $array['max_length'] : 255;

		// Add a new column to User Info Table		
		$sql = "ALTER TABLE `#__qazap_userinfos` ADD `" . (string)$array['field_title'] . "` VARCHAR(" . (int) $max_length . ")";
		
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
		
		// Add a new column to Order Address Table
		$sql = "ALTER TABLE `#__qazap_order_addresses` ADD `" . (string)$array['field_title'] . "` VARCHAR(" . (int) $max_length . ")";		
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
		
		return true;	
	}	
	
	
	// Drop Column in Userinfo Table
	
	protected function dropColumn($column)
	{
		$db = $this->getDbo();

		$sql = 'ALTER TABLE `#__qazap_userinfos` DROP `' . (string) $column . '`';
		
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
		
		$sql = 'ALTER TABLE `#__qazap_order_addresses` DROP `' . (string) $column . '`';
		
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
		
		return true;	
	}
	
	public function getStaticFields()
	{
		$staticFields = array(
											'first_name', 'middle_name', 'last_name', 'address_1', 
											'zip', 'city', 'country', 'states_territory', 'address_2', 
											'company', 'phone', 'fax', 'mobile', 'user_details', 
											'contact_information', 'title', 'email', 'address_name'
											);
							
		return $staticFields;
	}
	
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		
		if(!empty($pks))
		{
			$app = JFactory::getApplication();			
			$db = $this->getDbo();		
			
			$query = $db->getQuery(true)
								->select('id, field_name, field_title')
								->from('#__qazap_userfields')
								->where('id IN ('. implode(',', $pks). ')');
			try
			{
				$db->setQuery($query);
				$fields = $db->loadObjectList();			
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$protectedFields = $this->getStaticFields();
			$allowed = array();
			$skipped = array();
			$pks = array();
			
			if(!empty($fields))
			{
				foreach($fields as $field)
				{
					if(!in_array($field->field_title, $protectedFields))
					{
						$allowed[$field->id] = trim($field->field_title);
					}
					else
					{
						$skipped[] = $field->field_name;
					}
				}
			}	
			
			if(!empty($skipped))
			{
				if(count($skipped) == 1)
				{
					$msg = 'COM_QAZAP_ERROR_CORE_FIELD_DELETE';					
				}
				else
				{
					$msg = 'COM_QAZAP_ERROR_CORE_FIELDS_DELETE';
				}
				
				JLog::add(JText::sprintf($msg, implode(', ', $skipped)), JLog::WARNING, 'jerror');
			}
			
			if(!empty($allowed))
			{
				$dispatcher = JEventDispatcher::getInstance();
				$pks = array_keys($allowed);
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

							if (!$table->delete($pk))
							{
								$this->setError($table->getError());
								return false;
							}
							
							if (!$this->dropColumn($allowed[$pk]))
							{
								$this->setError($this->getError());
								return false;								
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

		return false;
	}
}