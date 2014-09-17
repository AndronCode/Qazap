<?php
/**
 * view.html.php
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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Qazap.
 */
class QazapViewCart extends JViewLegacy
{
	protected $state;	
	protected $cart;
	protected $isEmpty;
	protected $params;	
	protected $BTForm;
	protected $STForm;
	protected $registrationForm;
	protected $shippingMethods;
	protected $paymentMethods;
	protected $coupon_input_text;
	protected $form;
	protected $confirmedCart;
	protected $confirmButton;
	protected $confirmButtonClass;
	protected $cnFieldName;
	protected $tosFieldName;
	protected $tos;
	protected $confirm;
	protected $user;
	protected $checkoutMethod;
	protected $continue_link;


	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
    $this->state						= $this->get('State');
    $this->cart							= $this->get('Cart');
		$this->params						= $this->state->get('params');		
		$this->tos							= $this->get('TOS');
		$this->registrationForm = $this->get('RegistrationForm');

		$this->user							= JFactory::getUser();
		$this->cnFieldName			= 'qzform[customer_note]';
		$this->tosFieldName			= 'qzform[tos_accept]';
		
		$layout = $this->getLayout();		

		if($layout == 'edit_billto')
		{
			$this->BTForm			= $this->get('BTForm');
		}	
		elseif($layout == 'select_shipto')
		{
			if($this->user->guest)
			{
				$this->setLayout('edit_shipto');
			}
			else
			{
				$this->UserAddresses	= $this->get('UserAddresses');
			}			
		}
		elseif($layout == 'edit_shipto')
		{
			$this->STForm			= $this->get('STForm');
		}						
		elseif($layout == 'select_shipping')
		{
			$this->shippingMethods = $this->get('ShippingMethods');	
		}
		elseif($layout == 'select_payment')
		{
			$this->paymentMethods = $this->get('PaymentMethods');
		}
		elseif($layout == 'confirmed')
		{
			$this->confirmedCart = $this->get('ConfirmedCart');
			
			if(empty($this->confirmedCart))
			{
				$this->setLayout('default');
			}
		}
		elseif($layout == 'form' && $this->form)
		{
			$this->setLayout('form');
		}
		else
		{
			$this->setLayout('default');
		}
		
		$app = JFactory::getApplication();
		$this->checkoutMethod = $app->getUserState('com_qazap.cart.checkoutmethod', 'guest');
		
    // Check for errors.
    if (count($errors = $this->get('Errors'))) 
    {
			throw new Exception(implode("\n", $errors));
    }

		$this->_prepareContent();	
		$this->_prepareButtonVars();			
		$this->_prepareDocument();
		
