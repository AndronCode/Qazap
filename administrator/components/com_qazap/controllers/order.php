<?php
/**
 * order.php
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

jimport('joomla.application.component.controllerform');
JLoader::register('QazapHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/qazap.php');

/**
* Order controller class.
*/
class QazapControllerOrder extends JControllerForm
{
	public function __construct() 
	{
		$this->view_list = 'orders';
		parent::__construct();
	}

	public function updateOrdergroupStatus()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel();
		$data = $this->input->post->get('jform', array(), 'array');
		$task = $this->getTask();
		$ordergroup_id = $this->input->getInt('ordergroup_id');	
		$data['ordergroup_id'] = $ordergroup_id;
		$urlVar = null;

		// Access check.
		if (!$this->allowSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list. $this->getRedirectToListAppend(), false));

			return false;
		}		

		if(!$model->updateOrdergroupStatus($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));
			return false;					
		}

		$this->setMessage(JText::_('COM_QAZAP_MSG_ORDERGROUP_ORDER_STATUS_UPDATED'));	
		$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);

		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));

		return true;				
	}


	public function updatePayments()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel();
		$data = $this->input->post->get('jform', array(), 'array');
		$ordergroup_id = $this->input->getInt('ordergroup_id');	
		$data['ordergroup_id'] = $ordergroup_id;
		$urlVar = null;

		// Access check.
		if (!$this->allowSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list. $this->getRedirectToListAppend(), false));

			return false;
		}		
		
		if(!$model->updatePayments($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));
			return false;					
		}
		
		$this->setMessage(JText::_('COM_QAZAP_ORDERGROUP_PAYMENT_DETAILS_UPDATED'));
		$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);
		
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));	
		return true;				
	}


	public function updateOrderAddress()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel();
		$data = $this->input->post->get('jform', array(), 'array');
		$address_type = $this->input->getCmd('address_type', 'BT');
		$ordergroup_id = $this->input->getInt('ordergroup_id');	
		$data['ordergroup_id'] = $ordergroup_id;
		$urlVar = null;

		// Access check.
		if (!$this->allowSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list. $this->getRedirectToListAppend(), false));

			return false;
		}		

		if(!$model->updateOrderAddress($data, $address_type))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));
			return false;					
		}

		$this->setMessage(JText::_('COM_QAZAP_ORDERGROUP_'.strtoupper($address_type).'_UPDATED'));
		$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);

		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));	
		return true;				
	}

	
	public function updateOrderStatus()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel();
		$data = $this->input->post->get('jform', array(), 'array');
		$ordergroup_id = $this->input->getInt('ordergroup_id');
		$order_id = $this->input->post->getInt('order_id');	
		$data['ordergroup_id'] = $ordergroup_id;
		$data['order_id'] = $order_id;
		$urlVar = null;

		// Access check.
		if (!$this->allowSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list. $this->getRedirectToListAppend(), false));

			return false;
		}		
		
		if(!$model->updateOrderStatus($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));
			return false;					
		}
		
		$this->setMessage(JText::_('COM_QAZAP_MSG_ORDER_STATUS_UPDATED'));
		$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);
		
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));	
		return true;
		
	}
	
	
	public function updateItemQuantity()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel();
		$quantity = $this->input->getInt('quantity');
		$ordergroup_id = $this->input->getInt('ordergroup_id');
		$group_id = $this->input->post->getString('group_id');
		$data = array();		
		$data['ordergroup_id'] = $ordergroup_id;
		$data['group_id'] = $group_id;
		$data['quantity'] = $quantity;

		// Access check.
		if (!$this->allowSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}		
		
		if(!$model->updateItemQuantity($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));
			return false;					
		}
		
		$this->setMessage(JText::_('COM_QAZAP_ORDER_ITEM_QUANTITY_UPDATED_MSG'));
		$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);
		
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));	
		return true;
	}
	
	public function deleteItem()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel();
		$ordergroup_id = $this->input->getInt('ordergroup_id');
		$group_id = $this->input->post->getString('group_id');
		$data = array();		
		$data['ordergroup_id'] = $ordergroup_id;
		$data['group_id'] = $group_id;

		// Access check.
		if (!$this->allowSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list. $this->getRedirectToListAppend(), false));

			return false;
		}		
		
		if(!$model->deleteItem($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));
			return false;					
		}
		
		$this->setMessage(JText::_('COM_QAZAP_ORDER_ITEM_DELETED'));
		$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);
		
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));	
		return true;
	}
	
	public function updateItemStatus()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel();
		$data = $this->input->post->get('jform', array(), 'array');
		$ordergroup_id = $this->input->getInt('ordergroup_id');
		$group_id = $this->input->post->getString('group_id');
		$data['ordergroup_id'] = $ordergroup_id;
		$data['group_id'] = $group_id;

		// Access check.
		if (!$this->allowSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list. $this->getRedirectToListAppend(), false));

			return false;
		}		
		
		if(!$model->updateItemStatus($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));
			return false;					
		}
		
		$this->setMessage(JText::_('COM_QAZAP_MSG_ORDER_ITEM_STATUS_UPDATED'));
		$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);
		
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=order&layout=edit&ordergroup_id='.(int)$ordergroup_id, false));	
		return true;		
	}
	
	/**
	* Method to run batch operations.
	*
	* @param   object  $model  The model.
	*
	* @return  boolean   True if successful, false otherwise and internal error is set.
	*
	* @since   1.6
	*/
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel();

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=orders' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}	
}