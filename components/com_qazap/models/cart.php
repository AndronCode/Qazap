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

// Base this model on the backend version.
require_once(QZPATH_MODEL_ADMIN . DS . 'order.php');


/**
* Methods supporting a list of Qazap records.
*/
class QazapModelCart extends QazapModelOrder
{
	
	protected $_cart = null;
	
	protected $_btForm = null;
	
	protected $_stForm = null;
	
	protected $_userAddresses = null;
	
	protected $_stAddress = array();
	
	protected $_vendorUnacceptedShippings = array();
	
	protected $_ordergroup = null;
	
	protected $_paymentForm = null;
	
	protected $_confirmedCart = null;
	
	protected $_tos = null;
	
	protected $_missing_selection = null;
	
	protected $_product = null;
		
	protected $_compress;

	/**
	* Model typeAlias string. Used for version history.
	*
	* @var        string
	*/
	public $typeAlias = 'com_qazap.cart';	
	/**
	* Constructor.
	*
	* @param			array    An optional associative array of configuration settings.
	* @see				JController
	* @since			1.0.0.0
	*/
  public function __construct($config = array()) 
  {
		parent::__construct($config);
		
		if (function_exists('gzcompress') && function_exists('gzuncompress')) 
		{
			$this->_compress = true;
		}
		else
		{
			$this->_compress = false;
		}
  }
  
	protected function populateState()
	{
		$app = JFactory::getApplication();
		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);
		
		if($app->input->getString('layout') == 'edit_shipto')
		{
			$this->setState('shipto.userinfo.id', $app->input->getInt('id', 0));
		}

