<?php
/**
 * notify.php
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
class QazapModelNotify extends JModelLegacy
{
	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';
	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_qazap.notify';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0.0
	 */
	public function getTable($type = 'Notify', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	* Method for saving notify data
	* 
	* Returns Boolean True OR False
	*  
	* @param array &data Array from the input form
	* 
	* @since 1.0.0
	*/
	
	public function saveNotify($data)
	{
		if(!QazapHelper::validateEmail($data['user_email']))
		{
			$this->setError(JText::_('COM_QAZAP_ENTER_VALID_EMAIL'));
			return false;
		}
		
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			 ->select('count(id)')
			 ->from('#__qazap_notify_product')
			 ->where('user_email = ' . $db->quote($data['user_email']))
			 ->where('product_id = ' . (int) $data['product_id']);
			 
		try
		{
			$db->setQuery($sql);
			$results = $db->loadResult();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if($results)
		{
			$this->setError(JText::_('COM_QAZAP_DUPLICATE_NOTIFICATION'));
			return false;
		}

		//Get Notify Table
		$table = $this->getTable();
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapsystem');
		
		// Trigger the onEventBeforeSave event.
		$result = $dispatcher->trigger('onBeforeSave', array('notify', &$data, $isNew));
		
		if (in_array(false, $result, true))
		{
			$this->setError($dispatcher->getError());
			return false;
		}
				
		if(!$table->save($data))
		{
			$this->setError($table->getError());
			return false;			
		}

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger('onAfterSave', array('notify', $data, $isNew));
		
		if($data['block'] == 1)
		{
			$model = QZApp::getModel('Mail', array('ignore_request'=>true, 'display_message' => false));
			
			if(!$model->send('notify', $data, true))
			{
				return false;
			}
		}
	
		return true;		
	}

	/**
	* Method for notifying user
	* 
	* @return BOOLEAN true or false
	* 
	* @param int product_id Id of the updated product
	* 
	* @since 1.0.0 
	*/
	public function notify($product_id)
	{
		$product_id = (int) $product_id;
		$options = array();
		$options['rating'] = false;
		$options['custom_fields'] = false;
		$options['attributes'] = false;
		$filters = array();
		$filters['state'] = 1;
		
		//Get Details of the product id
		try
		{
			$helper = QZProducts::getInstance($options, $filters);
			$data = $helper->get($product_id);			
		}
		catch(Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		if($data->state != 1)
		{
			return true;
		}
				
		$presentStock = ($data->in_stock - $data->booked_order);
		
		if($presentStock <= 0)
		{
			return true;
		}		

		$db = $this->getDbo();
		$sql = $db->getQuery(true)
				->select('id, user_email, product_id')
				->from('#__qazap_notify_product')
				->where('product_id = '. $product_id)
				->where('block = 0');
				
		try
		{
			$db->setQuery($sql);
			$notifications = $db->loadObjectList();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(empty($notifications))
		{
			return true;
		}
		//exit;
		$pks = array();
		$emails = array();
		
		foreach($notifications as $notification)
		{
			$pks[] = $notification->id;
			$emails[] = $notification->user_email;
		}
		
		$dataCombined = array(
								'products' => $data,
								'user_email' => $emails			
							);
		
		$mailModel = QZApp::getModel('mail', array());
		
		// Send notification mail
		if(!$mailModel->send('notify', $dataCombined, false))
		{
			$this->setError($mailModel->getError());
			return false;
		}
		
		// Delete after sending the notification from notify table
		if(!$this->delete($pks))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return true;
	}
	
	/*
	* Method for activating the mail for notification
	*
	* @return Boolean true if success , false if failed
	*
	* @param string &key MD5 encryption of time
	*
	* @since 1.0.0 
	*/
	public function activate($key)
	{
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			 ->select('id')
			 ->from('#__qazap_notify_product')
			 ->where('block = 1')
			 ->where('activation_key = '.$db->quote($key));
		try
		{
			$db->setQuery($sql);
			$id = $db->loadResult();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(!$id)
		{
			$this->setError(JText::_('COM_QAZAP_ACTIVATION_TOKEN_NOT_FOUND'));
			return false;
		}
		
		$sql->clear();
		$sql = $db->getQuery(true)
					->update($db->quoteName('#__qazap_notify_product'))
					->set('activation_key = ""')
					->set('block = 0')
					->where('id = '.$id);
		try
		{
			$db->setQuery($sql);
			$db->query();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}
	
	/**
	* Method for delete from notify table
	* 
	* @return Boolean true if success, false if failed
	* 
	* @param array &pks Array of primary keys
	* 
	* @since 1.0.0 
	*/
	public function delete(&$pks)
	 {
	 	$pks = (array) $pks;
		if(empty($pks))
		{
			$this->setError(JText::_('COM_QAZAP_INVALID_DELETE_DATA'));
			return false;
		}
		$db = $this->getDbo();	
		
		$sql = $db->getQuery(true)
				 ->delete($db->quoteName('#__qazap_notify_product'))
				 ->where('id IN ('.implode(',',$pks).')');
		try
		{
			$db->setQuery($sql);
			$db->query();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	 }

}