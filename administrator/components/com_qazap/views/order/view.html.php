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
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
* View to edit
*/
class QazapViewOrder extends JViewLegacy
{
	protected $state;
	protected $params;
	protected $ordergroup;
	protected $form;	
	protected $username;
	protected $history;
	protected $paymentInfo;
	protected $model;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state				= $this->get('State');
		$this->params				= $this->state->get('params');
		$this->ordergroup		= $this->get('OrderGroupByID');
		$this->form					= $this->get('Form');
		$this->history			= $this->get('OrderHistory');
		$this->paymentInfo	= $this->get('OnAdminOrderDisplay');
		$this->model				= $this->getModel();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
		throw new Exception(implode("\n", $errors));
		}

		$this->_prepareUserName();
		$this->addToolbar();
		parent::display($tpl);
	}


	protected function _prepareUserName()
	{
		$guest = $this->ordergroup->user_id ? false : true;
		$name = '';

		if(isset($this->ordergroup->billing_address['first_name']))
		{
			$name .= trim($this->ordergroup->billing_address['first_name']);
		}
		if(isset($this->ordergroup->billing_address['middle_name']))
		{
			$this->ordergroup->billing_address['middle_name'] = trim($this->ordergroup->billing_address['middle_name']);
			if(!empty($this->ordergroup->billing_address['middle_name']))
			{
				$name .= ' ' . $this->ordergroup->billing_address['middle_name'];
			}			
		}
		if(isset($this->ordergroup->billing_address['last_name']) && $this->ordergroup->billing_address['last_name'])
		{
			$name .= ' ' . trim($this->ordergroup->billing_address['last_name']);
		}

		if(empty($name) && !$guest)
		{
			$user = JFactory::getUser($this->ordergroup->user_id);
			$name = $user->get('name');
		}

		if(!$guest)
		{
			$url = JRoute::_('index.php?option=com_users&task=user.edit&id=' . $this->ordergroup->user_id);
			$display = JHTML::link($url, $name, array('target'=>'_blank', 'title'=>$name));			
		}
		else
		{
			$display  = $name;
			$display .= ' (' . JText::_('COM_QAZAP_GUEST') . ')';
		}

		$this->username = $display;
	}
	/**
	* Add the page title and toolbar.
	*/
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();

		$canDo		= QazapHelper::getActions();

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' . JText::sprintf('COM_QAZAP_ORDERGROUP_TITLE', $this->ordergroup->ordergroup_number), ' qzicon-paste2');

		JToolBarHelper::cancel('order.cancel', 'JTOOLBAR_CLOSE');
	}

	protected function renderOrderStatusUpdateForm($order)
	{
		$layout = new JLayoutFile('statusupdate', $basePath = QZPATH_LAYOUT_ADMIN . DS . 'order');
		$form = clone $this->form;
		$form->setValue('order_status', null, $order->order_status);
		$form->setFieldAttribute('order_status', 'label', 'COM_QAZAP_ORDER_STATUS', null);
		$form->setFieldAttribute('order_status', 'description', '', null);
		return $layout->render(array('form' => $this->form, 'order' => $order));		
	}

	protected function renderItemStatusField($item_status, $readonly = false)
	{
		$form = clone $this->form;
		$form->setValue('order_status', null, $item_status);		
		if($readonly)
		{
			$form->setFieldAttribute('order_status', 'readonly', 'true', null);
		}
		else
		{
			$form->setFieldAttribute('order_status', 'readonly', 'false', null);
		}
		return $form->getInput('order_status');
	}
}
