<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 */
class QazapControllerProduct extends JControllerForm
{
	/**
	* The URL view item variable.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $view_item = 'form';

	/**
	* The URL view list variable.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $view_list = 'categories';

	/**
	* The URL edit variable.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $urlVar = 'a.product_id';

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
		$juser		= JFactory::getUser();
		$user 		= QZUser::get();
		$categoryId	= JArrayHelper::getValue($data, 'category_id', $this->input->getInt('category_id'), 'int');
		$allow		= null;

		if ($categoryId > 0)
		{
			// If the category has been passed in the data or URL check it.
			$allow	= $juser->authorise('core.create', 'com_qazap.category.'.$categoryId);
		}

		if($user->activeVendor)
		{
			$allow = true;
		}		

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
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
	protected function allowEdit($data = array(), $key = 'product_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = QZUser::get();
		$juser = JFactory::getUser();
		$vendorId = $user->get('vendor_id');
		$canDo = QZHelper::getActions();

		// Check if user is a active vendor and general edit permission first.
		if ($user->activeVendor || ($canDo->get('core.edit') || ($canDo->get('core.edit.own'))))
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

				$ownerId = $record->vendor;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $vendorId || $juser->get('isRoot'))
			{
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
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
	public function edit($key = null, $urlVar = 'product_id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
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
	 * @since   1.5
	 */
	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$this->addModelPath(QZPATH_MODEL_ADMIN);
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
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'product_id')
	{
		// Need to override the parent method completely.
		$tmpl   = $this->input->get('tmpl');
		//		$layout = $this->input->get('layout', 'edit');
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
		$return	= $this->getReturnPage();
		$catId  = $this->input->getInt('category_id', null, 'get');

		if ($itemId)
		{
			$append .= '&Itemid='.$itemId;
		}

		if ($catId)
		{
			$append .= '&category_id='.$catId;
		}

		if ($return)
		{
			$append .= '&return='.base64_encode($return);
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
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base();
		}
		else
		{
			return base64_decode($return);
		}
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
	public function save($key = null, $urlVar = 'product_id')
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getModel();
		$table = $model->getTable();
		$data  = $this->input->post->get('qzform', array(), 'array');
		$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.edit.$this->context";
		$task = $this->getTask();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if ($checkin && $model->checkin($data[$key]) === false)
			{
				// Check-in failed. Go back to the item and display a notice.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}

			// Reset the ID and then treat the request as for Apply.
			$data[$key] = 0;
			$task = 'apply';
		}

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}

		// Attempt to save the data.
		if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($validData[$key]) === false)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		$this->setMessage(
			JText::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$model->checkout($recordId);

				// Redirect back to the edit screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend(null, $urlVar), false
					)
				);
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect to the list screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);
				break;
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);
		
		$this->setRedirect($this->getReturnPage());
		return true;
	}

	/**
	 * Method to save a addreview
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addreview()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$config = QZApp::getConfig();

		$return = $this->input->post->get('return', null, 'base64');
		$form = $this->input->post->get('qzform', array(), 'array');
		
		if($user->guest)
		{
			$this->setMessage(JText::_('COM_QAZAP_PLEASE_LOGIN_TO_POST_REVIEW'), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;			
		}
		
		$model = $this->getModel('review');
		$form['id'] = 0;
		$form['user_id'] = $user->get('id');
		
		
		if(!$model->save($form))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;
		}
	
		if($config->get('new_review_approval') == 0)
		{
			$this->setMessage(JText::_('COM_QAZAP_REVIEW_POSTED_SUCCESSFULLY'), 'success');
		}		
		else
		{
			$this->setMessage(JText::_('COM_QAZAP_REVIEW_SUCCESSFULLY_POSTED_FOR_REVIEW'), 'success');
		}
		$this->setRedirect(JRoute::_(base64_decode($return), false));
		return true;
	}
	
	/**
	* Method for storing data to notify table
	* 
	* @return boolean True if success, false if failed
	*  
	* @since 1.0
	*/
	public function notify()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$return = $this->input->post->get('return', null, 'base64');
		$form = $this->input->post->get('qzform', array(), 'array');
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_qazap');
		$notify = $params->get('notify_registered_only');
		$mail_activation = $params->get('notify_mail_activation');
	
		if($notify == 1 && $user->guest)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return='.$return, false));
			return;
		}
		if($mail_activation == 1)
		{
			$form['block'] = 1;
			$form['activation_key'] = JApplication::getHash(JUserHelper::genRandomPassword());
		}
		if(!$form['user_email'])
		{
			$this->setMessage(JText::_('COM_QAZAP_ENTER_VALID_EMAIL'), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;			
		}
		
		$notifyModel = QZApp::getModel('Notify', array('ignore_request' => true));
		
		//Save Email to notify table for sending the notification
		if(!$notifyModel->saveNotify($form))
		{
			$this->setMessage($notifyModel->getError(), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;
		}
		else
		{
			$this->setMessage(JText::_('COM_QAZAP_NOTIFY_SUCCESSFULLY_SAVED'), 'success');
		}
		
		$this->setRedirect(JRoute::_(base64_decode($return), false));
		return true;
	}
	
	/*
	* 
	* Add to Wishlist
	*/
	public function wishlist()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$return = $this->input->post->get('return', null, 'base64');
		$form = $this->input->post->get('qzform', array(), 'array');
		
		if(!$form['user_id'])
		{
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return='.$return, false), JText::_('COM_QAZAP_LOGIN_FIRST_TO_ADD_WISHLIST'));
			return;		
		}
		
		$model = $this->getModel('product');
		
		if(!$model->saveWishlist($form))
		{
			$this->setRedirect(JRoute::_(base64_decode($return), false), $model->getError());
			return;
		}

		$this->setMessage(JText::_('COM_QAZAP_WISHLIST_SUCCESSFULLY_SAVED'), 'success');
		$this->setRedirect(JRoute::_(base64_decode($return), false));		
		return true;
	}
	
	/*
	* 
	* Ask a question about product
	*/
	public function askQuestion()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$return = $this->input->post->get('return', null, 'base64');
		$form = $this->input->post->get('qzform', array(), 'array');
		$user = JFactory::getUser();
		$config = JComponentHelper::getParams('com_qazap');
		$contact = $config->get('contact_mail_registered_only');
		

		if($contact == 1 && $user->guest)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login&return='.$return, false));
			return;
		}  
		
		$model = $this->getModel('product');
		
		if(!$form['user_email'])
		{
			$this->setMessage(JText::_('COM_QAZAP_ENTER_VALID_EMAIL'), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;			
		}
		
		if(!$model->askQuestion($form))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;
		}
		
		$this->setMessage(JText::_('COM_QAZAP_CONTACT_THANK_YOU_MSG'), 'success');
		$this->setRedirect(JRoute::_(base64_decode($return), false));
		return true;
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
}
