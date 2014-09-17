<?php
/**
 * review.php
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
class QazapModelReview extends JModelAdmin
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
	public function getTable($type = 'Review', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.review', 'review', array('control' => 'jform', 'load_data' => $loadData));
       
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.review.data', array());

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

		if (empty($table->id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_reviews');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	/*
	* Check authentication for review
	* 
	* @data review data from product review form
	* 
	*/
	
	protected function allowAddReview($product_id, $user_id)
	{		
		$user 		= JFactory::getuser();
		$product_id	= (int) $product_id;
		$user_id	= (int) $user_id;
		
		if(empty($product_id))
		{
			$this->setError(JText::_('COM_QAZAP_MSG_REVIEW_SELECT_A_VALID_PRODUCT'));
			return false;			
		}
		
		if(empty($user_id))
		{
			$this->setError(JText::_('COM_QAZAP_MSG_REVIEW_SELECT_A_VALID_USER'));
			return false;			
		}		
				
		// Allow Super Admin to add multiple reviews for a product
/*		if($user->get('isRoot'))
		{
			return true;
		}*/
		
		if($user->guest)
		{
			$this->setError(JText::_('COM_QAZAP_MSG_LOGIN_TO_ADD_REVIEW'));
			return false;
		}
		
		
		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('count(id)')
					->from('#__qazap_reviews')
					->where('product_id = ' . $product_id)
					->where('user_id = ' . $user_id);
		try
		{
			$db->setQuery($query);
			$count = $db->loadResult();			
		}
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if($count > 0)
		{
			$this->setError(JText::_('COM_QAZAP_YOU_HAVE_ALREADY_POSTED_A_REVIEW'));
			return false;
		}

		return true;
	}
	
	public function save($data)
	{
		$app = JFactory::getApplication();
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		$config = QZApp::getConfig();		
		
		if(!$this->allowAddReview($data['product_id'], $data['user_id']))
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
			
			if($app->isSite() && $isNew && $config->get('new_review_approval', 0) == 1)
			{
				$data['state'] = 0;
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('review', &$data, $isNew));
								
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
			$dispatcher->trigger('onAfterSave', array('review', $data, $isNew));		
			
		}
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;	
	}

}