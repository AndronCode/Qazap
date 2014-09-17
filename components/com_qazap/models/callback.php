<?php
/**
 * callback.php
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
* Methods supporting a list of Qazap records.
*/
class QazapModelCallback extends JModelLegacy 
{
	protected $_paymentMethodID = null;
	
	protected $_paymentMethods = array();
	
	/**
	* Constructor.
	*
	* @param    array    An optional associative array of configuration settings.
	* @see        JModelList
	* @since    1.0.0
	*/
	public function __construct($config = array()) 
	{
		parent::__construct($config);
	}
	
	public function setPaymentMethodID($method_id)
	{
		$this->_paymentMethodID = $method_id;
	}
	
	public function paymentResponse()
	{
		$method = $this->getPaymentMethod();
		
		if($method === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		if($method === null)
		{
			$this->setError('Invalid payment method id passed');
			return false;
		}
		
		// Get request and post data
		$data = $this->getData();
		$ordergroup = null;
		$success = false;

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
				
		$results = $dispatcher->trigger('onRecieveResponse', array($method, $data, &$ordergroup, &$success));
		
		if(in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
			return false;
		}
		elseif(in_array(true, $results, true))
		{
			$return = array();
			$return['ordergroup'] = $ordergroup;
			$return['success'] = $success;
			
			return $return;
		}

		return null;		
	}	
	
	public function paymentCancel()
	{
		$method = $this->getPaymentMethod();
		
		if($method === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		if($method === null)
		{
			$this->setError('Invalid payment method id passed');
			return false;
		}
		
		// Get request and post data
		$data = $this->getData();

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
				
		$results = $dispatcher->trigger('onPaymentCancel', array($method, $data));
		
		if (in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
			return false;
		}

		return true;		
	}		
	
	public function notify()
	{
		$method = $this->getPaymentMethod();
		
		if($method === false)
		{
			$this->setError($this->getError());
			return false;
		}
		
		if($method === null)
		{
			$this->setError('Invalid payment method id passed');
			return false;
		}

		$data = $this->getData();

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazappayment');
				
		$results = $dispatcher->trigger('onRecieveNotification', array($method, $data));
		
		if(empty($results))
		{
			$this->setError('No plugin response received against this data.');
			return false;
		}
		
		if (in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
			return false;
		}

		return true;		
	}		
	
	protected function getData()
	{
		// We are using JRequest instead of JInput to retrieve raw data.
		$rawDataPost = JRequest::get('POST', 2);
		$rawDataGet = JRequest::get('GET', 2);
		
		return array_merge($rawDataGet, $rawDataPost);		
	}

	protected function getPaymentMethod()
	{
		if($this->_paymentMethodID === null)
		{
			$this->setError('Payment method id not set');
			return false;
		}
		
		if(!isset($this->_paymentMethods[$this->_paymentMethodID]))
		{
			$db = $this->getDbo();
			$sql = $db->getQuery(true)
					->select('a.id, a.ordering, a.state, a.payment_name, a.payment_description, a.payment_method, '.
										'a.countries, a.logo, a.price, a.tax, a.tax_calculation, a.user_group, a.params')
					->from('#__qazap_payment_methods AS a')
					->select('b.element AS plugin')				
					->join('INNER', '#__extensions AS b ON a.payment_method = b.extension_id')
					->where('a.state = 1')
					->where('b.enabled = 1')
					->where('a.id = '. (int) $this->_paymentMethodID);	
			
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
			
			if(empty($method))
			{
				$this->_paymentMethods[$this->_paymentMethodID] = null;
			}
			else
			{
				$tmp = new JRegistry;
				$tmp->loadString($method->params);
				$method->params = $tmp;

				if($method->countries && is_string($method->countries))
				{
					$method->countries = json_decode($method->countries);
				}
								
				if($method->user_group && is_string($method->user_group))
				{
					$method->user_group = json_decode($method->user_group);
				}				
						
				$this->_paymentMethods[$this->_paymentMethodID] = $method;
			}						
		}
		
		return $this->_paymentMethods[$this->_paymentMethodID];
	}

}
