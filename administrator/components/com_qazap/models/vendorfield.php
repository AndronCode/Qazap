<?php
/**
 * vendorfield.php
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
class QazapModelVendorfield extends JModelAdmin
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
	public function getTable($type = 'Vendorfield', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.vendorfield', 'vendorfield', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.vendorfield.data', array());

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
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_vendorfields');
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
		$dispatcher = JEventDispatcher::getInstance();
		$table = $this->getTable();

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		$timeStamp = time();
		$field_title = strtolower(str_replace(' ','_',$data['field_name']));
		$field_title = preg_replace('/[^A-Za-z0-9_]/', '', $field_title);

		if(empty($field_title)) 
		{
			$field_title = 'vendor_field_'.$timeStamp;
		}
		
			
		//JPluginHelper::importPlugin('qazapuserfield');
		JPluginHelper::importPlugin('qazapsystem');
				
		try
		{
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
						
			if($isNew) 
			{
				if($this->fieldExists($field_title))
				{
					$this->setError(JText::_('COM_QAZAP_ERROR_VENDOR_FIELD_ALREADY_EXIST'));
					return false;
				}
				
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
			$result = $dispatcher->trigger('onBeforeSave', array('vendorfield', &$data, $isNew));
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
				if(!$this->dropColumn($table->field_title)) 
				{
					JFactory::getApplication()->enqueueMessage($this->getError(), 'error');					
				}
								
				$this->setError($table->getError());
				return false;
			}			

			$this->cleanCache();
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onAfterSave', array('vendorfield', &$data, $isNew));
			
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
	
	// Delete vendorfield
	
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		$table = $this->getTable();
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;
					
					$staticFields = $this->getStaticFields();

					if(in_array($table->field_title, $staticFields))
					{
						$this->setError(JText::_('COM_QAZAP_CORE_FIELD_CANNOT_BE_DELETED'));
						return false;
					}
					
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
					// Trigger After Delete event.
					if(!$this->dropColumn($table->field_title)) 
					{
						$this->setError($this->getError());
						return false;
					}
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
	
	// Add Column in Userinfo Table
	
	protected function addColoumn($array)
	{
		$db = $this->getDbo();
		$sql = "ALTER table" . $db->quoteName('#__qazap_vendor') . " ADD " . $db->quoteName($array['field_title']) . " VARCHAR(" . (int) $array['max_length'] . ")";		
		$db->setQuery($sql);
		
		if (!$result = $db->execute())
		{
			$this->setError($db->getErrorNum(). ':' . $db->getErrorMsg());
			return false;
		}
		
		return $result;	
	}
	
	/**
	* 
	* @ DROP COLUMN
	* 
	*/
	public function getStaticFields()
	{
		$staticFields = array(
							"firstname", "lastname", "address1", "address2", "country", "email", "mobile" , "states", "fax", "shop_description", "image");
							
		return $staticFields;
	}
	
	
	protected function dropColumn($column)
	{
		$db = $this->getDbo();
	
		$sql = 'ALTER TABLE'. $db->quoteName('#__qazap_vendor') . ' DROP ' . $db->quoteName($column);
		$db->setQuery($sql);
		
		if (!$result = $db->execute())
		{
			$this->setError($db->getErrorNum(). ':' . $db->getErrorMsg());
			return false;
		}				

		return $result;	
	}
	
	
	public function fieldExists($fieldName)
	{
		$db = $this->getDbo();
		// Lookup the fields for this table only once.
		$fields = $db->getTableColumns('#__qazap_vendor', true);

		if (array_key_exists($fieldName, $fields))
		{
			throw new UnexpectedValueException(sprintf('columns exist for %s table', '#__qazap_vendorfields'));
			return true;
		}

		return false;
	}	
}