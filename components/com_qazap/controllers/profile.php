<?php
/**
 * profile.php
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

jimport('joomla.application.component.controller');

/**
 * Shoppergroups list controller class.
 */
class QazapControllerProfile extends JControllerForm
{

	/**
	* The URL view item variable.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $view_item = 'profile';

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
	protected $urlVar = 'a.id';


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
		$user       = JFactory::getUser();
		$allow      = null;

		if ($user->get('id') > 0)
		{
			$allow	= true;
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
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;		
		$user = JFactory::getUser();
		$user_id = $user->get('id');
		
		// Now test the owner is the user.
		$ownerId = (int) isset($data['user_id']) ? $data['user_id'] : 0;
		
		if (empty($ownerId) && $recordId)
		{
			// Need to do a lookup from the model.
			$record = $this->getModel()->getItem($recordId);

			if (empty($record))
			{
				return false;
			}

			$ownerId = $record->user_id;
		}

		// If the owner matches 'me' then do the test.
		if ($ownerId == $user_id)
		{
			return true;
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
	public function cancel($key = 'id')
	{
		$type = $this->input->get('type', 'bt', 'string');
		
		if(strtolower($type) == 'bt')
		{
			$qzuser = QZUser::get();
			$this->input->set('id', $qzuser->get('id'));
		}
				
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
	public function edit($key = 'id', $urlVar = null)
	{
		$type = $this->input->get('type', 'bt', 'string');

		if(strtolower($type) == 'bt')
		{
			$qzuser = QZUser::get();
			$this->input->set('id', $qzuser->get('id'));
		}

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
	* @since   1.0.0
	*/
	public function getModel($name = 'profile', $prefix = '', $config = array('ignore_request' => true))
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
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		// Need to override the parent method completely.
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit', 'string');
		$type = $this->input->get('type', '', 'string');

		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl='.$tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if(!empty($type))
		{
			$append .= '&type='.$type;
		}

		if (strtolower($type) == 'st' && $recordId)
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
		return JRoute::_('index.php?option=com_qazap&view=profile', false);
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
	public function save($key = null, $urlVar = 'id')
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$user = JFactory::getUser();
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
		
		if($user->get('id') > 0)
		{
			$data['user_id'] = $user->get('id');
		}
		
		$recordId = $data[$key];
		
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

			$this->setRedirect($this->getReturnPage());

			return false;
		}
		
		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$model->setState($this->context . '.address_type', $data['address_type']);
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
		
		$successMessage = ($data['address_type'] == 'st') ? 'COM_QAZAP_USER_ST_ADDRESS_' : 'COM_QAZAP_USER_BT_ADDRESS_';	
			
		if($data['id'])
		{
			$successMessage .= 'UPDATED';
		} 
		else 
		{
			$successMessage .= 'SAVED';
		}
		
		
		$this->setMessage(JText::_($successMessage));

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
				$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=profile', false));
				break;
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);
		
		$this->setRedirect($this->getReturnPage());
		return true;
	}
	
	/*
	*
	* Remove Product from Wishlist
	* 
	*/
	public function deleteWishList()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$return = $this->input->post->get('return', null, 'base64');
		$form = $this->input->post->get('qzform', array(), 'array');
		
		$model = $this->getModel('profile');
		if(!$model->deleteWishlist($form['id']))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;
		}
		else
		{
			$this->setMessage(JText::_('COM_QAZAP_WISHLIST_SUCCESSFULLY_REMOVED'), 'success');
		}
		$this->setRedirect(JRoute::_(base64_decode($return), false));
		return true;
	}
	
/*
	*
	* Remove Product from Wishlist
	* 
	*/
	public function deleteWaitingList()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$return = $this->input->post->get('return', null, 'base64');
		$form = $this->input->post->get('qzform', array(), 'array');
		
		$model = $this->getModel('profile');
		if(!$model->deleteWaitingList($form['id']))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(base64_decode($return), false));
			return;
		}
		else
		{
			$this->setMessage(JText::_('COM_QAZAP_WAITING_LIST_SUCCESSFULLY_REMOVED'), 'success');
		}
		$this->setRedirect(JRoute::_(base64_decode($return), false));
		return true;
	}	
	
}