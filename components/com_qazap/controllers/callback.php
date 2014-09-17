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

jimport('joomla.application.component.controller');
/**
 * Callback controller class.
 */
class QazapControllerCallback extends JControllerLegacy
{
	public function shipmentResponse()
	{

	}
	
	public function paymentResponse()
	{
		$model = $this->getModel();
		$model->setPaymentMethodID($this->input->getInt('paymentmethod_id'));	
		
		$result = $model->paymentResponse();
		$ordergroup = isset($result['ordergroup']) ? $result['ordergroup'] : null;
		$success = isset($result['success']) ? $result['success'] : null;
				
		if(empty($result) || empty($ordergroup))
		{
			if($model->getError())
			{
				$this->setMessage($model->getError(), 'error');
			}
						
			$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=cart', false));
			return;
		}

		$cartModel = $this->getModel('cart');
		$cartModel->clearCart();
		$cartModel->setConfirmedCart($ordergroup);
		$this->setMessage(JText::_('COM_QAZAP_CART_ORDER_PLACED'), 'success');
		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=cart&layout=confirmed', false));
	}
	
	public function paymentCancel()
	{	
		$model = $this->getModel();
		$model->setPaymentMethodID($this->input->getInt('paymentmethod_id'));	

		if(!$model->paymentCancel())
		{
			$this->setMessage($model->getError(), 'error');
		}
		else
		{
			$this->setMessage(JText::_('COM_QAZAP_CART_PAYMENT_CANCELLED'));
		}		

		$this->setRedirect(JRoute::_('index.php?option=com_qazap&view=cart', false));
		return true;
	}	
	
	public function notify()
	{		
		$model = $this->getModel();
		$model->setPaymentMethodID($this->input->getInt('paymentmethod_id'));		

		if(!$result = $model->notify())
		{
			echo 'FAILED:<br/>';
			echo JText::_($model->getError());
		}
		else
		{
			'OK';
		}					

		JFactory::getApplication()->close();
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
	public function getModel($name = 'Callback', $prefix = '', $config = array('ignore_request' => true))
	{
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models');
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}		
}