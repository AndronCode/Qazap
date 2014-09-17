<?php
/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      VirtuePlanet Services LLP <info@virtueplanet.com> - http://www.virtueplanet.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Qazap.
 */
class QazapViewVendors extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $orderFields;

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

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}
        
		QazapHelper::addSubmenu('vendors');
        
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
		$state	= $this->get('State');
		$canDo	= QazapHelper::getActions();
		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_VENDORS'), ' qzicon-brain');

		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('vendor.add','JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit') && isset($this->items[0])) 
		{
			JToolBarHelper::editList('vendor.edit','JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('vendors.publish', 'publish.png', 'publish_f2.png','COM_QAZAP_ACIVATE', true);
			JToolBarHelper::custom('vendors.unpublish', 'unpublish.png', 'unpublish_f2.png', 'COM_QAZAP_BLOCK', true);
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'vendors.delete','JTOOLBAR_DELETE');
			JToolBarHelper::custom('vendors.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::preferences('com_qazap');
		}
        
		JHtmlSidebar::setAction('index.php?option=com_qazap&view=vendors');
        
		$this->extra_sidebar = '';      
	}
    
	protected function getSortFields()
	{
		$return = array(
			'a.id' => JText::_('JGRID_HEADING_ID'),
			'a.state' => JText::_('JSTATUS'),
			'a.created_by' => JText::_('COM_QAZAP_VENDORS_CREATED_BY'),
			'a.shop_name' => JText::_('COM_QAZAP_VENDORS_SHOP_NAME'),		
		);

		return $return;
	}    
}