		$this->setState('layout', $app->input->getString('layout'));
	} 
	
	public function setCart()
	{
		if($this->_cart instanceof QZCart)
		{
			$this->_cart->setCartStates();
			$session = JFactory::getSession();
			
			if($this->_compress)
			{
				$data = base64_encode(gzcompress(serialize($this->_cart)));
			}
			else
			{
				$data = serialize($this->_cart);
			}
			
			$session->set('QazapCart', $data, 'qazap');	
			return true;
		}
		
		return false;
	}
	
	public function clearCart($session_id = null)
	{
		$session = JFactory::getSession();
		
		if(empty($session_id) || ($session->getId() == $session_id))
		{
			$session->clear('QazapCart', 'qazap');
		}
		else
		{
			$result = $this->clearCartBySessionID($session_id);
		}		
	}
	
	public function getCart()
	{
		$session = JFactory::getSession();
		$cartSession = $session->get('QazapCart', 0, 'qazap');
		$user = JFactory::getUser();
		$cartUpdated = false;		

		if(!empty($cartSession))
		{
			if($this->_compress)
			{	
				$this->_cart = unserialize(gzuncompress(base64_decode($cartSession))); 
			}
			else
			{
				$this->_cart = unserialize($cartSession);
			}			
		}
		else
		{
			$this->_cart = new QZCart();
			$this->_cart->createOrderGroup();
			
			if($user->get('id') > 0)
			{
				if(!$this->autoAddAddresses())
				{
					$this->setError($this->getError());
					return false;
				}
				
				$cartUpdated = true;				
			}		
		}
		
		// If cart initiated as guest and user gets logged in thereafter
		if($this->_cart->user_id != $user->get('id'))
		{
			if(!$this->_cart->recalculate())
			{
				$this->setError($this->_cart->getError());
				return false;
			}
			
			if($user->get('id') > 0 && !$this->autoAddAddresses())
			{
				$this->setError($this->getError());
				return false;
			}
						
			$cartUpdated = true;
		}
		
		// If display currency changed
		if($this->_cart->user_currency != QZHelper::getDisplayCurrency())
		{
			$this->_cart->user_currency = QZHelper::getDisplayCurrency();
			$this->currency_exchange_rate = QZHelper::getExchangeRate();
			$cartUpdated = true;
		}

		if($cartUpdated === true)
		{
			// Set the updated cart to session
			$this->setCart();
		}
		
		return $this->_cart;
	}
	
	
	public function addProduct($data)
	{	
		$config	= QZApp::getConfig();

		list($product_id, $attr_ids, $membership_id, $quantity) = $this->getVars($data);
		
		$group_id = $this->getGroupID($product_id, $attr_ids, $membership_id);

		$cart = $this->getCart();		
		
		if($in_cart = $cart->getProduct($group_id))
		{
			$quantity += $in_cart->product_quantity;
		}		
	
		if(!$cartItem = QZOrderitem::getCartItem($product_id, $attr_ids, $membership_id, $quantity))
		{
			$this->setError($cartItem->getError());
			return false;			
		}
		
		$this->_product = $cartItem;
		
    if(isset($data['membership']) && empty($data['membership']) && $config->get('membership_required', 1))
    {
    	$this->_missing_selection = true;
      $this->setError('COM_QAZAP_ERROR_SELECT_A_MEMBERSHIP');
      return false;
    } 
    
    if(isset($data['attributes']) && !empty($data['attributes']) && $config->get('attribute_required', 1))
    {
      foreach($data['attributes'] as $attr)
      {
        if(empty($attr))
        {
        	$this->_missing_selection = true;
          $this->setError('COM_QAZAP_ERROR_SELECT_CART_ATTRIBUTES');
          return false;
        }
      }
    }		
		
		if(!$cartItem->checkQuantity())
		{	
			$this->setError($cartItem->getError());
			return false;
		}

		if($config->get('enablestockcheck', 1) && !$cartItem->checkStock())
		{
			$this->setError($cartItem->getError());
			return false;
		}
		
		$cartItem->createGroup();
		$cartItem->calculateCommission();		
		
		if(!$cart->addProduct($cartItem))
		{
			$this->setError($cart->getError());
			return false;
		}
		
		// Set cart order status as cart item's order status.
		$cart->setOrderStatus($cart->order_status, $cartItem->group_id);
		
		if($cart->coupon_code !== null && !$this->reapplyCoupon($cart, $cart->coupon_code))
		{
			$this->setError($this->getError());
			return false;
		}		
		elseif(!$cart->calculateCart())
		{
			$this->setError($cart->getError());
			return false;			
		}

		// Revalidate selected shipping method
		if($cart->cart_shipment_method_id && !$this->validateShippingMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetShippingMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_SHIPPING_METHOD_UNSET'));			
		}
		
		// Revalidate selected payment method
		if($cart->cart_payment_method_id && !$this->validatePaymentMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetPaymentMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_PAYMENT_METHOD_UNSET'));			
		}
		
		// Set the cart to session
		$this->setCart();
		
		return true;
	}
	
	public function updateProduct($data)
	{	
		//QZApp::dump($this->clearCart());exit;
		$config	= QZApp::getConfig();
		
		if(!isset($data['group_id']))
		{
			$this->setError('No product group id available for update');
			return false;
		}		

		list($product_id, $attr_ids, $membership_id) = $this->groupToArray($data['group_id']);

		if(!$cartItem = QZOrderitem::getCartItem($product_id, $attr_ids, $membership_id, $data['quantity']))
		{
			$this->setError($cartItem->getError());
			return false;			
		}
		
		if(!$cartItem->checkQuantity())
		{	
			$this->setError($cartItem->getError());
			return false;
		}

		if($config->get('enablestockcheck', 1) && !$cartItem->checkStock())
		{
			$this->setError($cartItem->getError());
			return false;
		}	
		
		$cartItem->createGroup();
		$cartItem->calculateCommission();		
		
		$cart = $this->getCart();

		if(!$cart->addProduct($cartItem))
		{
			$this->setError($cart->getError());
			return false;
		}		
		
		// Set cart order status as cart item's order status.
		$cart->setOrderStatus($cart->order_status, $cartItem->group_id);
			
		if(!$cart->calculateCart())
		{
			$this->setError($cart->getError());
			return false;			
		}

		// Revalidate selected shipping method
		if($cart->cart_shipment_method_id && !$this->validateShippingMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetShippingMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_SHIPPING_METHOD_UNSET'));			
		}
		
		// Revalidate selected payment method
		if($cart->cart_payment_method_id && !$this->validatePaymentMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetPaymentMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_PAYMENT_METHOD_UNSET'));			
		}
		
		// Set the cart to session
		$this->setCart();
		
		return true;
	}	

	public function removeProduct($data)
	{	
		if(!isset($data['group_id']) || !isset($data['vendor']))
		{
			$this->setError('No product group id available for removal');
			return false;
		}		

		$cart = $this->getCart();
		
		if(!$cart->removeProduct($data['vendor'], $data['group_id']))
		{
			$this->setError($cart->getError());
			return false;
		}		
		
		if(!$cart->calculateCart())
		{
			$this->setError($cart->getError());
			return false;			
		}
		
		// Revalidate selected shipping method
		if($cart->cart_shipment_method_id > 0 && !$this->validateShippingMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetShippingMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_SHIPPING_METHOD_UNSET'));			
		}
		
		// Revalidate selected payment method
		if($cart->cart_payment_method_id > 0 && !$this->validatePaymentMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetPaymentMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_PAYMENT_METHOD_UNSET'));			
		}		
		
		// Set the cart to session
		$this->setCart();
		
		return true;
	}	
	
	public function getVars($data)
	{
		$return = array(
							isset($data['product_id']) ? $data['product_id'] : 0, // product_id
							(isset($data['attributes']) && count($data['attributes'])) ? $data['attributes'] : array(), // attr_ids
							(isset($data['membership']) && $data['membership']) ? $data['membership'] : null, // membership_id
							isset($data['quantity']) ? $data['quantity'] : null //quantity
							);
		
		return $return;				
	}
	
	/**
	* Method to build product group id
	* 
	* @param	integer	$product_id 		Selected Product ID
	* @param	array		$attr_ids				Array of celected Attribute IDs
	* @param	integer	$membership_id	Selected Membership ID
	* 
	* @return	string	Group ID
	* @since	1.0.0.0
	*/
	public function getGroupID($product_id, $attr_ids, $membership_id)
	{
		$group_id = (string) $product_id;
		
		if(is_array($attr_ids) && count($attr_ids))
		{
			$group_id .= '::' . implode(':', $attr_ids) . '::';
		}
		else
		{
			$group_id .= '::0::';
		}
		
		if($membership_id)
		{
			$group_id .= (string) $membership_id;
		}
		else
		{
			$group_id .= '0';
		}
		
		return $group_id;
	}	

	/**
	* Method to get Billing address form
	* 
	* @return	JForm object
	* @since	1.0.0.0
	*/
	public function getBTForm()
	{
		if($this->_btForm === null)
		{
			$profileModel = QZApp::getModel('Profile', array('ignore_request' => true), false);
			$profileModel->setState('com_qazap.profile.id', 0);
			$profileModel->setState('address_type', 'bt');
			$profileModel->setState('vendor', 0);
			$profileModel->setState('user.id', JFactory::getUser()->get('id'));
			$profileModel->setState('params', QZApp::getConfig());
			if(!$form = $profileModel->getForm())
			{
				$this->setError($profileModel->getError());
				return false;
			}
			
			$this->_btForm = $form;
		}
		
		return $this->_btForm;
	}
	
	/**
	* Method to get Shipping address form
	* 
	* @return	JForm object
	* @since	1.0.0.0
	*/	
	public function getSTForm()
	{
		if($this->_stForm === null)
		{
			$app = JFactory::getApplication();
			$profileModel = QZApp::getModel('Profile', array('ignore_request' => true), false);
			$profileModel->setState($profileModel->getName() . '.id', $this->getState('shipto.userinfo.id'));	
			$profileModel->setState($profileModel->getName() . '.address_type', 'st');
			$profileModel->setState('vendor', 0);
			$profileModel->setState('user.id', JFactory::getUser()->get('id'));
			$profileModel->setState('params', $app->getParams());
			
			if(!$form = $profileModel->getForm())
			{
				$this->setError($profileModel->getError());
				return false;
			}
			
			$this->_stForm = $form;
		}

		return $this->_stForm;
	}	
	
	/**
	* Method to get saved user address / addresses from Table or from session for Guest user
	* 
	* @return	Mixed	array/boolean		Array of addresses data or false in case failure
	* @since	1.0.0.0
	*/		
	public function getUserAddresses()
	{
		if($this->_userAddresses === null)
		{
			$profileModel = QZApp::getModel('Profile', array('ignore_request' => true), false);
			$btAddress = $profileModel->getBTAddress();
			
			if($btAddress === false && $profileModel->getError())
			{
				$this->setError($profileModel->getError());
				return false;
			}
			
			if(!empty($btAddress))
			{
				$btAddress->address_name = JText::_('COM_QAZAP_BILLING_ADDRESS');
			}			
			
			$addresses = $profileModel->getSTAddresses();
			
			if($addresses === false && $profileModel->getError())
			{
				$this->setError($profileModel->getError());
				return false;
			}
			
			if(!empty($addresses) && !empty($btAddress))
			{
				array_unshift($addresses, $btAddress);
				$this->_userAddresses = $addresses;
			}
			elseif(!empty($btAddress))
			{
				$this->_userAddresses = array($btAddress);
			}			
			else
			{
				$this->_userAddresses = array();	
			}			
		}
		
		return $this->_userAddresses;
	}
	
	protected function autoAddAddresses()
	{
		$profileModel = QZApp::getModel('Profile', array('ignore_request' => true), false);
		$btAddress = $profileModel->getBTAddress();
		
		if($btAddress === false && $profileModel->getError())
		{
			$this->setError($profileModel->getError());
			return false;
		}		
		
		if(!empty($btAddress))
		{
			$stAddress = clone $btAddress;
			$stAddress->address_name = JText::_('COM_QAZAP_BILLING_ADDRESS');
			$stAddress->id = 0;
			
			$this->_cart->billing_address = JArrayHelper::fromObject($btAddress);
			$this->_cart->shipping_address = JArrayHelper::fromObject($stAddress);
			
			// Revalidate selected shipping method
			if($this->_cart->cart_shipment_method_id && !$this->validateShippingMethod($this->_cart))
			{
				if($this->getError())
				{
					$this->setError($this->getError());
					return false;
				}			
				
				if(!$this->_cart->unsetShippingMethod())
				{
					$this->setError($this->_cart->getError());
					return false;
				}
				
				JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_SHIPPING_METHOD_UNSET'));			
			}
			
			// Revalidate selected payment method
			if($this->_cart->cart_payment_method_id && !$this->validatePaymentMethod($this->_cart))
			{
				if($this->getError())
				{
					$this->setError($this->getError());
					return false;
				}			
				
				if(!$this->_cart->unsetPaymentMethod())
				{
					$this->setError($this->_cart->getError());
					return false;
				}
				
				JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_PAYMENT_METHOD_UNSET'));			
			}			
			
		}
		
		return true;	
	}

	/**
	* Method to set billing address and shipping address in cart
	* 
	* @return	boolean	(true/false)
	* @since	1.0.0
	*/		
	public function saveAddress($data)
	{
		$cart = $this->getCart();
		$config = QZApp::getConfig();
		$noShipping = $config->get('intangible', 0) || $config->get('downloadable', 0);
		
		if($noShipping)
		{
			$cart->setBTAddress($data);
			$cart->setSTAddress($data);
		}
		elseif($data['address_type'] == 'bt')
		{
			$cart->setBTAddress($data);
		}
		else
		{
			$cart->setSTAddress($data);
		}		
		
		if(!$cart->recalculate())
		{
			$this->setError($cart->getError());
			return false;
		}
		
		// Revalidate selected shipping method
		if(!$noShipping && ($cart->cart_shipment_method_id > 0) && !$this->validateShippingMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetShippingMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_SHIPPING_METHOD_UNSET'));			
		}
		
		// Revalidate selected payment method
		if($cart->cart_payment_method_id > 0 && !$this->validatePaymentMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetPaymentMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_PAYMENT_METHOD_UNSET'));			
		}		
		
		$this->setCart();
		return true;
	}

	/**
	* Method to set a already available shipping address in cart
	* 
	* @param	integer	$shipto_to	Shipping address Userinfo table id
	* 
	* @return	boolean	(true/false)
	* @since	1.0.0
	*/	
	public function selectShipto($shipto_id)
	{
		$cart = $this->getCart();
		
		if($shipto_id == -1)
		{
			$data = $cart->billing_address;			
		}
		
		$data = $this->getSTAddress($shipto_id);
		
		if($data === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		$cart->setSTAddress($data);
		
		if(!$cart->recalculate())
		{
			$this->setError($cart->getError());
			return false;
		}	
		
		// Revalidate selected shipping method
		if($cart->cart_shipment_method_id > 0 && !$this->validateShippingMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetShippingMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_SHIPPING_METHOD_UNSET'));			
		}
		
		// Revalidate selected payment method
		if($cart->cart_payment_method_id > 0 && !$this->validatePaymentMethod($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
				return false;
			}			
			
			if(!$cart->unsetPaymentMethod())
			{
				$this->setError($cart->getError());
				return false;
			}
			
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QAZAP_CAR_MSG_PAYMENT_METHOD_UNSET'));			
		}			
		
		$this->setCart();
		return true;		
	}
	
	public function addCoupon($couponCode)
	{
		$model = QZApp::getModel('Coupon', array('ignore_request' => true));
		$cart = $this->getCart();
		$coupon = $model->getCoupon($couponCode, $cart);
		
		if($coupon === false)
		{
			$this->setError($model->getError());
			return false;
		}

		if(!$cart->setCoupon($coupon))
		{
			$this->setError($cart->getError());
			return false;
		}
		
		$this->setCart();
		
		return true;		
	}
	
	protected function reapplyCoupon(QZCart $cart, $couponCode, $setCart = false)
	{
		$model = QZApp::getModel('Coupon', array('ignore_request' => true));
		$coupon = $model->getCoupon($couponCode, $cart);
		
		if($coupon === false)
		{
			$this->setError($model->getError());
			return false;
		}

		if(!$cart->setCoupon($coupon))
		{
			$this->setError($cart->getError());
			return false;
		}
		
		if($setCart)
		{
			$this->setCart();
		}		
		
		return true;			
	}
	
	/**
	* Method to set Shipping Method in cart
	* 
	* @param	integer $shipping_method_id	Shipping Method ID
	* 
	* @return	boolean (true/false)
	* @since	1.0.0
	*/	
	public function selectShipping($shipping_method_id)
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
				->select('a.id, a.ordering, a.state, a.shipment_name, a.shipment_description, a.shipment_method, '.
									'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
				->from('#__qazap_shipment_methods AS a')
				->select('b.element AS plugin')				
				->leftjoin('#__extensions AS b ON a.shipment_method = b.extension_id')
				->where('a.state = 1')
				->where('b.enabled = 1')
				->where('b.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->where('a.id = '. (int) $shipping_method_id);	
		
		try 
		{
			$db->setQuery($sql);
			$method = $db->loadObject();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}		

		if(empty($method) || !is_object($method))
		{
			$this->setError(JText::_('COM_QAZAP_MSG_INVALID_SHIPPING_METHOD'));
			return false;
		}		

		// Get cart object
		$cart = $this->getCart();
		
		// Process method data and check for valid selection
		$tmp = new JRegistry;
		$tmp->loadString($method->params);
		$method->params = $tmp;

		$tmp = new JRegistry;
		$tmp->loadArray($cart->shipping_address);
		$shipping_address = $tmp;
		$user_country = $shipping_address->get('country', 0);	
		
		if(is_string($method->countries) && $method->countries)
		{			
			$method->countries = (array) json_decode($method->countries);
			$method->countries = array_map('intval', $method->countries);
			
			if(count($method->countries) && !in_array($user_country, $method->countries) && !in_array(0, $method->countries))
			{
				$this->setError(JText::_('COM_QAZAP_MSG_INVALID_SHIPPING_METHOD'));
				return false;
			}
		}
		
		// Overload an empty display property to method object
		$method->total_price = 0;
		$method->html = false;
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapshipment');
				
		$result = $dispatcher->trigger('onSelectShipmentMethod', array(&$method, $cart));

		if (in_array(false, $result, true))
		{
			$this->setError($cart->getError());
			return false;
		}
		
		if(!$cart->setShippingMethod($method))
		{
			$this->setError($cart->getError());
			return false;			
		}		
		
		$this->setCart();
		return true;
	}
	
	/**
	* Method to set Payment Method in cart
	* 
	* @param	integer $payment_method_id	Payment Method ID
	* 
	* @return	boolean (true/false)
	* @since	1.0.0
	*/	
	public function selectPayment($payment_method_id)
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
				->select('a.id, a.ordering, a.state, a.payment_name, a.payment_description, a.payment_method, '.
									'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
				->from('#__qazap_payment_methods AS a')
				->select('b.element AS plugin')				
				->leftjoin('#__extensions AS b ON a.payment_method = b.extension_id')
				->where('a.state = 1')
				->where('b.enabled = 1')
				->where('b.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->where('a.id = '. (int) $payment_method_id);	
		
		try 
		{
			$db->setQuery($sql);
			$method = $db->loadObject();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}		

		if(empty($method) || !is_object($method))
		{
			$this->setError(JText::_('COM_QAZAP_MSG_INVALID_PAYMENT_METHOD'));
			return false;
		}		

		// Get cart object
		$cart = $this->getCart();
		
		// Process method data and check for valid selection
		$tmp = new JRegistry;
		$tmp->loadString($method->params);
		$method->params = $tmp;
				
		$tmp = new JRegistry;
		$tmp->loadArray($cart->shipping_address);
		$shipping_address = $tmp;
		$user_country = $shipping_address->get('country', 0);	
		
		if(is_string($method->countries) && $method->countries)
		{			
			$method->countries = (array) json_decode($method->countries);
			$method->countries = array_map('intval', $method->countries);
			
			if(count($method->countries) && !in_array($user_country, $method->countries) && !in_array(0, $method->countries))
			{
				$this->setError(JText::_('COM_QAZAP_MSG_INVALID_PAYMENT_METHOD'));
				return false;
			}
		}
		
		// Overload an empty display property to method object
		$method->total_price = 0;
		$method->html = false;
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
				
		$result = $dispatcher->trigger('onSelectPaymentMethod', array(&$method, $cart));

		if (in_array(false, $result, true))
		{
			$this->setError($cart->getError());
			return false;
		}
		
		if(!$cart->setPaymentMethod($method))
		{
			$this->setError($cart->getError());
			return false;			
		}		
		
		$this->setCart();
		return true;
	}	

	/**
	* Method to get a Ship To Address by ID
	* 
	* @param	integer $id	Address Userinfo table ID
	* 
	* @return	mixed	array of address data or false in case of failure
	* @since	1.0.0
	*/	
	protected function getSTAddress($id) 
	{
		if(!isset($this->_stAddress[$id]))
		{			
			$profileModel = QZApp::getModel('Profile', array('ignore_request' => true), $admin = false);
			$fields = $profileModel->getUserFields('st');
			
			if($fields === false)
			{
				$this->setError($profileModel->getError());
				return false;
			}
			
			// Add the table prefix to fields
			$fields = array_map(function($val) { return 'a.'.$val;}, $fields);
			
			$db = $this->getDbo();
			$sql = $db->getQuery(true)
				 ->select($fields)
				 ->select(array('c.country_name','s.state_name'))
				 ->from('#__qazap_userinfos AS a')
				 ->leftjoin('#__qazap_countries AS c ON a.country = c.id')
				 ->leftjoin('#__qazap_states AS s ON a.states_territory = s.id')			 
				 ->where('a.address_type ='.$db->quote('st'))
				 ->where('a.state = 1')
				 ->where('a.id = '.(int) $id)
				 ->group($fields);
				 
			try {
				$db->setQuery($sql);
				$result = $db->loadAssoc();								
			} catch (Exception $e) {
				$this->setError($e->getMessage());
				return false;
			}
			
			$this->_stAddress[$id] = $result;			
		}

		return $this->_stAddress[$id];	
	}	
	
	/**
	* Method to get all available shipping methods
	* 
	* @return	Mixed	array of methods or false
	* @since	1.0.0
	*/	
	public function getShippingMethods($cart = null)
	{
		$cart = $cart ? $cart : $this->getCart();
		
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
				->select('a.id, a.ordering, a.state, a.shipment_name, a.shipment_description, a.shipment_method, '.
									'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
				->from('#__qazap_shipment_methods AS a')
				->select('b.element AS plugin')
				->join('INNER', '#__extensions AS b ON a.shipment_method = b.extension_id')
				->where('a.state = 1')
				->where('b.enabled = 1')
				->where('b.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->order('a.ordering ASC')
				->group('a.id, a.ordering, a.state, a.shipment_name, a.shipment_description, a.shipment_method, '.
									'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params');	
		
		try 
		{
			$db->setQuery($sql);
			$methods = $db->loadObjectList('id');
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}		

		if(empty($methods))
		{
			return false;
		}
		
		if(!$methods = $this->getVendorAcceptedShippingMethods($methods, $cart))
		{
			return false;
		}

		// Check for User's shipping country
		$tmp = new JRegistry;
		$tmp->loadArray($cart->shipping_address);
		$shipping_address = $tmp;
		$user_country = $shipping_address->get('country', 0);
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapshipment');
		
		// Layout file path to display shipping methods
		$layoutPath = QZPATH_LAYOUT . DS . 'cart';

		foreach($methods as $key => &$method)
		{
			if(is_string($method->countries) && $method->countries)
			{						
				$method->countries = (array) json_decode($method->countries);
				$method->countries = array_map('intval', $method->countries);
				
				if(count($method->countries) && !in_array($user_country, $method->countries) && !in_array(0, $method->countries))
				{
					unset($methods[$key]);
					continue;
				}
			}
			
			if(is_string($method->user_group) && $method->user_group)
			{						
				$method->user_group = (array) json_decode($method->user_group);
				$method->user_group = array_map('intval', $method->user_group);
				$intersects = array_intersect($method->user_group, $user->getAuthorisedGroups());
				$validGroup = count($intersects) ? 1 : 0;
				
				if(!$validGroup)
				{
					unset($methods[$key]);
					continue;
				}
			}						
			
			$tmp = new JRegistry;
			$tmp->loadString($method->params);
			$method->params = $tmp;
			
			// Overload empty Display property to stdclass object
			$method->total_price = 0;
			$method->display = false;
			$method->additional_html = false;
			
			$result = $dispatcher->trigger('onDisplayShipmentMethods', array(&$method, $cart));

			if (in_array(false, $result, true))
			{
				$this->setError($cart->getError());
				return false;
			}
			
			if(!$method->display)
			{
				unset($methods[$key]);
				continue;
			}
			
			// Overload selection
			$method->selected = ($cart->cart_shipment_method_id == $method->id) ? 1 : 0;
			// Overload static field id and field name
			$method->field_id = 'qzform_shipping_method';
			$method->field_name = 'qzform[shipping_method_id]';
			
			// Arrange to display the method with layout file.
			$layout = new JLayoutFile('shipping_method', $layoutPath);
			$method->html = $layout->render($method);
		}

		if(empty($methods))
		{
			return false;
		}

		return $methods;
	}	
	
	/**
	* Method to check for vendor accepted shipping methods
	* 
	* @param array	$available_methods	Array of list of shipping methods
	* @param object	$cart								QZCart object
	* 
	* @return mixed array/false	Array of accepted shipping methods or false in case failue/no match.
	* @since	1.0.0
	*/	
	protected function getVendorAcceptedShippingMethods($available_methods, $cart = null)
	{
		$cart = $cart ? $cart : $this->getCart();

		if(count($cart->vendor_carts))
		{
			$vendor_ids = array_map('intval', array_keys($cart->vendor_carts));

			$db = $this->getDbo();
			$query = $db->getQuery(true)
								->select('id, shipment_methods')
								->from('#__qazap_vendor')
								->where('id IN (' . implode(',', $vendor_ids) . ')')
								->group('id, shipment_methods');
			try 
			{
				$db->setQuery($query);
				$vendors = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}		

			$accepted_methods = array_keys($available_methods);
			
			foreach($vendors as $vendor)
			{
				if(is_string($vendor->shipment_methods) && $vendor->shipment_methods)
				{						
					$vendor->shipment_methods = json_decode($vendor->shipment_methods);
				}

				$vendor->shipment_methods = (array) $vendor->shipment_methods;
				$vendor->shipment_methods = array_map('intval', $vendor->shipment_methods);
				
				if(!in_array(0, $vendor->shipment_methods))
				{
					$accepted_methods = array_intersect($accepted_methods, $vendor->shipment_methods);
					$array_diff = array_diff_key($available_methods, array_flip($vendor->shipment_methods));
					
					if(count($array_diff))
					{
						$this->_vendorUnacceptedShippings[$vendor->id] =  $array_diff; 
					}					
				}				
			}
			
			if(empty($accepted_methods))
			{
				return false;
			}					

			$accepted_methods = array_filter(array_unique($accepted_methods));			
			$accepted_methods = array_intersect_key($available_methods, array_flip($accepted_methods));

			return $accepted_methods;
		}

		return false;
	}

	/**
	* Method to validate selected shipping method
	* 
	* @param object QZCart object of present cart
	* 
	* @return boolean
	* @since	1.0.0
	*/		
	protected function validateShippingMethod(QZCart $cart)
	{
		if(!$methods = $this->getShippingMethods($cart))
		{
			if($this->getError())
			{
				$this->setError($this->getError());
			}
			return false;
		}

		if(!array_key_exists($cart->cart_shipment_method_id, $methods))
		{
			return false;
		}
		
		return true;			
	}

	/**
	* Method to get all available payment methods
	* 
	* @return	mixed	array of methods or false
	* @since	1.0.0
	*/	
	public function getPaymentMethods($cart = null)
	{
		$cart = !empty($cart) ? $cart : $this->getCart();
		
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
				->select('a.id, a.ordering, a.state, a.payment_name, a.payment_description, a.payment_method, '.
									'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
				->from('#__qazap_payment_methods AS a')
				->select('b.element AS plugin')				
				->leftjoin('#__extensions AS b ON a.payment_method = b.extension_id')
				->where('a.state = 1')
				->where('b.enabled = 1')
				->where('b.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->order('a.ordering ASC')
				->group('a.id, a.ordering, a.state, a.payment_name, a.payment_description, a.payment_method, '.
									'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params');	
		
		try 
		{
			$db->setQuery($sql);
			$methods = $db->loadObjectList('id');
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}		

		if(empty($methods))
		{
			return false;
		}		
		
		// Check for User's shipping country
		$tmp = new JRegistry;
		$tmp->loadArray($cart->shipping_address);
		$shipping_address = $tmp;
		$user_country = $shipping_address->get('country', 0);
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
		
		// Layout file path to display shipping methods
		$layoutPath = QZPATH_LAYOUT . DS . 'cart';

		foreach($methods as $key => &$method)
		{
			if(is_string($method->countries) && $method->countries)
			{						
				$method->countries = (array) json_decode($method->countries);
				$method->countries = array_map('intval', $method->countries);
				
				if(count($method->countries) && !in_array($user_country, $method->countries) && !in_array(0, $method->countries))
				{
					unset($methods[$key]);
					continue;
				}
			}
			
			if(is_string($method->user_group) && $method->user_group)
			{						
				$method->user_group = (array) json_decode($method->user_group);
				$method->user_group = array_map('intval', $method->user_group);
				$intersects = array_intersect($method->user_group, $user->getAuthorisedGroups());
				$validGroup = count($intersects) ? 1 : 0;
				
				if(!$validGroup)
				{
					unset($methods[$key]);
					continue;
				}
			}						
			
			$tmp = new JRegistry;
			$tmp->loadString($method->params);
			$method->params = $tmp;
			
			// Overload empty Display property to stdclass object
			$method->total_price = 0;
			$method->display = false;
			$method->additional_html = false;
			
			$result = $dispatcher->trigger('onDisplayPaymentMethods', array(&$method, $cart));

			if (in_array(false, $result, true))
			{
				$this->setError($cart->getError());
				return false;
			}

			if(!$method->display)
			{
				unset($methods[$key]);
				continue;
			}
			
			// Overload selection
			$method->selected = ($cart->cart_payment_method_id == $method->id) ? 1 : 0;
			// Overload static field id and field name
			$method->field_id = 'qzform_payment_method';
			$method->field_name = 'qzform[payment_method_id]';
			
			// Arrange to display the method with layout file.
			$layout = new JLayoutFile('payment_method', $layoutPath);
			$method->html = $layout->render($method);
		}

		if(empty($methods))
		{
			return false;
		}

		return $methods;
	}	

	/**
	* Method to validate selected payment method
	* 
	* @param object QZCart object
	* 
	* @return boolean
	* @since	1.0.0
	*/		
	protected function validatePaymentMethod(QZCart $cart)
	{
		if(!$methods = $this->getPaymentMethods($cart))
		{			
			if($this->getError())
			{
				$this->setError($this->getError());
			}
			return false;
		}

		if(!array_key_exists($cart->cart_payment_method_id, $methods))
		{
			return false;
		}
		
		return true;			
	}
	
	public function setConfirmFormData($data = array())
	{
		$cart = $this->getCart();
		
		if(isset($data['customer_note']) && !$cart->setCustomerNote($data['customer_note']))
		{
			$this->setError($cart->getError());
			return false;
		}
		
		if(isset($data['tos_accept']) && !$cart->setTOSAccept($data['tos_accept']))
		{
			$this->setError($cart->getError());
			return false;
		}
		
		$this->setCart();		
		return true;		
	}
	
	public function placeOrder()
	{
		$cart = $this->getCart();

		if(!$cart->validate())
		{
			$this->setError($cart->getError());
			return false;
		}
		
		$cart->setLanguage();
		$ordergroup = $this->saveOrderGroup($cart);
		
		if($ordergroup === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		$this->_ordergroup = $ordergroup;
		
		$this->renewCart();
		
		return $this->_ordergroup;
	}
	
	public function setPaymentForm($html)
	{
		$this->_paymentForm = $html;
	}
	
	public function getPaymentForm()
	{
		if($this->_paymentForm === null)
		{
			return false;
		}
		
		return $this->_paymentForm;
	}
	
	public function getOrderConfirmation()
	{
		if(empty($this->_ordergroup))
		{
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_NO_ORDER_PLACED'));
			return false;
		}
		
		$cart = $this->getCart();
		
		if(!$cart || !count($cart->vendor_carts))
		{
			$this->setError(JText::_('COM_QAZAP_CART_ERROR_CART_IS_EMPTY'));
			return false;			
		}

		if(!$this->validateOrdergroupWithCart($cart, $this->_ordergroup))
		{
			$this->setError($this->getError());
			return false;
		}
		
		$dispatcher = JEventDispatcher::getInstance();

		JPluginHelper::importPlugin('qazapcartattributes');
		JPluginHelper::importPlugin('qazapcustomfields');
		JPluginHelper::importPlugin('qazapshipment');
		JPluginHelper::importPlugin('qazappayment');		
	
		$results = $dispatcher->trigger('onGetOrderConfirmation', array($this->_ordergroup, $this));
		
		if(in_array(false, $results, true))
		{
			$this->setError($this->getError());
			return false;
		}		
		elseif(in_array(true, $results, true))
		{
			if($this->_paymentForm === null)
			{
				// Plugin will return true without setting a payment form only when order is confirmed or is processed so clear the cart in session
				$this->clearCart();				
			}
			
			return true;
		}
		
		// If not true or false that means no payment method applied
		$this->setError('onGetOrderConfirmation::Error');
		return false;
	}
	
	
	protected function validateOrdergroupWithCart(QZCart $cart, QZCart $ordergroup)
	{
		$fields = array(
									'cart_payment_method_id',
									'cart_total',
									'user_id',
									'ip_address'
									);
									
		$config = QZApp::getConfig();
		
		if(!$config->get('intangible', 0) && !$config->get('downloadable', 0))
		{
			$fields[] = 'cart_shipment_method_id';
		}
									
		foreach($fields as $field)
		{
			if(!isset($cart->$field) || !isset($ordergroup->$field))
			{
				$this->setError(JText::_('COM_QAZAP_CART_ERROR_' . strtoupper($field) . '_MISSING_DATA') . '::validateOrdergroupWithCart()');
				return false;		
			}
			elseif($cart->$field != $ordergroup->$field)
			{
				$this->setError(JText::_('COM_QAZAP_CART_ERROR_' . strtoupper($field) . '_MISMATCH'));
				return false;					
			}
		}
		
		return true;
	}
	
	/**
	* Method to validate the cart and return the next checkout step layout
	* 
	* @return	string Cart view layout
	* @since	1.0.0
	*/
	public function getNextStep()
	{
		$user = JFactory::getUser();
		$config = QZApp::getConfig();
		$cart = $this->getCart();		
		$result = $cart->validate(true);

		if($result === false)
		{
			$this->setError($cart->getError());
			return false;
		}
		elseif($result === true)
		{
			return null;
		}		
		elseif($result == 'billing_address')
		{
			return 'edit_billto';
		}
		elseif($result == 'shipping_address')
		{
			if($user->guest && $config->get('guest_checkout', 1))
			{
				return 'edit_shipto';
			}
			
			return 'select_shipto';
		}
		elseif($result == 'shipment_method')
		{
			return 'select_shipping';
		}
		elseif($result == 'payment_method')
		{
			return 'select_payment';
		}	
		
		return null;	
	}
	
	/**
	* Method to renew the cart in session
	* 
	* @return void
	*/
	public function renewCart()
	{
		// Create a new ordergroup ID for the left cart in session
		$this->_cart->createOrderGroup(true);
		$this->_cart->createAccessKey(true);
		$this->setCart();		
	}
	
	/**
	* Method to set Confirmed Cart i.e. the placed order to confirmed cart session
	* 
	* @param object $ordergroup QZCart object of ordergroup
	* 
	* @return void
	* @since	1.0.0
	*/
	public function setConfirmedCart($ordergroup = null)
	{
		$ordergroup = $ordergroup ? $ordergroup : $this->_ordergroup;
		
		if(!($ordergroup instanceof QZCart))
		{
			$ordergroup = new QZCart($ordergroup);
		}

		if(empty($ordergroup->user_id))
		{
			// If guest user, validate the new ordergroup for this user 
			// so that they can access the order from confirmation page order details link.
			$orderdetailsModel = QZApp::getModel('orderdetails', array('ignore_request' => true), false);
			$result = $orderdetailsModel->add($ordergroup->ordergroup_id);
		}
		
		if($this->_compress)
		{
			$data = base64_encode(gzcompress(serialize($ordergroup)));
		}
		else
		{
			$data = serialize($ordergroup);
		}		
		
		$session = JFactory::getSession();
		$session->set('QazapConfirmedCart', $data, 'qazap');			
	}
	
	/**
	* Method to get the confirmed cart from session
	* 
	* @return mixed (object/boolean) QZCart object or false
	* @since	1.0.0
	*/	
	public function getConfirmedCart($clearSession = true)
	{
		if($this->_confirmedCart === null)
		{
			$session = JFactory::getSession();
			$confirmedCart = $session->get('QazapConfirmedCart', 0, 'qazap');
			
			if($confirmedCart)
			{
				if($this->_compress)
				{	
					$this->_confirmedCart = unserialize(gzuncompress(base64_decode($confirmedCart))); 
				}
				else
				{
					$this->_confirmedCart = unserialize($confirmedCart);
				}	
				
				if($clearSession)
				{
					$session->clear('QazapConfirmedCart', 'qazap');
				}							
			}
			else
			{
				$this->_confirmedCart = false;
			}			
		}		
		
		return $this->_confirmedCart;
	}
	
	public function getTOS()
	{
		if($this->_tos === null)
		{
			$model = QZApp::getModel('Tos', array('ignore_request' => true), $admin = false);
			$model->dontReturnError();
			$tos = $model->getItem();
			
			if($tos === false)
			{
				$this->setError($model->getError());
				return false;
			}
		
			$this->_tos = $tos;
		}
		
		return $this->_tos;
	}
	
	public function getRegistrationForm()
	{
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_users/models/');
		$model = JModelLegacy::getInstance('registration', 'UsersModel', array('igonore_request' => true));
		JFactory::getLanguage()->load('com_users', JPATH_SITE);		
		JForm::addFormPath(JPATH_SITE . '/components/com_users/models/forms');
				
		return $model->getForm();
	}


	public function hasVarients($product_id)
	{
		if(!isset($this->_hasVarients[$product_id]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
									->select('COUNT(d.extension_id) AS attributes, m.membership')
									->from('#__qazap_products AS a')
									->join('LEFT', '#__qazap_cartattributes AS b ON b.product_id = a.product_id')
									->join('LEFT', '#__qazap_cartattributestype AS c ON c.id = b.typeid AND c.state = 1 AND c.hidden = 0')
									->join('LEFT', '#__extensions AS d ON d.extension_id = c.type AND d.enabled = 1');
									
			$subQuery = '(SELECT p.product_id, p.membership FROM #__qazap_products AS p GROUP BY p.product_id, p.membership)';
			
			$query->join('LEFT', $subQuery . ' AS m ON m.product_id = a.product_id AND (m.membership != ' . $db->quote("") . ' AND m.membership != ' . $db->quote("[]") . ')')
						->where('a.product_id = ' . (int) $product_id);
			
			try
			{
				$db->setQuery($query);
				$result = $db->loadObject();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}

			if(!empty($result))
			{
				if(!empty($result->attributes) || !empty($result->product_id))
				{
					return true;
				}
			}
			
			return null;
		}
	}
	
	public function clearCartBySessionID($session_id) 
	{
		$cfg = JFactory::getConfig ();
		$handler = $cfg->get('session_handler', 'none');
		$name = Japplication::getHash ('site');
		$storage = JSessionStorage::getInstance($handler, array('name'=>$name));
		$storage->register ();
		
		// Reads directly the session from the storage
		$session = $storage->read($session_id);

		if(empty($session)) 
		{
			return;
		}
		
		$session = $this->decodeSession($session);

		$namespace = '__qazap';
		$cart_name = 'QazapCart';
		
		if (array_key_exists($namespace, $session)) 
		{ 
			$qazap_session = &$session[$namespace];
			
			if (array_key_exists($cart_name, $qazap_session)) 
			{ 
				// Cart session is there. We will unset it. 
				// To Do: Check for no change in cart.
				unset($qazap_session[$cart_name]);
				$session = $this->encodeSession($session);
				if(!$storage->write($session_id, $session))
				{
					return;
				}
			}
		}
	}


	protected function decodeSession($session_data) 
	{
		$decoded_session = array();
		$offset = 0;
		while ($offset < strlen ($session_data)) 
		{
			if (!strstr (substr ($session_data, $offset), '|')) 
			{
				return array();
			}
			$pos = strpos ($session_data, '|', $offset);
			$num = $pos - $offset;
			$varname = substr ($session_data, $offset, $num);
			$offset += $num + 1;
			$data = unserialize (substr ($session_data, $offset));
			$decoded_session[$varname] = $data;
			$offset += strlen (serialize ($data));
		}
		return $decoded_session;
	}


	protected function encodeSession($session_data_array) 
	{
		$encoded_session = '';
		foreach ($session_data_array as $key => $session_data) 
		{
			$encoded_session .= $key . '|' . serialize ($session_data);
		}
		return $encoded_session;
	}


	
}
