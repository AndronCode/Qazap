<?php
/**
 * paymentplugin.php
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
defined('_JEXEC') or die();


abstract class QZPaymentPlugin extends QZPlugin
{	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}

	/**
	* This event is called by cart model when user confirms as order
	* 
	* @param	object	$ordergroup		QZCart object of present order for which the order confirmation is requested
	* @param	object	$carModel			QazapModelCart object of cart model
	* 
	* @return	boolean	Return false in case of any error. 
	* @note		Plugin can redirect to some other pages if needed. Eg. Payment gateways for payment processing
	*/
	public function onGetOrderConfirmation(QZCart $ordergroup, QazapModelCart $cartModel)
	{
		return;
	}	
	
	public function onOrderDisplayPaymentMethod(&$method)
	{
		return;
	}		
	
	/**
	* Method to get the plugin name by method id
	* 
	* @param integer $method_id
	* 
	* @return mixed	(string/false) Plugin name or false
	*/	
	protected function getName($method_id)
	{
		if(!$method_id)
		{
			$this->setError(JText::_('COM_QAZAP_PLUGIN_ERROR_INVALID_PAYMENT_METHOD_id'));
			return false;
		}
		
		if(!$method = $this->getMethod($method_id, 'payment'))
		{
			$this->setError($this->getError());
			return false;
		}
		
		return $method->plugin;
	}
	
	/**
	* Method to get the method params by method id
	* 
	* @param integer $method_id
	* 
	* @return mixed	(object/false) JRegistry params object or false in case of failure
	*/	
	protected function getParams($method_id)
	{
		if(!$method_id)
		{
			$this->setError(JText::_('COM_QAZAP_PLUGIN_ERROR_INVALID_PAYMENT_METHOD_ID_TO_GET_PARAMS'));
			return false;
		}
		
		if(!$method = $this->getMethod($method_id, 'payment'))
		{
			$this->setError($this->getError());
			return false;
		}

		if ($method->params instanceof JRegistry)
		{
			return $method->params;
		}
		else
		{
			$tmp = new JRegistry;
			$tmp->loadString($method->params);
			return $tmp;
		}
	}	

	/**
	* Method to calculate shipping method price
	* 
	* @param	object $method stdClass object of method
	* 
	* @return	void
	* @since	1.0
	*/	
	protected function calculatePrice(&$method)
	{
		$tax = 0;
		
		if($method->tax)
		{
			// If percent
			if($method->tax_calculation == 'p')
			{
				$tax = ($method->price * $method->tax) / 100;
			}
			else
			{
				$tax = $method->tax;
			}
		}
		
		$method->tax = $tax;		
		$method->total_price = ($method->price + $method->tax);
	}

	protected function getSelectedMethodDisplay($method)
	{
		$layoutPath = QZPATH_LAYOUT . DS . 'cart';
		// Arrange to display the method with layout file.
		$layout = new JLayoutFile('selected_payment', $layoutPath);
		return $layout->render($method);		
	}

	
	protected final function getCurrencyCode($processing_currency = 'order', $type = 'code3letters')
	{
		if(!($this->ordergroup instanceof QZCart))
		{
			$this->logDebug('ordergroup not defined.', 'getCurrencyCode ERROR');
			return false;
		}
		
		$processing_currency = strtolower($processing_currency);
		
		if(!in_array($processing_currency, array('user', 'order')))
		{
			$processing_currency = 'order';
		}	
		
		$processing_currency .= '_currency';
						
		if(!$currency = $this->getCurrency($this->ordergroup->$processing_currency))
		{
			return false;
		}
		
		if(is_object($currency) && property_exists($currency, $type))
		{
			return $currency->$type;
		}
		
		return false;
	}
	
	protected final function getValueInCurrency($value, $processing_currency = 'order')
	{
		if(!($this->ordergroup instanceof QZCart))
		{
			$this->logDebug('ordergroup not defined.', 'getValueInCurrency ERROR');
			return false;
		}
		
		$processing_currency = strtolower($processing_currency);
		
		if(!in_array($processing_currency, array('user', 'order')))
		{
			$processing_currency = 'order';
		}		
		
		if($processing_currency == 'user')
		{
			$value = ($value * $this->ordergroup->currency_exchange_rate);
		}
		
		$processing_currency .= '_currency';
		
		if(!$currency = $this->getCurrency($this->ordergroup->$processing_currency))
		{
			return false;
		}
		
		$value = round($value, $currency->decimals);
		
		return $value;
	}
	
	protected final function getAmountInOrderCurrency($value, $value_currency = 'order')
	{
		$value_currency = strtolower($value_currency);
		
		if(!in_array($value_currency, array('user', 'order')))
		{
			$value_currency = 'order';
		}
		
		if($value_currency == 'order')
		{
			return $value;
		}
		
		if(!($this->ordergroup instanceof QZCart))
		{
			$this->logDebug('ordergroup not defined.', 'getAmountInOrderCurrency ERROR');
			return false;
		}		
		
		$value = ($value / $this->ordergroup->currency_exchange_rate);
		
		if(!$currency = $this->getCurrency($this->ordergroup->order_currency))
		{
			return false;
		}
		
		$value = round($value, $currency->decimals);		
		
		return $value;
	}
	
	
	protected final function getBuyerEmail()
	{
		if($this->ordergroup && $this->address)
		{
			$user_id = $this->ordergroup->user_id;
			
			if($user_id == 'guest')
			{
				$email = $this->ordergroup->billing_address['email'];
			}
			else
			{
				$user = JFactory::getUser($user_id);
				$email = $user->get('email');
			}
		}
		else
		{
			$user = JFactory::getUser();
			$email = $user->get('email');
		}
		
		return $email;
	}
	
	
	/**
	* Logs the received IPN information to file
	*
	* @param array $data
	* @param bool $isValid
	*/
	protected final function logIPN($data, $isValid = true)
	{
		$config = JFactory::getConfig();
		$logpath = $config->get('log_path');

		$logFilenameBase = $logpath.'/'.$this->_type.'_'.$this->_name.'_ipn';

		$logFile = $logFilenameBase.'.php';
		JLoader::import('joomla.filesystem.file');
		if(!JFile::exists($logFile)) 
		{
			$dummy = "<?php die(); ?>\n";
			JFile::write($logFile, $dummy);
		} 
		else 
		{
			if(@filesize($logFile) > 1048756) 
			{
				$altLog = $logFilenameBase.'-1.php';
				if(JFile::exists($altLog)) 
				{
					JFile::delete($altLog);
				}
				JFile::copy($logFile, $altLog);
				JFile::delete($logFile);
				$dummy = "<?php die(); ?>\n";
				JFile::write($logFile, $dummy);
			}
		}
		$logData = JFile::read($logFile);
		if($logData === false) $logData = '';
		$logData .= "\n" . str_repeat('-', 80);
		$logData .= $isValid ? 'VALID '.$this->_name.' IPN' : 'INVALID '.$this->_name.' IPN *** FRAUD ATTEMPT OR INVALID NOTIFICATION ***';
		$logData .= "\nDate/time : ".gmdate('Y-m-d H:i:s')." GMT\n\n";
		foreach($data as $key => $value) 
		{
			$logData .= '  ' . str_pad($key, 30, ' ') . $value . "\n";
		}
		$logData .= "\n";
		JFile::write($logFile, $logData);
	}

}