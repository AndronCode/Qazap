<?php
/**
 * shop.php
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
class QazapModelShop extends JModelAdmin
{
	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();

		// Get the pk of the record from the request.
		$lang = $app->input->getString('lang');
		$this->setState($this->getName() . '.lang', $lang);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
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
	public function getTable($type = 'Shop', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.shop', 'shop', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.shop.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data)
	{
		$table = $this->getTable();
		$key = $table->getKeyName();
		$data[$lang] = isset($data[$lang]) ? $data[$lang] : '*';	
		
		try
		{
			$table->load(array('lang' => $data[$lang]));
			$isNew = false;

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('shop', &$data, $isNew));			
						
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
			$dispatcher->trigger('onAfterSave', array('shop', $data, $isNew));		
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}	
		
		$pkName = $table->getKeyName();
		return true;		
	}
	/**
	* Method to get a single record.
	*
	* @param	integer	The id of the primary key.
	*
	* @return	mixed	Object on success, false on failure.
	* @since	1.0.0
	*/
	public function getItem($lang = null)
	{
		$table = $this->getTable();
		$lang = $lang ? $lang : $this->getState($this->getName() . '.lang', '*');
		return parent::getItem(array('lang' => $lang));
	}
	
	public function createMultiple($action = 'create')
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();		
		$query = $db->getQuery(true)
					->select('shop_id, lang')
					->from('#__qazap_shop');
		try
		{
			$db->setQuery($query);
			$results = $db->loadObjectList();
		} 
		catch (Exception $e) 
		{
			$this->getError($e->getMessage());
			return false;
		}
		
		$shops = array();
		$insert = array();
		$update = array();
		$delete = array();
		$return = array();
		
		if(!empty($results))
		{
			foreach($results as $result)
			{
				$shops[$result->lang] = $result->shop_id;
			}
		}
		
		$lang = JFactory::getLanguage();
		$default_language = $lang->getDefault();
		
		if($action == 'create')
		{
			$multiple_language = JLanguageMultilang::isEnabled();		
			$languages = JLanguageHelper::getLanguages('lang_code');
			
			if(isset($shops['*']))
			{
				$shop_id = $shops['*'];
				$update[$shop_id] = $default_language;
				unset($languages[$default_language]);
				unset($shops['*']);
			}
			
			$tags = array_keys($languages);
			$existing_langs = array_keys($shops);
			$insert = array_diff($tags, $existing_langs);
			$obsolete_langs = array_diff($existing_langs, $tags);
			$delete = array_intersect_key($shops, array_flip($obsolete_langs));
		}
		else
		{
			if(isset($shops['*']))
			{
				unset($shops['*']);
			}
			elseif(isset($shops[$default_language]))
			{
				$shop_id = $shops[$default_language];
				$update[$shop_id] = '*';
				unset($shops[$default_language]);
			}
			else
			{
				$insert = array($default_language);
			}
			
			if(!empty($shops))
			{
				$delete = array_keys($shops);
			}
		}
		
		$languages = JLanguageHelper::getLanguages('lang_code');
		
		if(!empty($insert))
		{
			$query->clear()
					->insert($db->quoteName('#__qazap_shop'))
					->columns($db->quoteName('lang'))	
					->values(implode('),(', $db->quote($insert)));
			
			try
			{
				$db->setQuery($query)->execute();	
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$processed = array_intersect_key($languages, array_flip($insert));
			$processed = array_keys($processed);			

			if(count($processed) == 1)
			{
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_NEW_LANGUAGE_INSERTED',$languages[$processed[0]]->title));
			}
			else
			{
				$titles = array();
				foreach($processed as $proLang)
				{
					$titles[] = $languages[$processed]->title;
				}
				
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_NEW_LANGUAGE_N_INSERTED', implode(',', $titles)));
			}	
		
		}
		
		if(!empty($update))
		{
			foreach($update as $key=>$value) 
			{
				$query->clear()
						->update('#__qazap_shop')
						->set('lang = '.$db->quote($value))
						->where('shop_id = '.$key);
						
				try
				{
					$db->setQuery($query)->execute();

				} 
				catch (Exception $e) 
				{
					$this->setError($e->getMessage());
					return false;
				}
			}

			$Updatelang = array_intersect_key($languages, array_flip($update));
			$Updatelang = array_keys($Updatelang);		
			
			if(count($Updatelang) == 1)
			{
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_LANGUAGE_UPDATED', $languages[$Updatelang[0]]->title));			
			}
			
			else if($update[$shop_id] == '*')
			{
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_DEFAULT_LANGUAGE_FOR_ALL'));
			}
			
			else
			{
				$titles = array();
				foreach($Updatelang as $proLang)
				{
					$titles[] = $languages[$proLang]->title;
				}
				
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_LANGUAGE_N_UPDATED', implode(',', $titles)));
			}
		}
		if(!empty($delete))
		{
			$query->clear()
					->delete($db->quoteName('#__qazap_shop'))
					->where('lang IN (' . implode(',', $db->quote($delete)) . ')');
			
			try
			{
				$db->setQuery($query)->execute();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			$titles = array();
			foreach($delete as $deleteLang)
			{
				$titles[] = $languages[$deleteLang]->title;
			}
			
			$app->enqueueMessage(JText::sprintf('COM_QAZAP_LANGUAGE_DELETED', implode(',', $titles)));
			
		}
		return true;
	}	

}