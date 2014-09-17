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
class QazapViewCategories extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

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
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item)
		{
			$this->ordering[$item->parent_id][] = $item->category_id;
		}		
        
		QazapHelper::addSubmenu('categories');
        
		$this->addToolbar();
        
		$this->sidebar = QZHtmlSidebar	::render();
		parent::display($tpl);
	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.0.0
	*/
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/qazap.php';

		$state	= $this->get('State');
		$canDo	= QazapHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_CATEGORIES'), ' qzicon-tree4');

		//Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/category';
		if (file_exists($formPath)) 
		{
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::addNew('category.add','JTOOLBAR_NEW');
			}
			
			if ($canDo->get('core.edit') && isset($this->items[0])) 
			{
				JToolBarHelper::editList('category.edit','JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state')) 
		{
			if (isset($this->items[0]->published)) 
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('categories.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('categories.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			} 
			elseif (isset($this->items[0])) 
			{
				//If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->checked_out)) 
			{
				JToolBarHelper::custom('categories.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}
        
		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->published)) 
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) 
			{
		  		JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_EMPTY_TRASH');
		  		JToolBarHelper::divider();
			} 
			elseif ($canDo->get('core.edit.state')) 
			{
		  		JToolBarHelper::trash('categories.trash','JTOOLBAR_TRASH');
		  		JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin')) 
		{
			JToolbarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
			JToolBarHelper::preferences('com_qazap');
		}
        
        //Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_qazap&view=categories');
     
		$this->extra_sidebar = '';
        
		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)
		);

        
	}
    
	protected function getSortFields()
	{
		return array(
			'a.lft' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'b.title' => JText::_('JGLOBAL_TITLE'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'COUNT(product.product_id)' => JText::_('COM_QAZAP_CATEGORY_PRODUCT_COUNT'),
			'a.category_id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
