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
class QazapViewTaxes extends JViewLegacy
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
		$this->pagination				= $this->get('Pagination');
		$this->filterForm				= $this->get('FilterForm');
		$this->activeFilters			= $this->get('ActiveFilters');		

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}

		QazapHelper::addSubmenu('taxes');

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

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_TAXES'), ' qzicon-calculate2');

		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('tax.add','JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit') && isset($this->items[0])) 
		{
			JToolBarHelper::editList('tax.edit','JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state')) 
		{
			if(isset($this->items[0]->state)) 
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('taxes.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('taxes.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			} 
			elseif(isset($this->items[0])) 
			{
				JToolBarHelper::deleteList('', 'taxes.delete','JTOOLBAR_DELETE');
			}

			JToolBarHelper::custom('taxes.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}

		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) 
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) 
			{
				JToolBarHelper::deleteList('', 'taxes.delete','JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			} 
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('taxes.trash','JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::preferences('com_qazap');
		}        
		//Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_qazap&view=taxes');

		$this->extra_sidebar = '';        

	}

	/**
	* Returns an array of fields the table can be sorted by
	*
	* @return  array  Array containing the field name to sort by as the key and display text as value
	*
	* @since   1.0.0
	*/
	protected function getSortFields()
	{
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.id' => JText::_('JGRID_HEADING_ID'),
			'a.state' => JText::_('JSTATUS'),
			'a.calculation_rule_name' => JText::_('COM_QAZAP_FORM_LBL_TAX_MATH_OPERATION'),
			'a.type_of_arithmatic_operation' => JText::_('COM_QAZAP_FORM_LBL_TAX_TYPE_OF_ARITHMATIC_OPERATION'),
			'a.value' => JText::_('COM_QAZAP_FORM_LBL_TAX_VALUE')
		);
	}

	protected function getOperation($key=NULL)
	{
		$key = (int) $key;

		$operations = array(
		1 => JText::_('COM_QAZAP_DISCOUNT_AFTER_TAX'),
		2 => JText::_('COM_QAZAP_DISCOUNT_BEFORE_TAX'),
		3 => JText::_('COM_QAZAP_TAX'),
		4 =>  JText::_('COM_QAZAP_ORDER_DISCOUNT_AFTER_TAX'),
		5 => JText::_('COM_QAZAP_ORDER_DISCOUNT_BEFORE_TAX'),
		6 => JText::_('COM_QAZAP_ORDER_TAX')
		);

		if(!$key || !array_key_exists($key, $operations))
		{
		return false;
		}

		return $operations[$key];			
	}
    
}
