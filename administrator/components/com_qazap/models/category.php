<?php
/**
 * category.php
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
class QazapModelCategory extends JModelAdmin
{
	
	/**
	* @var    string  The prefix to use with controller messages.
	* @since  1.0.0
	*/
	protected $text_prefix 		= 'COM_QAZAP';	
	
	/**
	 * The type alias for this content type. Used for content version history.
	 *
	 * @var      string
	 * @since    1.0.0
	 */
	public $typeAlias 			= null;

	protected $details_table 	= '#__qazap_category_details';
	
	protected $mainPKname		= 'category_id';
	
	protected $detailsPKname 	= 'category_details_id';

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->typeAlias = 'com_qazap.category';
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object   $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.0.0
	 */
	protected function canDelete($record)
	{
		if (!empty($record->category_id))
		{
			if ($record->published != -2)
			{
				return;
			}
			$user = JFactory::getUser();

			return $user->authorise('core.delete', 'com_qazap.category.' . (int) $record->category_id);
		}
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object   $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.0.0
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing category.
		if (!empty($record->category_id))
		{
			return $user->authorise('core.edit.state', 'com_qazap.category.' . (int) $record->category_id);
		}
		// New category, so check against the parent.
		elseif (!empty($record->parent_id))
		{
			return $user->authorise('core.edit.state', 'com_qazap.category.' . (int) $record->parent_id);
		}
		// Default to component settings if neither category nor parent known.
		else
		{
			return $user->authorise('core.edit.state', 'com_qazap');
		}
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.0.0
	 */
	public function getTable($type = 'Categories', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		$parentId = $app->input->getInt('parent_id');
		$this->setState('category.parent_id', $parentId);

		// Load the User state.
		$pk = $app->input->getInt('category_id');
		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_qazap');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a category.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    Category data object on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk))
		{
			// Prime required properties.
			if (empty($result->category_id))
			{
				$result->parent_id = $this->getState('category.parent_id');
			}

			if($result->category_id && !$this->getDetails($result->category_id, $result))
			{
				$this->setError($this->getError());
				return false;
			}

			// Convert the created and modified dates to local user time for display in the form.
			$tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if ((int) $result->created_time)
			{
				$date = new JDate($result->created_time);
				$date->setTimezone($tz);
				$result->created_time = $date->toSql(true);
			}
			else
			{
				$result->created_time = null;
			}

			if ((int) $result->modified_time)
			{
				$date = new JDate($result->modified_time);
				$date->setTimezone($tz);
				$result->modified_time = $date->toSql(true);
			}
			else
			{
				$result->modified_time = null;
			}

			if (!empty($result->category_id))
			{
				$result->tags = new JHelperTags;
				$result->tags->getTagIds($result->category_id, 'com_qazap.category');
			}
		}

		return $result;
	}
	
	/**
	* Load all details for a product.
	* 
	* @param product_id Product id (int)
	* @param item Already loaded product data (JObject)
	* 
	* @return
	*/

	protected function getDetails($category_id = 0, &$item)
	{		
		if(!$category_id || !is_object($item))
		{
			return false;
		}
		
		$tags = JLanguageHelper::getLanguages('lang_code');
		$db = $this->getDbo();
		$tags = array_keys($tags);
		// Initialise the query.
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName($this->details_table));
		$query->where($db->quoteName($this->mainPKname) . ' = ' . $db->quote($category_id));
		$query->where($db->quoteName('language') . ' IN (' . implode(',', $db->quote($tags)) . ')');
		
		try 
		{
			$db->setQuery($query);
			$rows = $db->loadAssocList('language');
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		// Check that we have a result.
		if (empty($rows))
		{
			return true;
		}
		else
		{
			foreach($rows as $tag=>$row) 
			{
				$ignore = array($this->mainPKname, 'language');
				foreach($row as $k => $v)
				{	
					if($k == $this->detailsPKname)
					{
						if(!isset($item->language))
						{
							$item->language = new stdClass;
						}
						$item->language->$tag = $v;
					}
					elseif($k == 'metadata')
					{
						$metadata = json_decode($v);
						if(!is_array($metadata) && empty($metadata)) continue;
						foreach($metadata as $mk => $mv)
						{
							if(!isset($item->$mk))
							{
								$item->$mk = new stdClass;
							}
							$item->$mk->$tag = $mv;
						}
					}
					elseif(!in_array($k, $ignore))
					{
						if(!isset($item->$k))
						{
							$item->$k = new stdClass;
						}
						$item->$k->$tag = $v;;
					}			
				}				
			}
			return true;			
		}		
	}	

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$jinput = JFactory::getApplication()->input;
		// Get the form.
		$form = $this->loadForm('com_qazap.category', 'category', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		$user = JFactory::getUser();
		if (!$user->authorise('core.edit.state', 'com_qazap.category.' . $jinput->get('category_id')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.' . $this->getName() . '.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_qazap.category', $data);

		return $data;
	}

	/**
	 * Method to get language specific dynamic form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */	
	protected function getDetailsForm()
	{
		$columns = array('path'=>'text', 'title'=>'text', 'alias'=>'text', 'description'=>'editor', 'metadesc'=>'textarea', 'metakey'=>'textarea', 'language'=>'hidden', 'page_title'=>'text', 'author'=>'text', 'robots'=>'qazaprobots');
		
		$app = JFactory::getApplication();
		$languages = JLanguageHelper::getLanguages('lang_code');
		
		$form = new SimpleXMLElement('<form></form>');	
		
		foreach($columns as $name => $type)
		{
			$fields = $form->addChild('fields');
			$fields->addAttribute('name', $name);
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', $name.'_set');
				
			foreach($languages as $tag => $language)
			{				
				$field = $fieldset->addChild('field');
				$field->addAttribute('name', $tag);
				
				if($name == 'title')
				{
					$field->addAttribute('required', 'required');
					$field->addAttribute('class', 'inputbox input-xxlarge input-large-text');
					$field->addAttribute('size', '40');
				}
				elseif($name == 'path')
				{
					$field->addAttribute('readonly', 'true');
					$field->addAttribute('class', 'readonly');
					$field->addAttribute('size', '40');
				}				
				elseif($name == 'alias')
				{
					$field->addAttribute('hint', 'COM_QAZAP_FORM_ALIAS_PLACEHOLDER');
					$field->addAttribute('size', '45');
				}
				elseif($name == 'description')
				{
					$field->addAttribute('class', 'inputbox');
					$field->addAttribute('filter', 'JComponentHelper::filterText');
					$field->addAttribute('buttons', 'true');
					$field->addAttribute('hide', 'readmore,pagebreak');
				}
				elseif($name == 'metadesc' || $name == 'metakey')
				{
					$field->addAttribute('rows', '3');
					$field->addAttribute('cols', '40');
				}									

				$field->addAttribute('type', $type);
				$field->addAttribute('language', $tag);
				$field->addAttribute('language_name', $language->title);

				if($name == 'author')
				{
					$field->addAttribute('label', 'JAUTHOR');
					$field->addAttribute('description', 'JFIELD_METADATA_AUTHOR_DESC');						
				}
				elseif($name == 'robots')
				{
					$field->addAttribute('label', 'JFIELD_METADATA_ROBOTS_LABEL');
					$field->addAttribute('description', 'JFIELD_METADATA_ROBOTS_DESC');						
				}				
				else
				{
					$field->addAttribute('label', 'COM_QAZAP_CATEGORY_FORM_LBL_'.strtoupper($name));
					$field->addAttribute('description', 'COM_QAZAP_CATEGORY_FORM_DESC_'.strtoupper($name));						
				}			
			}
		}
		return $form;
	}

	/**
	 * Method to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import.
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   1.0.0
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		jimport('joomla.filesystem.path');

		$detailsForm = $this->getDetailsForm($form);
		$form->load($detailsForm, false);

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array    $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$table = $this->getTable();
		$input = JFactory::getApplication()->input;
		$pk = (!empty($data['category_id'])) ? $data['category_id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		if(!$this->checkData($data))
		{
			$this->setError($this->gerError());
			return false;
		}	

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->parent_id != $data['parent_id'] || $data['category_id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$languages = JLanguageHelper::getLanguages('lang_code');					
			foreach($languages as $tag => $language)
			{
				list($title, $alias) = $this->generateNewTitleAlias($data['parent_id'], $tag, $data['alias'][$tag], $data['title'][$tag]);
				$data['title'][$tag] = $title;
				$data['alias'][$tag] = $alias;
				$data['language'][$tag] = '';
				$data['published'] = 0;	
			}
		}
		// Include the qazap plugins for the on save events.
		JPluginHelper::importPlugin('qazapsystem');
				
		// Trigger the onEventBeforeSave event.
		$result = $dispatcher->trigger('onBeforeSave', array('category', &$data, $isNew));		

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());
			return false;
		}

		// Bind the rules.
		if (isset($data['rules']))
		{
			$rules = new JAccessRules($data['rules']);
			$table->setRules($rules);
		}

		// Check the data.
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

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}
		
		if(!$this->saveDetails($data, $table->category_id))
		{
			$this->setError($this->getError());
			return false;				
		}

		// Trigger the onEventBeforeSave event.
		$result = $dispatcher->trigger('onAfterSave', array('category', $data, $isNew));

		// Rebuild the path for the category:
		if (!$path = $this->rebuildPath($table->category_id))
		{
			$this->setError($this->getError());
			return false;
		}

		// Rebuild the paths of the category's children:
		if (!$table->rebuild($table->category_id, $table->lft, $table->level, $path))
		{
			$this->setError($table->getError());
			return false;
		}

		$this->setState($this->getName() . '.id', $table->category_id);

		// Clear the cache
		$this->cleanCache();

		return true;
	}
	
	/*
	* Bind and save Category Details
	* 
	* @data For submission data array.
	* @category_id - Category ID (integer)
	*/
	protected function saveDetails($data, $category_id = 0)
	{
		if(empty($data) || !$category_id)
		{
			$this->setError('Invalid data saveDetails().');
			return false;
		}
		// Unset language data if exists
		//if(isset($data['language'])) unset($data['language']);
		$languages = JLanguageHelper::getLanguages('lang_code');
		$db = $this->getDbo();
		// Process Details Fields
		$metaDataFields = array('page_title', 'author', 'robots');
		$data['metadata'] = array();
		foreach($metaDataFields as $metaDataField)
		{
			if(isset($data[$metaDataField])) 
			{
				foreach($languages as $tag => $language)
				{
					$data['metadata'][$tag][$metaDataField] = $data[$metaDataField][$tag];
				}				
			}
		}
		
		$fields = array($this->mainPKname, 'path', 'title', 'alias', 'description', 'metadesc', 'metakey', 'metadata');
		
		$update = false;
		$updateCase = array();
		$insert = false;
		$insertColumns = array('language');
		$insertData = array();
		
		foreach ($languages as $tag => $language)
		{	
			// Check and prepare data for Update and Insert		
			if(isset($data['language'][$tag]) && $data['language'][$tag] > 0) 
			{				
				$id = $data['language'][$tag];
				$updateCase['language'][$id] = $tag;
				foreach($data as $name => $value)
				{
					if(!in_array($name, $fields)) {continue;}
					if(!isset($updateCase[$name])) 
					{
						$updateCase[$name] = array();
					}
					if($name == $this->mainPKname) 
					{
						$updateCase[$name][$id] = $category_id;
					} 
					else 
					{
						$updateCase[$name][$id] = isset($value[$tag]) ? $value[$tag] : '';
					}					
				}
				$update = true;
			}
			else
			{
				$tmp = array();				
				$tmp['language'] = $tag;				
				foreach($data as $name => $value)
				{
					if(!in_array($name, $fields)) {continue;}
					if(!in_array($name, $insertColumns))
					{
						$insertColumns[] = $name;
					}
					if($name == $this->mainPKname) 
					{
						$tmp[$name] = $category_id;
					} 
					else 
					{					
						$tmp[$name] = isset($value[$tag]) ? $value[$tag] : '';
					}
				}
				$tmp['metadata'] = json_encode($tmp['metadata']);
				$insert = true;
				$insertData[] = implode(',', $db->Quote($tmp));
				unset($tmp);				
			}
		}			
		
		if($update)
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName($this->details_table));
			$ids = array();
			//print_r($updateCase);exit;
			foreach($updateCase as $field_name => $values)
			{	
				$when = '';			 
				foreach($values as $id => $value) 
				{
					if(!in_array($id, $ids))
					{
						$ids[] = $id;
					}
					if($field_name == 'metadata')
					{
						$value = json_encode($value);
					}
					$when .= sprintf('WHEN %d THEN %s ', $id, $db->quote($value));
				}
				$query->set($db->quoteName($field_name) .' = CASE '.$db->quoteName($this->detailsPKname).' '.$when.' END');
			}
			$query->where($db->quoteName($this->detailsPKname).' IN ('.implode(',', $ids).')');
			$db->setQuery($query);

			try 
			{
				$db->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}				
		}
		
		if($insert)
		{
			$query = $db->getQuery(true);
			$query->insert($db->quoteName($this->details_table));
			$query->columns($db->quoteName($insertColumns));
			$query->values(implode('),(', $insertData));
			$db->setQuery($query);
			
			try {
				$db->execute();
			} catch (Exception $e) {
				$this->setError($e->getMessage());
				return false;
			}				
		}
		return true;
	}	

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function publish(&$pks, $value = 1)
	{
		if (parent::publish($pks, $value))
		{
			$dispatcher = JEventDispatcher::getInstance();

			// Include the content plugins for the change of category state event.
			JPluginHelper::importPlugin('qazap');

			// Trigger the onCategoryChangeState event.
			$dispatcher->trigger('onProductCategoryChangeState', array($pks, $value));

			return true;
		}
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   1.0.0
	 */
	public function rebuild()
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->rebuild())
		{
			$this->setError($table->getError());
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to save the reordered nested set tree.
	 * First we save the new order values in the lft values of the changed ids.
	 * Then we invoke the table rebuild to implement the new ordering.
	 *
	 * @param   array    $idArray    An array of primary key ids.
	 * @param   integer  $lft_array  The lft value
	 *
	 * @return  boolean  False on failure or error, True otherwise
	 *
	 * @since   1.0.0
	 */
	public function saveorder($idArray = null, $lft_array = null)
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->saveorder($idArray, $lft_array))
		{
			$this->setError($table->getError());
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	protected function batchTag($value, $pks, $contexts)
	{
		// Set the variables
		$user = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$tags = array($value);

				/**
				 * @var  JTableObserverTags  $tagsObserver
				 */
				$tagsObserver = $table->getObserverOfClass('JTableObserverTags');
				$result = $tagsObserver->setNewTags($tags, false);

				if (!$result)
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy categories to a new category.
	 *
	 * @param   integer  $value     The new category.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed    An array of new IDs on success, boolean false on failure.
	 *
	 * @since   1.0.0
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		$type = new JUcmType;
		$this->type = $type->getTypeByAlias($this->typeAlias);

		// $value comes as {parent_id}.{extension}
		$parts = explode('.', $value);
		$parentId = (int) JArrayHelper::getValue($parts, 0, 1);

		$db = $this->getDbo();
		$extension = JFactory::getApplication()->input->get('extension', '', 'word');
		$i = 0;

		// Check that the parent exists
		if ($parentId)
		{
			if (!$this->table->load($parentId))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Non-fatal error
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}
			// Check that user has create permission for parent category
			$canCreate = ($parentId == $this->table->getRootId()) ? $this->user->authorise('core.create', $extension) : $this->user->authorise('core.create', $extension . '.category.' . $parentId);

			if (!$canCreate)
			{
				// Error since user cannot create in parent category
				$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));

				return false;
			}
		}

		// If the parent is 0, set it to the ID of the root item in the tree
		if (empty($parentId))
		{
			if (!$parentId = $this->table->getRootId())
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
			// Make sure we can create in root
			elseif (!$this->user->authorise('core.create', $extension))
			{
				$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
				return false;
			}
		}

		// We need to log the parent ID
		$parents = array();

		// Calculate the emergency stop count as a precaution against a runaway loop bug
		$query = $db->getQuery(true)
			->select('COUNT(category_id)')
			->from($db->quoteName('#__qazap_product_categories'));
		$db->setQuery($query);

		try
		{
			$count = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Parent exists so let's proceed
		while (!empty($pks) && $count > 0)
		{
			// Pop the first id off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Copy is a bit tricky, because we also need to copy the children
			$query->clear()
				->select('category_id')
				->from($db->quoteName('#__qazap_product_categories'))
				->where('lft > ' . (int) $this->table->lft)
				->where('rgt < ' . (int) $this->table->rgt);
			$db->setQuery($query);
			$childIds = $db->loadColumn();

			// Add child ID's to the array only if they aren't already there.
			foreach ($childIds as $childId)
			{
				if (!in_array($childId, $pks))
				{
					array_push($pks, $childId);
				}
			}

			// Make a copy of the old ID and Parent ID
			$oldId = $this->table->category_id;
			$oldParentId = $this->table->parent_id;

			// Reset the id because we are making a copy.
			$this->table->category_id = 0;

			// If we a copying children, the Old ID will turn up in the parents list
			// otherwise it's a new top level item
			$this->table->parent_id = isset($parents[$oldParentId]) ? $parents[$oldParentId] : $parentId;

			// Set the new location in the tree for the node.
			$this->table->setLocation($this->table->parent_id, 'last-child');

			// TODO: Deal with ordering?
			// $this->table->ordering	= 1;
			$this->table->level = null;
			$this->table->asset_id = null;
			$this->table->lft = null;
			$this->table->rgt = null;

			// Alter the title & alias
			list($title, $alias) = $this->generateNewTitle($this->table->parent_id, $this->table->alias, $this->table->title);
			$this->table->title = $title;
			$this->table->alias = $alias;

			parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());
				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('category_id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;

			// Now we log the old 'parent' to the new 'parent'
			$parents[$oldId] = $this->table->category_id;
			$count--;
		}

		// Rebuild the hierarchy.
		if (!$this->table->rebuild())
		{
			$this->setError($this->table->getError());
			return false;
		}

		// Rebuild the tree path.
		if (!$this->table->rebuildPath($this->table->category_id))
		{
			$this->setError($this->table->getError());
			return false;
		}

		return $newIds;
	}

	/**
	 * Batch move categories to a new category.
	 *
	 * @param   integer  $value     The new category ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	protected function batchMove($value, $pks, $contexts)
	{
		$parentId = (int) $value;
		$type = new JUcmType;
		$this->type = $type->getTypeByAlias($this->typeAlias);

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$extension = JFactory::getApplication()->input->get('extension', '', 'word');

		// Check that the parent exists.
		if ($parentId)
		{
			if (!$this->table->load($parentId))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Non-fatal error
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}

			// Check that user has create permission for parent category
			$canCreate = ($parentId == $this->table->getRootId()) ? $this->user->authorise('core.create', $extension) : $this->user->authorise('core.create', $extension . '.category.' . $parentId);

			if (!$canCreate)
			{
				// Error since user cannot create in parent category
				$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
				return false;
			}

			// Check that user has edit permission for every category being moved
			// Note that the entire batch operation fails if any category lacks edit permission
			foreach ($pks as $pk)
			{
				if (!$this->user->authorise('core.edit', $extension . '.category.' . $pk))
				{
					// Error since user cannot edit this category
					$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_EDIT'));
					return false;
				}
			}
		}

		// We are going to store all the children and just move the category
		$children = array();

		// Parent exists so let's proceed
		foreach ($pks as $pk)
		{
			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new location in the tree for the node.
			$this->table->setLocation($parentId, 'last-child');

			// Check if we are moving to a different parent
			if ($parentId != $this->table->parent_id)
			{
				// Add the child node ids to the children array.
				$query->clear()
					->select('category_id')
					->from($db->quoteName('#__qazap_product_categories'))
					->where($db->quoteName('lft') . ' BETWEEN ' . (int) $this->table->lft . ' AND ' . (int) $this->table->rgt);
				$db->setQuery($query);

				try
				{
					$children = array_merge($children, (array) $db->loadColumn());
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}

			parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());
				return false;
			}

			// Rebuild the tree path.
			if (!$this->table->rebuildPath())
			{
				$this->setError($this->table->getError());
				return false;
			}
		}

		// Process the child rows
		if (!empty($children))
		{
			// Remove any duplicates and sanitize ids.
			$children = array_unique($children);
			JArrayHelper::toInteger($children);
		}

		return true;
	}

	/**
	 * Custom clean the cache of com_qazap and qazap modules
	 *
	 * @since   1.0.0
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_qazap');
		parent::cleanCache('mod_qazap_categories');
		parent::cleanCache('mod_qazap_search');
		parent::cleanCache('mod_qazap_filters');
	}	

	protected function checkData(&$data)
	{		
		$languages = JLanguageHelper::getLanguages('lang_code');
		
		if(!$existingAliases = $this->getExistingAliases($data['category_id']))
		{
			$this->setError($this->getError());
			return false;
		}		

		foreach($languages as $tag => $language)
		{
			if (trim($data['title'][$tag]) == '')
			{
				$this->setError(JText::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));
				return false;
			}

			if (trim($data['alias'][$tag]) == '')
			{
				$data['alias'][$tag] = $data['title'][$tag];
			}

			$data['alias'][$tag] = JApplication::stringURLSafe($data['alias'][$tag]);
			
			if(isset($existingAliases->$tag) && in_array($data['alias'][$tag], $existingAliases->$tag))
			{
				$data['alias'][$tag] = JString::increment($data['alias'][$tag], 'dash');
			}

			if (trim(str_replace('-', '', $data['alias'][$tag])) == '')
			{
				$data['alias'][$tag] = JFactory::getDate()->format('Y-m-d-H-i-s');
			}
			
			if (trim(str_replace('&nbsp;', '', $data['description'][$tag])) == '')
			{
				$data['description'][$tag] = '';
			}			
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
	protected function getExistingAliases($skipID = false, $langauge = true)
	{
		$tableName = '#__qazap_category_details';
		$fieldName = 'alias';
		$parentField = 'category_id';
		
		$db = $this->getDbo();
		
		$query = $db->getQuery(true);
		
		if($langauge)
		{
			$query->select(array($db->quoteName($fieldName), 'language'));
		}
		else
		{
			$query->select($db->quoteName($fieldName));
		}
		
		$query->from($db->quoteName($tableName));
		
		if($skipID)
		{
			$query->where($db->quoteName($parentField).' != '.$db->quote($skipID));
		}
		
		try 
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$return = new stdClass;
		foreach($result as $value)
		{
			$lang = isset($value->language) ? $value->language : false;
			if($lang)
			{
				if(!isset($return->$lang))
				{
					$return->$lang = array();					
				}
				array_push($return->$lang, $value->$fieldName);
			}
			else
			{
				if(!isset($return->$fieldName))
				{
					$return->$fieldName = array();					
				}
				array_push($return->$fieldName, $value->$fieldName);
			}
		}
		return $return;
	}	

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $parent_id  The id of the parent.
	 * @param   string   $alias      The alias.
	 * @param   string   $title      The title.
	 *
	 * @return  array    Contains the modified title and alias.
	 *
	 * @since   1.7
	 */
	protected function generateNewTitleAlias($parent_id, $language, $alias, $title)
	{
		// Alter the title & alias
		$db = $this->getDbo();
		$query = $db->getQuery(true)
						->select('COUNT(d.alias)')
						->from($db->quoteName('#__qazap_categories').' AS c')
						->leftJoin($db->quoteName('#__qazap_category_details') . ' AS d ON d.category_id = c.category_id')
						->where('c.parent_id = '. $db->quote($parent_id))
						->where('d.language = '. $db->quote($language));
						
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
	
	/**
	 * Method to rebuild the node's path field from the alias values of the
	 * nodes from the current node to the root node of the tree.
	 *
	 * @param   integer  $pk  Primary key of the node for which to get the path.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTableNested/rebuildPath
	 * @since   11.1
	 */
	public function rebuildPath($pk = null)
	{
		$db = $this->getDbo();
		$pk = (is_null($pk)) ? (int) $this->getState($this->getName() . '.id') : $pk;

		// Get the aliases for the path from the node to the root node.
		$query = $db->getQuery(true)
					->select(array('d.alias', 'd.language'))
					->from('#__qazap_categories AS n, #__qazap_categories AS p')
					->join('LEFT', '#__qazap_category_details AS d ON d.category_id = p.category_id')
					->where('n.lft BETWEEN p.lft AND p.rgt')
					->where('n.category_id = ' . (int) $pk)
					->order('p.lft');
		
		try 
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();			
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		

		$data = array();
		
		foreach($result as $value)
		{
			// Make sure to remove the root path if it exists in the list.
			if($value->alias != 'root' && $value->language != '*')
			{
				if(!isset($data[$value->language])) 
				{
					$data[$value->language] = array();
				}				
				$data[$value->language][] = $value->alias;	
			}					
		}
		
		$when = '';			 
		foreach($data as $language => &$path) 
		{
			// Build the path.
			$path = trim(implode('/', $path), ' /\\');			
			$when .= sprintf('WHEN %s THEN %s ', $db->quote($language), $db->quote($path));
		}
			
		$query->clear();
		$query->update($db->quoteName('#__qazap_category_details'));		
		$query->set($db->quoteName('path') .' = CASE '.$db->quoteName('language').' '.$when.' END');
		$query->where($db->quoteName('category_id').' = '.(int) $pk);

		try 
		{
			$db->setQuery($query)->execute();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return $data;
	}
	

}