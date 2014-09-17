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
class QazapViewEmailtemplates extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state					= $this->get('State');
		$this->items					= $this->get('Items');
		$this->pagination			= $this->get('Pagination');
		$this->fieldTypes 		= $this->get('Fields');
		$this->filterForm   	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}
        
		QazapHelper::addSubmenu('emailtemplates');
        
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

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_EMAILS'), ' qzicon-envelop2');


		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('emailtemplate.add','JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit') && isset($this->items[0])) 
		{
			JToolBarHelper::editList('email.edit','JTOOLBAR_EDIT');
		}


		if ($canDo->get('core.edit.state')) 
		{
			if (isset($this->items[0]->state)) 
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('emailtemplates.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('emailtemplates.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			} 
			//If this component does not use state then show a direct delete button as we can not trash
			JToolBarHelper::deleteList('', 'emailtemplates.delete','JTOOLBAR_DELETE');

			if (isset($this->items[0]->checked_out)) 
			{
			JToolBarHelper::custom('emailtemplates.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::preferences('com_qazap');
		}		
        
		$this->extra_sidebar = '';       
	}
    
	protected function getSortFields()
	{
		return array(
			'a.id' => JText::_('JGRID_HEADING_ID'),
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.checked_out' => JText::_('COM_QAZAP_MAILINGS_CHECKED_OUT'),
			'a.checked_out_time' => JText::_('COM_QAZAP_MAILINGS_CHECKED_OUT_TIME'),
			'a.created_by' => JText::_('COM_QAZAP_MAILINGS_CREATED_BY'),
			'a.name' => JText::_('COM_QAZAP_MAILINGS_NAME'),
			'a.subject' => JText::_('COM_QAZAP_MAILINGS_SUBJECT'),
			'a.default' => JText::_('COM_QAZAP_MAILINGS_DEFAULT'),
			'a.purpose' => JText::_('COM_QAZAP_MAILINGS_PURPOSE'),
		);
	}

    
}