		parent::display($tpl);
	}

	protected function _prepareButtonVars()
	{
		static $pathAdded = false;
		
		$app = JFactory::getApplication();
		$pathway = $app->getPathway();
		$layout = $this->getLayout();
		$nextStep = $this->get('NextStep');
		$buttonData = new stdClass;
		$buttonData->buttonTxt = null;
		$buttonData->buttonID = null;
		$buttonData->confirmButtonClass = !empty($this->confirmButtonClass) ? 
																				$this->confirmButtonClass : 
																				'btn btn-large btn-primary validate pull-right';
		$buttonData->inputs = array();
		$buttonData->inputs['option'] = 'com_qazap';

		switch ($layout) 
		{
			case 'default':
				
				if($this->isEmpty)
				{
					$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CHECKOUT');
					$buttonData->buttonID = 'qazap-cart-checkout-button';
					$buttonData->inputs['task'] = 'cart.checkout';
					$this->document->setTitle(JText::_('COM_QAZAP_CART_OVERVIEW'));						
				}
				elseif(!$nextStep)
				{
					$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CONFIRM_ORDER');
					$buttonData->buttonID = 'qazap-cart-confirm-button';
					$buttonData->inputs['task'] = 'cart.confirm';
					$this->confirm = true;
					$this->document->setTitle(JText::_('COM_QAZAP_CART_CONFIRM_ORDER'));
				}
				elseif($this->user->guest && $this->params->get('guest_checkout', 1))
				{
					$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CHECKOUT_AS_GUEST');
					$buttonData->buttonID = 'qazap-cart-guest-checkout-button';
					$buttonData->inputs['task'] = 'cart.checkout';
					$this->document->setTitle(JText::_('COM_QAZAP_CART_CHECKOUT'));
				}
				elseif(!$this->user->guest)
				{
					$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CHECKOUT');
					$buttonData->buttonID = 'qazap-cart-checkout-button';
					$buttonData->inputs['task'] = 'cart.checkout';
					$this->document->setTitle(JText::_('COM_QAZAP_CART_CHECKOUT'));				
				}
				
				if(!$pathAdded)
				{
					$pathway->addItem(JText::_('COM_QAZAP_CART_OVERVIEW'));
					$pathAdded = true;
				}					
				break;
				
			case 'edit_billto':
				$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CONFIRM_BILLING_ADDRESS');
				$buttonData->buttonID = 'qazap-cart-billto-confirm-button';
				$qzuser = QZUser::get();	
				$buttonData->inputs['qzform[id]'] = $qzuser->get('id');
				$buttonData->inputs['qzform[address_type]'] = 'bt';
				$buttonData->inputs['task'] = 'cart.usersave';
				if(!$pathAdded)
				{
					$pathway->addItem(JText::_('COM_QAZAP_CART_OVERVIEW'), 'index.php?option=com_qazap&view=cart');	
					$pathway->addItem(JText::_('COM_QAZAP_CART_CONFIRM_BILLING_ADDRESS'));
					$pathAdded = true;
				}
				$this->document->setTitle(JText::_('COM_QAZAP_CART_CONFIRM_BILLING_ADDRESS'));	
				break;
				
			case 'edit_shipto':
				$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS');
				$buttonData->buttonID = 'qazap-cart-shipto-confirm-button';
				$buttonData->inputs['qzform[id]'] = $this->STForm->getValue('id');
				$buttonData->inputs['qzform[address_type]'] = 'st';
				$buttonData->inputs['task'] = 'cart.usersave';
				if(!$pathAdded)
				{
					$pathway->addItem(JText::_('COM_QAZAP_CART_OVERVIEW'), 'index.php?option=com_qazap&view=cart');	
					$pathway->addItem(JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS'));		
					$pathAdded = true;
				}		
				$this->document->setTitle(JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS'));
				break;

			case 'select_shipto':
				$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS');
				$buttonData->buttonID = 'qazap-cart-shipto-confirm-button';
				$buttonData->inputs['task'] = 'cart.selectshipto';
				if(!$pathAdded)
				{
					$pathway->addItem(JText::_('COM_QAZAP_CART_OVERVIEW'), 'index.php?option=com_qazap&view=cart');	
					$pathway->addItem(JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS'));	
					$pathAdded = true;
				}
				$this->document->setTitle(JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS'));	
				break;
				
			case 'select_shipping':
				$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CONFIRM_SHIPMENT_METHOD');
				$buttonData->buttonID = 'qazap-cart-shipment-confirm-button';
				$buttonData->inputs['task'] = 'cart.selectshipping';
				if(!$pathAdded)
				{
					$pathway->addItem(JText::_('COM_QAZAP_CART_OVERVIEW'), 'index.php?option=com_qazap&view=cart');	
					$pathway->addItem(JText::_('COM_QAZAP_CART_CONFIRM_SHIPMENT_METHOD'));
					$pathAdded = true;
				}					
				$this->document->setTitle(JText::_('COM_QAZAP_CART_CONFIRM_SHIPMENT_METHOD'));
				break;
				
			case 'select_payment':
				$buttonData->buttonTxt = JText::_('COM_QAZAP_CART_CONFIRM_PAYMENT_METHOD');
				$buttonData->buttonID = 'qazap-cart-payment-confirm-button';
				$buttonData->inputs['task'] = 'cart.selectpayment';
				if(!$pathAdded)
				{
					$pathway->addItem(JText::_('COM_QAZAP_CART_OVERVIEW'), 'index.php?option=com_qazap&view=cart');	
					$pathway->addItem(JText::_('COM_QAZAP_CART_CONFIRM_PAYMENT_METHOD'));
					$pathAdded = true;
				}					
				$this->document->setTitle(JText::_('COM_QAZAP_CART_CONFIRM_PAYMENT_METHOD'));
				break;
			
			case 'form':		
				$this->document->setTitle(JText::_('COM_QAZAP_CART_PROCESSING_PAYMENT'));
				break;

			case 'confirmed':								
				$this->document->setTitle(JText::_('COM_QAZAP_CART_ORDER_PLACED'));
				break;										
		}		
		
		$buttonLayout = new JLayoutFile('confirm_button', $basePath = QZPATH_LAYOUT . DS . 'cart');
		$this->confirmButton = $buttonLayout->render($buttonData);
		
		$lastvisited_category_id = (int) $app->getUserState('com_qazap.category.lastvisted.id', 0);
		
		if($lastvisited_category_id > 0)
		{
			$this->continue_link = JRoute::_(QazapHelperRoute::getCategoryRoute($lastvisited_category_id));
		}
		else
		{
			$this->continue_link = JUri::base();
		}
			
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$this->document->setMetadata('robots', 'NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');
	}
	
	protected function _prepareContent()
	{
		if(!empty($this->cart) && ($this->cart instanceof QZCart))
		{
			$products = $this->cart->getProducts();
			
			if(empty($products))
			{
				$this->isEmpty = true;
			}
			else
			{
				$this->isEmpty = false;
			}
			
			if($this->cart->coupon_code)
			{
				$this->coupon_input_text = JText::_('COM_QAZAP_CART_EDIT_COUPON');
			}
			else
			{
				$this->coupon_input_text = JText::_('COM_QAZAP_CART_ADD_COUPON');
			}			
		}
	}    
    	
}
