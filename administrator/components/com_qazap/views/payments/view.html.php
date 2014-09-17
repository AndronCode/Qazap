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
 * View class for a list of Qazap.
 */
class QazapViewPayments extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $paymentTotal;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->filterForm   = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->paymentTotal	= $this->get('PaymentTotal');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
		throw new Exception(implode("\n", $errors));
		}

		QazapHelper::addSubmenu('payments');

		$this->addToolbar();

		$this->sidebar = QZHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/qazap.php';

		$state	= $this->get('State');
		$canDo	= QazapHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_PAYMENTS'), ' qzicon-coin');

		//Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/payment';
		if (file_exists($formPath)) 
		{

			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::addNew('payment.add','JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0])) 
			{
				JToolBarHelper::editList('payment.edit','JTOOLBAR_EDIT');
			}

		}

		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) 
		{
			if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) 
			{
				JToolBarHelper::deleteList('', 'payments.delete','JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			} 
			else if ($canDo->get('core.edit.state')) 
			{
				JToolBarHelper::trash('payments.trash','JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::preferences('com_qazap');
		}

		//Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_qazap&view=payments');

		$this->extra_sidebar = '';

		JHtmlSidebar::addFilter(

			JText::_('JOPTION_SELECT_PUBLISHED'),

			'filter_published',

			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.published'), true)

		);
	}

	protected function getSortFields()
	{
		return array(
			'a.payment_id' => JText::_('COM_QAZAP_PAYMENTS_PAYMENT_ID'),
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.created_by' => JText::_('COM_QAZAP_PAYMENTS_CREATED_BY'),
			'a.vendor' => JText::_('COM_QAZAP_PAYMENTS_VENDOR'),
			'a.date' => JText::_('COM_QAZAP_PAYMENTS_DATE'),
			'a.total_confirmed_order' => JText::_('COM_QAZAP_PAYMENTS_TOTAL_CONFIRMED_ORDER'),
			'a.total_pending_order' => JText::_('COM_QAZAP_PAYMENTS_TOTAL_PENDING_ORDER'),
			'a.total_commission' => JText::_('COM_QAZAP_PAYMENTS_TOTAL_COMMISSION'),
			'a.last_payment_amount' => JText::_('COM_QAZAP_PAYMENTS_LAST_PAYMENT_AMOUNT'),
			'a.last_payment_date' => JText::_('COM_QAZAP_PAYMENTS_LAST_PAYMENT_DATE'),
			'a.total_paid_amount' => JText::_('COM_QAZAP_PAYMENTS_TOTAL_PAID_AMOUNT'),
			'a.total_balance' => JText::_('COM_QAZAP_PAYMENTS_TOTAL_BALANCE'),
			'a.payment_amount' => JText::_('COM_QAZAP_PAYMENTS_PAYMENT_AMOUNT'),
			'a.balance' => JText::_('COM_QAZAP_PAYMENTS_BALANCE'),
			'a.payment_method' => JText::_('COM_QAZAP_PAYMENTS_PAYMENT_METHOD'),
		);
	}
}
