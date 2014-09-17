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
class QazapViewOrders extends JViewLegacy
{
	protected $items;
	protected $orders;
	protected $pagination;
	protected $state;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state				= $this->get('State');
		$this->items				= $this->get('Items');
		$this->orders				= $this->get('Orders');
		$this->pagination			= $this->get('Pagination');
		$this->filterForm			= $this->get('FilterForm');
		$this->activeFilters		= $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}

		QazapHelper::addSubmenu('orders');

		$this->addToolbar();

		$this->sidebar = QZHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.0.0
	*/
	protected function addToolbar()
	{
		$user = JFactory::getUser();

		$state	= $this->get('State');
		$canDo	= ($user->authorise('core.create', 'com_qazap') && $user->authorise('core.edit', 'com_qazap') && $user->authorise('core.edit.state', 'com_qazap'));
		$bar = JToolBar::getInstance('toolbar');

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_ORDERS'), ' qzicon-paste2');

		if($canDo && isset($this->items[0])) 
		{
		JToolBarHelper::editList('order.edit', 'JTOOLBAR_EDIT');
		}

		//Show trash and delete for components that uses the state field
		if($canDo) 
		{
			JToolBarHelper::deleteList('', 'orders.delete', 'JTOOLBAR_DELETE');
			JToolBarHelper::divider();

			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');		    
		}

		if ($user->authorise('core.admin', 'com_qazap')) 
		{
			JToolBarHelper::preferences('com_qazap');
		}        
	}

	protected function getSortFields()
	{
		return array(
			'a.order_id' => JText::_('JGRID_HEADING_ID'),
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.created_by' => JText::_('COM_QAZAP_ORDERS_CREATED_BY'),
			'a.user_id' => JText::_('COM_QAZAP_ORDERS_USER_ID'),
			'a.order_number' => JText::_('COM_QAZAP_ORDERS_ORDER_NUMBER'),
			'a.payment_method_id' => JText::_('COM_QAZAP_ORDERS_PAYMENT_METHOD_ID'),
			'a.shipment_method_id' => JText::_('COM_QAZAP_ORDERS_SHIPMENT_METHOD_ID'),
			'a.order_status' => JText::_('COM_QAZAP_ORDERS_ORDER_STATUS'),
		);
	}
}
