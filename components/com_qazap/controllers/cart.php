<?php
/**
 * cart.php
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

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Shoppergroups list controller class.
 */
class QazapControllerCart extends QazapController
{
	public function checkout()
	{		
		if(!$nextPageURL = $this->getNextPage())
		{
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false), JText::_($this->getError()));
			return;
		}
		
		if(JFactory::getUser()->guest)
		{
			JFactory::getApplication()->setUserState('com_qazap.cart.checkoutmethod', 'guest');
		}

		$this->setRedirect($nextPageURL);

		return true;
	}
	
	
	public function add()
	{
		$formRequest = $this->input->get('qzform', array(), 'array');
		$formPost = $this->input->post->get('qzform', array(), 'array');		
		$data = array_merge($formRequest, $formPost);
		$fromList = isset($data['fromlist']) ? $data['fromlist'] : 0;				
		$product_name = $this->input->post->get('product_name', null, 'base64');
		$product_name = $product_name ? $product_name : $this->input->get('product_name', null, 'base64');
		$document = JFactory::getDocument();
		$vFormat = $document->getType();
		$model = $this->getModel();
		$product_id = (int) isset($data['product_id']) ? $data['product_id'] : 0;
		
		if(!empty($fromList) && $model->hasVarients($product_id))
		{
			if(strtolower($vFormat) == 'html')
			{
				$this->setMessage(JText::_('COM_QAZAP_SELECT_PRODUCT_VARIENTS'));
				$this->setRedirect(JRoute::_(QazapHelperRoute::getProductRoute($product_id), false));			
			}
			else
			{
				$this->input->set('view', 'cart');
				$this->input->set('layout', 'selectattributes');
				$view = $this->getView('cart', $vFormat);
				$view->setLayout('selectattributes');
				$view->setModel($model, true);
									
				$helper = QZProducts::getInstance();
				$product = $helper->get($product_id);
				
				if (empty($product))
				{
					$view->set('errors', JText::_('COM_QAZAP_ERROR_PRODUCT_NOT_FOUND')) ;
				}					
				else
				{	
					if(!empty($product->membership))
					{
						$product->membership = $product->getMemberships();
					}
					
					if(!empty($product->attributes))
					{
						$product->attributes = $product->getAttributes();
					}
					
					$config = QZApp::getConfig();
					$product->params = $product->getParams();
					$product->params->merge($config);					


					// Decide if add to cart button to be displayed
					$product->buy_action = 'addtocart';
					
					if($product->in_stock - $product->booked_order <= 0 && $product->params->get('enablestockcheck'))
					{
						$stockout_handle = $product->params->get('stockout_action', 'notify');
						
						if($stockout_handle == 'notify')
						{
							$product->buy_action = 'notify';
						}
						elseif($stockout_handle == 'hide')
						{
							$product->buy_action = null;
						}	
					}
					
					$view->set('errors', null);	
					$view->set('product', $product);
				}

				$view->document = JFactory::getDocument();
				$view->display();										
			}
		}
		else
		{
			$result = $model->addProduct($data);

			if(strtolower($vFormat) == 'html')
			{	
				if(!$result)
				{
					$this->setMessage($model->getError(), 'error');
					$this->setRedirect($this->getReturnPage());
					return;
				}

				$this->setMessage(JText::sprintf('COM_QAZAP_PRODUCT_ADDED_TO_CART', base64_decode($product_name)), 'Success');
				$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			}
			else
			{
				$layout = $this->input->getCmd('layout', null) ? $this->input->getCmd('layout', null) : 'ajaxpopup';
				$this->input->set('view', 'cart');
				$this->input->set('layout', $layout);
				$view = $this->getView('cart', $vFormat);
				$view->setLayout($layout);
				$view->setModel($model, true);

				$jsonResult = new stdClass;
				$jsonResult->error = false;
				$jsonResult->message = JText::sprintf('COM_QAZAP_PRODUCT_ADDED_TO_CART', base64_decode($product_name));

				if(!$result)
				{
					$jsonResult->error = true;
					$jsonResult->message = $model->getError();				
				}

				$view->set('result', $jsonResult);
				$view->document = JFactory::getDocument();
				$view->display();
			}				
		}	
	}
	
	
	public function update()
	{
		$data = $this->input->post->get('qzform', array(), 'array');
		$product_name = $this->input->post->get('product_name', null, 'base64'); 

		$model = $this->getModel();	

		if(!$model->updateProduct($data)) 
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return;	
		}

		$this->setMessage(JText::sprintf('COM_QAZAP_PRODUCT_QUANTITY_UPDATED_CART', base64_decode($product_name)), 'Success');
		$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
		return;
	}	
	
	public function remove()
	{
		$data = $this->input->post->get('qzform', array(), 'array');
		$product_name = $this->input->post->get('product_name', null, 'base64');

		$model = $this->getModel(); 	

		if(!$model->removeProduct($data)) 
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return;	
		}

		$this->setMessage(JText::sprintf('COM_QAZAP_PRODUCT_REMOVED_FROM_CART', base64_decode($product_name)), 'Success');
		$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
		return;
	}	
	
	public function userSave()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();		
		$user = JFactory::getUser();
		$model = $this->getModel();
		$formData = $this->input->post->get('qzform', array(), 'array');
		$layoutStr = $this->input->getCmd('layout') ? '&layout=' . $this->input->getCmd('layout') : '';
		$failedURL = JRoute::_(QazapHelperRoute::getCartRoute() . $layoutStr, false);
		$formData['user_id'] = $user->get('id');

		if(!isset($formData['address_type']) && !in_array(strtolower($formData['address_type']), array('bt', 'st')))
		{
			$this->setMessage('Invalid form data', 'error');
			$this->setRedirect($failedURL);
			return;
		}

		$app->setUserState('com_qazap.edit.profile.data', $formData);

		$address_type = strtolower($formData['address_type']);

		if($address_type == 'bt')
		{
			$form = $model->getBTForm();
		}
		else
		{
			$form = $model->getSTForm();
		}
		
		$profileModel = $this->getModel('profile');		
		$data	= $profileModel->validate($form, $formData);

		// Check for errors.
		if ($data === false)
		{
			// Redirect back to the edit screen.
			$this->setMessage(JText::_($model->getError()), 'warning');
			$this->setRedirect($failedURL);
			return false;
		}	
		
		// If not guest save the data to Userinfo database table
		if (!$user->guest)
		{
			// Get backend model		
			$userinfo_model = $this->getModel('userinfo');
			$return = $userinfo_model->save($data);		

			if($return === false)
			{
				// Redirect back to the edit screen.
				$this->setMessage(JText::_($userinfo_model->getError()), 'warning');
				$this->setRedirect($failedURL);
				return;
			}
		}				
		
		// Save address in the cart session
		if(!$model->saveAddress($data))
		{
			$this->setMessage($model->getError(), 'warning');
			$this->setRedirect($failedURL);
			return;
		}

		$successMessage = ($address_type == 'st') ? 'COM_QAZAP_USER_ST_ADDRESS_' : 'COM_QAZAP_USER_BT_ADDRESS_';	

		if($data['id'])
		{
			$successMessage .= 'UPDATED';
		} 
		else 
		{
			$successMessage .= 'SAVED';
		}

		$successMessage = trim($successMessage);
		// Flush the data from the session.
		$app->setUserState('com_qazap.edit.profile.data', null);

		$this->setMessage(JText::_($successMessage), 'Success');

		if(!$nextPageURL = $this->getNextPage())
		{
			$app->enqueueMessage(JText::_($this->getError()));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return;
		}
		
		$this->setRedirect($nextPageURL);

		return true;		
	}	

	public function selectShipto()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();		
		$user = JFactory::getUser();
		$model = $this->getModel();
		$formData = $this->input->post->get('qzform', array(), 'array');

		if(!isset($formData['shipto_id']) || !$formData['shipto_id']) 
		{
			$this->setMessage(JText::_('COM_QAZAP_CART_ERROR_NO_SHIPPING_ADDRESS_SELECTED'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'select_shipto'))));
			return;			
		}

		if(!$model->selectShipto($formData['shipto_id']))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=> 'select_shipto'))));
			return;
		}

		$this->setMessage(JText::_('COM_QAZAP_CART_SHIPPING_ADDRESS_SELECTED'), 'success');

		if(!$nextPageURL = $this->getNextPage())
		{
			$app->enqueueMessage(JText::_($this->getError()));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return;
		}

		$this->setRedirect($nextPageURL);
		return true;					
	}

	public function selectShipping()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();		
		$user = JFactory::getUser();
		$model = $this->getModel();
		$formData = $this->input->post->get('qzform', array(), 'array');

		if(!isset($formData['shipping_method_id']) || !$formData['shipping_method_id']) 
		{
			$this->setMessage(JText::_('COM_QAZAP_CART_ERROR_NO_SHIPPING_METHOD_SELECTED'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'select_shipping')), false));
			return;			
		}

		if(!$model->selectShipping($formData['shipping_method_id']))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'select_shipping')), false));
			return;
		}

		$this->setMessage(JText::_('COM_QAZAP_CART_SHIPPING_METHOD_SELECTED'), 'success');

		if(!$nextPageURL = $this->getNextPage())
		{
			$app->enqueueMessage(JText::_($this->getError()));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return;
		}

		$this->setRedirect($nextPageURL);
		return true;
	}

	public function selectPayment()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();		
		$user = JFactory::getUser();
		$model = $this->getModel();
		$formData = $this->input->post->get('qzform', array(), 'array');

		if(!isset($formData['payment_method_id']) || !$formData['payment_method_id']) 
		{
			$this->setMessage(JText::_('COM_QAZAP_CART_ERROR_NO_PAYMENT_METHOD_SELECTED'), 'error');
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'select_payment')), false));
			return;			
		}

		if(!$model->selectPayment($formData['payment_method_id']))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'select_payment')), false));
			return;
		}

		$this->setMessage(JText::_('COM_QAZAP_CART_PAYMENT_METHOD_SELECTED'), 'success');

		if(!$nextPageURL = $this->getNextPage())
		{
			$app->enqueueMessage(JText::_($this->getError()));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return;
		}

		$this->setRedirect($nextPageURL);

		return true;
	}	


	public function addCoupon()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();
		$model = $this->getModel();
		$coupon_code = $this->input->post->get('coupon_code', null, 'string');
		$cart_url = JRoute::_(QazapHelperRoute::getCartRoute(), false);

		if(empty($coupon_code) || !$coupon_code)
		{
			$this->setMessage(JText::_('COM_QAZAP_CART_ERROR_ENTER_A_COUPON_CODE'));
			$this->setRedirect($cart_url, JText::_('COM_QAZAP_CART_ERROR_ENTER_A_COUPON_CODE'));	
			return;		
		}	

		if(!$model->addCoupon($coupon_code))
		{
			$this->setMessage($model->getError());
			$this->setRedirect($cart_url);	
			return;	
		}

		$this->setMessage(JText::_('COM_QAZAP_CART_COUPON_DISCOUNT_ADDED'), 'success');
		$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
		return true;	
	}


	public function confirm()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));		

		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$config = QZApp::getConfig();
		$cart_url = JRoute::_(QazapHelperRoute::getCartRoute(), false);
		$formData = $this->input->post->get('qzform', array(), 'array');
		$formData['tos_accept'] = isset($formData['tos_accept']) ? true : false;

		if(!$model->setConfirmFormData($formData))
		{
			$this->setMessage($model->getError());
			$this->setRedirect($cart_url);
			return;			
		}

		if(!$formData['tos_accept'] && ($config->get('tos_acceptance', 1) == 1))
		{
			$this->setMessage(JText::_('COM_QAZAP_CART_ERROR_PENDING_TOS_ACCEPTANCE'));
			$this->setRedirect($cart_url);
			return;	
		}

		if(!$model->placeOrder())
		{
			$this->setMessage($model->getError());
			$this->setRedirect($cart_url);
			return;			
		}

		$return = $model->getOrderConfirmation();

		if(!$return)
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect($cart_url);
			return;			
		}
		// Check if Payment form is set by the payment plugin
		elseif($form = $model->getPaymentForm())
		{
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'form');
			$view = $this->getView('cart', 'html');
			$view->setLayout('form');
			$view->assignRef('form', $form);
		}
		else
		{
			$model->setConfirmedCart();
			$app->enqueueMessage(JText::_('COM_QAZAP_CART_ORDER_PLACED'), 'success');
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'confirmed');
			$view = $this->getView('cart', 'html');
			$view->setLayout('confirmed');
			$view->set('success', true);
		}

		$view->setModel($model, true);
		$view->document = JFactory::getDocument();
		$view->display();			
	}	

	protected function getNextPage($route = true)
	{
		$model = $this->getModel();	
		$nextStep = $model->getNextStep();

		if($nextStep === false)
		{
			$this->setError($model->getError());
			return false;
		}
		
		$options = array();
		
		if($nextStep)
		{
			$options['layout'] = $nextStep;
		}
		
		$url = QazapHelperRoute::getCartRoute($options);

		if($route)
		{
			return JRoute::_($url);
		}

		return $url;
	}

	/**
	 * Method to register a user.
	 *
	 * @return  boolean  True on success, false on failure.
	 * @since   1.6
	 */
	public function register()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// If registration is disabled - Redirect to login page.
		if (JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0)
		{
			$this->setMessage(JText::_('COM_QAZAP_USER_REGISTRATION_IS_DISABLED'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return false;
		}
		
		JFactory::getLanguage()->load('com_users', JPATH_SITE);
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Registration', 'UsersModel');		
		JForm::addFormPath(JPATH_SITE . '/components/com_users/models/forms');
		
		// Set active checkout method
		$app->setUserState('com_qazap.cart.checkoutmethod', 'register');
		
		// Get the user data.
		$requestData = $this->input->post->get('jform', array(), 'array');

		// Validate the posted data.
		$form	= $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data	= $model->validate($form, $requestData);

		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

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
			$app->setUserState('com_users.registration.data', $requestData);

			// Redirect back to the registration screen.
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return false;
		}

		// Attempt to save the data.
		$return	= $model->register($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.registration.data', null);

		// Redirect to the profile screen.
		if ($return === 'adminactivate')
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
		}
		elseif ($return === 'useractivate')
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
		}
		else
		{
			$options = array();
			$options['silent'] = true;
			$options['entry_url'] = JUri::base() . 'index.php?option=com_qazap&task=cart.register';
			
			if(!$app->login($data, $options))
			{
				$this->setMessage(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
			}
			else
			{
				$this->setMessage(JText::_('COM_QAZAP_REGISTRATION_SAVE_SUCCESS'));
			}			
			$this->setRedirect(JRoute::_(QazapHelperRoute::getCartRoute(), false));
		}

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
	public function getModel($name = 'cart', $prefix = '', $config = array())
	{
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models');
		$this->addModelPath(JPATH_SITE . '/components/com_users/models/');
		$model = parent::getModel($name, $prefix, $config);

		return $model;
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
		$return = $this->input->post->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base();
		}
		else
		{
			return base64_decode($return);
		}
	}	
}