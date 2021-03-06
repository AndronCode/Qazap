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
class QazapViewPayment extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	protected $params;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->params	= $this->get('PaymentParams');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	* Add the page title and toolbar.
	*/
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->payment_id == 0);
		$vendorDetails = QZVendor::get($this->item->vendor);

		if (isset($this->item->checked_out)) 
		{
			$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} 
		else 
		{
			$checkedOut = false;
		}
		$canDo		= QazapHelper::getActions();

		if($isNew)
		{
			JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_PAYMENT_ADD'), ' qzicon-coin');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_TITLE_PAYMENTS') . '- ' .  $vendorDetails->shop_name, ' qzicon-coin');	
		}

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{
			JToolBarHelper::apply('payment.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('payment.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('payment.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) 
		{
			JToolBarHelper::custom('payment.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if (empty($this->item->id)) 
		{
			JToolBarHelper::cancel('payment.cancel', 'JTOOLBAR_CANCEL');
		}
		else 
		{
			JToolBarHelper::cancel('payment.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
