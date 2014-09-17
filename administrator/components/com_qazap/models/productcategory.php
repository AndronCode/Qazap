<?php
/**
 * productcategory.php
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
class QazapModelproductcategory extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0.0
	 */
	protected $text_prefix = 'COM_QAZAP';
	protected $category_string = "";

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0
	 */
	public function getTable($type = 'Productcategory', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.productcategory', 'productcategory', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.productcategory.data', array());

		if (empty($data)) {
			$data = $this->getItem();
			if($data->id == NULL) $id = 0; else $id = $data->id;
			$info = $this->getCategoryInfo($id);			
			$data->category_name = $info['category_name'];
			$data->description = $info['category_description'];
			$data->page_title = json_decode($data->page_title);
			$data->meta_keywords = json_decode($data->meta_keywords);
			$data->meta_description = json_decode($data->meta_description);
            
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
		if ($item = parent::getItem($pk)) {

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

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_productcategories');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}
		
	private function saveProductCategoryInfo($new,$categoryId,$data)
	{		
		$lang = JFactory::getLanguage();
		$CurrentLanguage = $lang->getTag();
		$languages = JLanguage::getKnownLanguages(JPATH_SITE);
		$v = $languages[$CurrentLanguage];
		unset($languages[$CurrentLanguage]);
		$languages[$CurrentLanguage] = $v;		
		$languages = array_reverse($languages, true);

		$db = JFactory::getDBO();
		$CategoryNames = $data['category_name'];
		$category_description = $data['description'];

		if($new) 
		{		
			$query = $db->getQuery(true);
			$columns = array('category_id', 'language', 'category_name','category_description');
			$query
			    ->insert($db->quoteName('#__qazap_categoryinfo'))
			    ->columns($db->quoteName($columns));
				
			foreach($languages as $language) 
			{
				$key = $language['tag'];
				$values = array($categoryId, $db->Quote($key), $db->Quote($CategoryNames[$key]), $db->Quote($category_description[$key]));
				$query->values(implode(',',$values));
			}				    
			$db->setQuery($query);
			try 
			{
		    	$result = $db->query();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				$this->deleteCategory($categoryId);
			    return false;
			}			
			return true;	
		
		} 
		else 
		{			
			$query = $db->getQuery(true);
			$columns = array('category_id', 'language', 'category_name', 'category_description');
			
			$query = $db->getQuery(true)
			       ->select('a.language')
				   ->from('#__qazap_categoryinfo as a')
				   ->where('a.category_id = '.$categoryId);
		    $db->setQuery($query);
			$nameLanguages = $db->loadColumn();
			$n=1;
			foreach($languages as $language) 
			{
				$key = $language['tag'];
				if(in_array($key, $nameLanguages)) 
				{
					$query = $db->getQuery(true)
								->update($db->quoteName('#__qazap_categoryinfo'))
								->set('category_name = '.$db->Quote($CategoryNames[$key]))
								->set('category_description = '.$db->Quote($category_description[$key]))
								->where('category_id = '.$categoryId)
								->where('language = '.$db->Quote($key));
					$db->setQuery($query);
					try 
					{
					    $result = $db->query();
					} 
					catch (Exception $e) 
					{
						$this->setError($e->getMessage());
					    return false;
					}											
				} 
				else 
				{
					$values = array($categoryId, $db->Quote($key), $db->Quote($CategoryNames[$key]), $db->Quote($category_description[$key]));
					$query = $db->getQuery(true);
					$query
					    ->insert($db->quoteName('#__qazap_categoryinfo'))
					    ->columns($db->quoteName($columns))						
						->values(implode(',',$value));
					$db->setQuery($query);
					try 
					{
					    $result = $db->query();
					} 
					catch (Exception $e) 
					{
						$this->setError($e->getMessage());
					    return false;
					}										
				}				
			}
			return true;
		}
	}
	
	public function save($data)	
	{
		$lang = JFactory::getLanguage();
		$CurrentLanguage = $lang->getTag();				
		if ($data['category_name'][$CurrentLanguage] == "")
		{
			$this->setError('category Name Cannot be Left Blank');
			return false;	
		}		
				
		$new = false;
		$categoryId = $data['id'];
		if($categoryId == 0) $new = true;
		$categorySave = parent::save($data);
		if(!$categorySave)
		{
			return false;
		}
		if($new)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
						->select('MAX(id)')
						->from('#__qazap_productcategories');
			$db->setQuery($query);
			$categoryId = $db->loadResult();	   
		}
		//print_r($categoryId);exit;
		if(!$this->saveProductCategoryInfo($new, $categoryId,$data))
		{
			return FALSE;
		}

		return true;
	}
		
	private function getProductCategoryInfo($id)
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
					->select(array('language','category_name', 'category_description'))
					->from('#__qazap_categoryinfo')
					->where('category_id='.$id);
		$db->setQuery($sql);
		$datas = $db->loadObjectList();
		$category = array();
		$info['category_name'] = new stdClass();
		$info['category_description'] = new stdClass();
		foreach($datas as $value)
		{
			$lang = $value->language;
			$info['category_name']->$lang = $value->category_name;
			$info['category_description']->$lang = $value->category_description;
		}
		return $info;
	}	
	
	private function getCategoryInfo($id)
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
					->select(array('language','category_name', 'category_description'))
					->from('#__qazap_categoryinfo')
					->where('category_id='.$id);
		$db->setQuery($sql);
		$datas = $db->loadObjectList();
		$category = array();
		$info['category_name'] = new stdClass();
		$info['category_description'] = new stdClass();
		foreach($datas as $value)
		{
			$lang = $value->language;
			$info['category_name']->$lang = $value->category_name;
			$info['category_description']->$lang = $value->category_description;
		}
		return $info;
	}
	
	private function deleteCategory($id)
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
					->delete($db->quoteName('#__qazap_productcategories'))
					->where('id = '.$id);	
		$db->setQuery($sql);
		$db->query();
	}
	

}