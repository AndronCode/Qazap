<?php
/**
 * orderstatus.php
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
class QazapModelOrderstatus extends JModelAdmin
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
	public function getTable($type = 'Orderstatus', $prefix = 'QazapTable', $config = array())
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
		$form = $this->loadForm('com_qazap.orderstatus', 'orderstatus', array('control' => 'jform', 'load_data' => $loadData));
        
        
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
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.orderstatus.data', array());

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
	
	public function save($data)
	{
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapsystem');
				
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
					->select('COUNT(id)')
					->from('#__qazap_order_status')
					->where('status_code = '.$db->quote($data['status_code']))
					->where('id !='.$data['id']);
		$db->setQuery($sql);
		$count = $db->loadResult();
		if($count)
		{
			$this->setError('Status code Already Exist');
			return false;
		}
		
		// Trigger the onEventBeforeSave event.
		$result = $dispatcher->trigger('onBeforeSave', array('orderstatus', &$data, $isNew));
		
		if(parent::save($data))
		{
			return true;
		}
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
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_order_status');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}
	
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$statuses = array('P','S','R','C','X','U','D');
		$sql = $db->getQuery(true)
			 ->select(array('status_name','id'))
			 ->from('#__qazap_order_status')
			 ->where('id IN ('.implode(',',$pks).')')
			 ->where('status_code IN ('.implode(',', $db->quote($statuses)).')');
		$db->setQuery($sql);
		$statuses = $db->loadObjectList();
		
		foreach($statuses as $status)
		{
			if(in_array($status->id,$pks))
			{
				if(($key = array_search($status->id, $pks)) !== false) 
				{
				    unset($pks[$key]);
					$app->enqueueMessage(JText::sprintf('Core status %s can be deleted', $status->status_name), 'error');
				}
			}
		}
		if(empty($pks))
		{
			$this->setError('An error occurred while deleting order status');
			return false;
		}
		parent::delete($pks);
	}
	
	public function getStatuses()
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			 -> select(array('status_code', 'status_name'))
			 ->from('#__qazap_order_status');
		try
		{
			$db->setQuery($sql);
			$statuses = $db->loadAssocList('status_code');	
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		return $statuses;
	}

}