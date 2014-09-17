<?php
/**
 * orderdetails.php
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
class QazapControllerOrderdetails extends QazapController
{

	public function validate()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$ordergroup_id = $this->input->getInt('ordergroup_id', 0);
		$access_key = $this->input->post->get('access_key', null, 'alnum');
		$email = $this->input->post->get('email', null, 'email');
		
		if(empty($ordergroup_id))
		{
			return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_ORDERGROUP_NOT_FOUND'));
		}
		
		if(empty($email) || !QZHelper::validateEmail($email))
		{
			$this->setMessage(JText::_('COM_QAZAP_ERROR_INVALID_EMAIL'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getOrderdetailsRoute($ordergroup_id)));
			return;
		}
		
		if(empty($access_key))
		{
			$this->setMessage(JText::_('COM_QAZAP_ERROR_INVALID_ACCESS_KEY'));
			$this->setRedirect(JRoute::_(QazapHelperRoute::getOrderdetailsRoute($ordergroup_id)));
			return;
		}
		
		$model = $this->getModel();
		
		$ordergroupDetails = $model->getItem($ordergroup_id);
		
		if(!$ordergroupDetails)
		{
			$this->setMessage($model->getError());
		}
		elseif($ordergroupDetails->billing_address['email'] != $email)
		{
			$this->setMessage(JText::_('COM_QAZAP_ORDERDETAILS_NO_MATCHING_EMAIL'));
		}		
		elseif($ordergroupDetails->access_key != $access_key)
		{
			$this->setMessage(JText::_('COM_QAZAP_ORDERDETAILS_NO_MATCHING_ACCESS_KEY'));
		}
		elseif(!$model->add($ordergroup_id))
		{
			$this->setMessage($model->getError());
		}

		$this->setRedirect(JRoute::_(QazapHelperRoute::getOrderdetailsRoute($ordergroup_id)));		
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
	public function getModel($name = 'orderdetails', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}	
}