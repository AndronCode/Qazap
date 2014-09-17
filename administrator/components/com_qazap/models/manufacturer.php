<?php
/**
 * manufacturer.php
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
class QazapModelManufacturer extends JModelAdmin
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
	public function getTable($type = 'Manufacturer', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.manufacturer', 'manufacturer', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.manufacturer.data', array());

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
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_manufacturers');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	public function save($data)
	{
		$input = JFactory::getApplication()->input;
		$table = $this->getTable();
		$key = $table->getKeyName();	
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
			
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
			}
			
			if ($input->get('task') == 'save2copy')
			{
				list($title, $alias) = $this->generateNewTitleAlias($data['alias'], $data['manufacturer_category_name']);
				$data['manufacturer_category_name'] = $title;
				$data['alias'] = $alias;
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('manufacturer', &$data, $isNew));
			
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
				$this->setError($dispatcher->getError());
				return false;
			}			
			
			
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger('onAfterSave', array('manufacturer', $data, $isNew));	
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
	
	protected function generateNewTitleAlias($alias, $title, $language = null)
	{
		// Alter the title & alias
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('COUNT(alias)')
					->from($db->quoteName('#__qazap_manufacturers'))
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
		
		if (trim($data['manufacturer_name'] == ''))
		{
			$this->setError(JText::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));
			return false;
		}

		if (trim($data['alias']) == '')
		{
			$data['alias'] = $data['manufacturer_name'];
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
					->from('#__qazap_manufacturers');
					
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
}