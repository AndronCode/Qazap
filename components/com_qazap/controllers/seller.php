<?php
/**
 * seller.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_qazap
 */
class QazapControllerSeller extends JControllerForm
{
	/**
	* The URL view item variable.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $view_item = 'sellerform';

	/**
	* The URL view list variable.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $view_list = 'profile';

	/**
	* The URL edit variable.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $urlVar = '';

	/**
	* Method to add a new record.
	*
	* @return  mixed  True if the record can be added, a error object if not.
	*
	* @since   1.0.0
	*/
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}

	/**
	* Method override to check if you can add a new record.
	*
	* @param   array  $data  An array of input data.
	*
	* @return  boolean
	*
	* @since   1.0.0
	*/
	protected function allowAdd($data = array())
	{
		$user			= QZUser::get();
		$juser		= JFactory::getUser();
		$config		= QZApp::getConfig(); 
		
		if($user->isVendor)
		{
			$this->setError('');
			return false;
		}
		elseif ($juser->get('id') > 0 && $config->get('enable_vendor_registration'))
		{
			return true;
		}
		
		return false;		
	}

	/**
	* Method override to check if you can edit an existing record.
	*
	* @param   array   $data  An array of input data.
	* @param   string  $key   The name of the key for the primary key; default is id.
	*
	* @return  boolean
	*
	* @since   1.0.0
	*/
	protected function allowEdit($data = array(), $key = '')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$vendor = QZUser::get();
		$vendorId = $vendor->get('vendor_id');
		$canDo = QZHelper::getActions();

		// Check if user is a active vendor and general edit permission first.
		if ($vendorId > 0)
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['vendor']) ? $data['vendor'] : 0;
			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->id;
			}
			
			// If the owner matches 'me' then do the test.
			if ($ownerId == $vendorId)
			{
				return true;
			}
		}

		return true;
	}

	/**
	* Method to cancel an edit.
	*
	* @param   string  $key  The name of the primary key of the URL variable.
	*
	* @return  boolean  True if access level checks pass, false otherwise.
	*
	* @since   1.0.0
	*/
	public function cancel($key = 'product_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	/**
	* Method to edit an existing record.
	*
	* @param   string  $key     The name of the primary key of the URL variable.
	* @param   string  $urlVar  The name of the URL variable if different from the primary key
	* (sometimes required to avoid router collisions).
	*
	* @return  boolean  True if access level check and checkout passes, false otherwise.
	*
	* @since   1.0.0
	*/
	public function edit($key = null, $urlVar = '')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
	}


	public function allowSave($data, $key ='vendor_admin')
	{
		return true;
	}

	/**
	* Method to get a model object, loading it if required.
	*
	* @param   string  $name    The model name. Optional.
	* @param   string  $prefix  The class prefix. Optional.
	* @param   array   $config  Configuration array for model. Optional.
	*
	* @return  object  The model.
	*
	* @since   1.0.0
	*/
	public function getModel($name = 'sellerform', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	* Gets the URL arguments to append to an item redirect.
	*
	* @param   integer  $recordId  The primary key id for the item.
	* @param   string   $urlVar    The name of the URL variable for the id.
	*
	* @return  string	The arguments to append to the redirect URL.
	*
	* @since   1.0.0
	*/
	protected function getRedirectToItemAppend($recordId = null, $urlVar = '')
	{
		// Need to override the parent method completely.
		$tmpl   = $this->input->get('tmpl');
		//$layout = $this->input->get('layout', 'edit');
		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl='.$tmpl;
		}

		// TODO This is a bandaid, not a long term solution.
		$append .= '&layout=edit';

		if ($recordId)
		{
			$append .= '&'.$urlVar.'='.$recordId;
		}

		$itemId	= $this->input->getInt('Itemid');

		if ($itemId)
		{
			$append .= '&Itemid='.$itemId;
		}

		return $append;
	}

	/**
	* Get the return URL.
	*
	* If a "return" variable has been passed in the request
	*
	* @return  string	The return URL.
	*
	* @since   1.0.0
	*/
	protected function getReturnPage()
	{
		return JRoute::_(QazapHelperRoute::getSellerRoute(), false);
	}

	/**
	* Function that allows child controller access to model data after the data has been saved.
	*
	* @param   JModelLegacy  $model  The data model object.
	* @param   array         $validData   The validated data.
	*
	* @return  void
	*
	* @since   1.0.0
	*/
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		return;
	}

	/**
	* Method to save a record.
	*
	* @param   string  $key     The name of the primary key of the URL variable.
	* @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	*
	* @return  boolean  True if successful, false otherwise.
	*
	* @since   1.0.0
	*/
	public function save($key = null, $urlVar = '')
	{		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$user = QZUser::get();
		$juser = $user->juser;
		$data  = $this->input->post->get('jform', array(), 'array');
		$retun = base64_encode(JRoute::_(QazapHelperRoute::getSellerRoute(), false));
		$isNew = ($data['id'] > 0) ? false : true;

		if($juser->guest)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($return), false));
			return;

		}
		elseif(!$isNew && ($user->get('vendor_id') != $data['id']))
		{
			$this->setRedirect(JRoute::_(QazapHelperRoute::getSellerRoute(), false));
			return;
		}
		elseif($data['vendor_admin'] != $juser->get('id'))
		{
			$this->setRedirect(JRoute::_(QazapHelperRoute::getSellerRoute(), false));
			return;			
		}
		
		$this->input->set('id', $data['id']);
		
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result)
		{
			if($isNew)
			{
				$this->setMessage(JText::_('COM_QAZAP_NEW_VENDOR_SAVED'));
			}
			else
			{
				$this->setMessage(JText::_('COM_QZAP_EDITED_VENDOR_SAVED'));
			}
			$this->setRedirect(JRoute::_(QazapHelperRoute::getSellerRoute(), false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=sellerform&layout=edit', false));
		}

		return $result;
	}	

	
	public function getSelection()
	{
		$formRequest = $this->input->get('qzform', array(), 'array');
		$formPost = $this->input->post->get('qzform', array(), 'array');
		$data = array_merge($formRequest, $formPost);
		
		$model = $this->getModel('product');
		
		$selection = $model->getSelection($data);

		if(!$selection)
		{
			echo 'Error QazapModelProduct::getSelection() : ' . $model->getError();
			JFactory::getApplication()->close();
		}
		
		$this->input->set('view', 'product');
		$this->input->set('layout', 'json');
		$view = $this->getView('product', 'json');
		$view->setLayout('json');
		$view->setModel($model, true);
		$view->set('selection', $selection);
		$view->document = JFactory::getDocument();
		$view->display();
	}

	public function updateOrderStatus($ordergroup_id = null, $order_id = null)
	{
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel('seller');
		$data = $this->input->post->get('qzform', array(), 'array');
		$ordergroup_id = ($ordergroup_id != null) ? $ordergroup_id : $app->input->getInt('ordergroup_id');
		$order_id = $this->input->post->getInt('order_id');
			
		$data['ordergroup_id'] = $ordergroup_id;
		$data['order_id'] = $order_id;
		$urlVar = null;
		
		// Access check.
		if (!$this->allowOrderSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					QazapHelperRoute::getSellerRoute('order', $ordergroup_id)
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}		
		
		if(!$model->updateOrderStatus($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_(QazapHelperRoute::getSellerRoute('order', $ordergroup_id), false));
			return false;					
		}
		
		$this->setMessage(JText::_('COM_QAZAP_MSG_ORDER_STATUS_UPDATED'));
		//$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);
		
		$this->setRedirect(JRoute::_(QazapHelperRoute::getSellerRoute('order', $ordergroup_id), false));	
		return true;
		
	}
		
	
	public function updateItemStatus($ordergroup_id = null, $order_id = null)
	{
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();		
		$model = $this->getModel('seller');
		$data = $this->input->post->get('qzform', array(), 'array');
		$ordergroup_id = ($ordergroup_id != null) ? $ordergroup_id : $app->input->getInt('ordergroup_id');
		$order_id = $this->input->post->getInt('order_id');
			
		$data['ordergroup_id'] = $ordergroup_id;
		$data['order_id'] = $order_id;
		$urlVar = null;
		
		// Access check.
		if (!$this->allowOrderSave($data, 'ordergroup_id'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					QazapHelperRoute::getSellerRoute('order', $ordergroup_id)
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}		
		
		if(!$model->updateItemStatus($data))
		{
			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_(QazapHelperRoute::getSellerRoute('order', $ordergroup_id), false));
			return false;					
		}
		
		$this->setMessage(JText::_('COM_QAZAP_MSG_ORDER_STATUS_UPDATED'));
		//$ordergroup_id = $model->getState($this->context . '.id');
		$this->holdEditId($context, $ordergroup_id);
		
		$this->setRedirect(JRoute::_(QazapHelperRoute::getSellerRoute('order', $ordergroup_id), false));	
		return true;
		
	}

	public function allowOrderSave($data, $key ='ordergroup_id')
	{
		$user = QZUser::get();
		$juser = $user->juser;
		$model = $this->getModel();
		
		if($juser->guest)
		{
			$this->setError(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'));
			return false;
		}
		
		if(!$user->vendor_id)
		{
			$this->setError(JText::_('COM_QAZAP_INVALID_VENDOR'));
		}
		elseif(!$user->activeVendor)
		{
			$this->setError(JText::_('COM_QAZAP_INACTIVE_VENDOR'));
		}
		elseif(!$model->getOrderDetails($data['ordergroup_id'], $user->vendor_id))
		{
			$this->setError($model->getError());
			return false;
		}

		return true;
	}	
	
}
