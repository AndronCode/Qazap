<?php
/**
 * paymentmethod.php
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

if(!class_exists('QazapHelper'))
{
	require(JPATH_COMPONENT_ADMINISTRATOR .'/helpers/qazap.php');
}
/**
 * Qazap model.
 */
class QazapModelPaymentmethod extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0.0
	 */
	protected $text_prefix = 'COM_QAZAP';
	protected $params;
	protected $type;

	/**
	* Returns a reference to the a Table object, always creating it.
	*
	* @param	type	The table type to instantiate
	* @param	string	A prefix for the table class name. Optional.
	* @param	array	Configuration array for model. Optional.
	* @return	JTable	A database object
	* @since	1.0.0
	*/
	public function getTable($type = 'Paymentmethod', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.paymentmethod', 'paymentmethod', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) 
		{
			return false;
		}
		
		$form = $this->mergePluginParams($data, $form);
		
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.paymentmethod.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();
		}

		return $data;
	}

	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{	
		$form = $this->mergePluginParams($data, $form);
		
		parent::preprocessForm($form, $data, $group);
	}
	
	protected function mergePluginParams($data, $form)
	{
		if(is_object($data))
		{
			$data = JArrayHelper::fromObject($data);
		}
		
		if(isset($data['payment_method']) && $data['payment_method'])
		{
			$plugin = QazapHelper::getPlugin($data['payment_method']);
			$paramsFormFile = null;
			$dispatcher	= JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazappayment');
			$result = $dispatcher->trigger('onGetParamsFormPath', array($plugin, &$paramsFormFile));

			if($paramsFormFile)
			{
				$form->removeField('params');
				// Get the plugin form's config section.
				if (!$form->loadFile($paramsFormFile, false))
				{
					throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
				}
				// Attempt to load the xml file.
				if (!$xml = simplexml_load_file($paramsFormFile))
				{
					throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
				}
			}			
		}
		
		return $form;			
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
			//Do any procesing on fields here if needed
			$item->countries = isset($item->countries) ? json_decode($item->countries) : array();
			$item->user_group = isset($item->user_group) ? json_decode($item->user_group) : array();			
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{		
		$table = $this->getTable();

		$data['countries'] = isset($data['countries']) ? $data['countries'] : array();
		$data['user_group'] = isset($data['user_group']) ? $data['user_group'] : array();
		$data['payment_method'] = (int) isset($data['payment_method']) ? $data['payment_method'] : 0;
		
		if(empty($data['user_group']))
		{
			$data['user_group'] = array(1);
		}

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}
			
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('qazapsystem');
			JPluginHelper::importPlugin('qazappayment');
						
			$plugin = QazapHelper::getPlugin($data['payment_method']);			
			
			if(empty($plugin))
			{
				$this->setError(JText::_('COM_QAZAP_PAYMENTMETHOD_ERROR_INVALID_METHOD'));
				return false;				
			}
			
			// Trigger the onEventBeforeSave event.
			$result = $dispatcher->trigger('onBeforeSave', array('paymentmethod', &$data, $isNew));
			
			if (in_array(false, $result, true))
			{
				$this->setError($dispatcher->getError());
				return false;
			}
						
			$result = $dispatcher->trigger('onMethodBeforeSave', array($plugin, &$data, $isNew));
			
			if (in_array(false, $result, true))
			{
				$this->setError($dispatcher->getError());
				return false;
			}
			
			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}


			
			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			if(!isset($table->payment_method) && empty($table->payment_method))
			{				
				$this->setError(JText::_('COM_QAZAP_PAYMENTMETHOD_ERROR_INVALID_METHOD'));
				return false;
			}
						
			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			// Clean the cache.
			$this->cleanCache();
			
			// Trigger the onContentAfterSave event.
			$dispatcher->trigger('onAfterSave', array('paymentmethod', $data, $isNew));
			$dispatcher->trigger('onMethodAfterSave', array($plugin, &$data, $isNew));
			
			// Trigger the onSaveCreateTable event.
			$result = $dispatcher->trigger('onSaveCreateTable', array($plugin, $table, $isNew));			

			if (in_array(false, $result, true))
			{
				$this->setError($dispatcher->getError());
				return false;
			}
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
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_payment_methods');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}
	
	
	/**
	* Method to get Payment Method Plugin Params
	* 
	* @return JForm object
	* @since	1.0
	*/
	public function getPaymentParams() 
	{
		$app = JFactory::getApplication();
		
		if(!$plugin_id = $app->input->getInt('plugin_id')) 
		{
			return false;
		}

		$params = new stdClass;
		
		$plugin = QazapHelper::getPlugin($plugin_id);
		
		if(!(isset($plugin->enabled)) || !$plugin->enabled)
		{
			return false;
		}	
		
		$form = null;
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
		
		$result = $dispatcher->trigger('onEditParams', array($plugin, $params, &$form));

		return $form;		
	}	

}