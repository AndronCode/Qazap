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
class QazapViewUserfields extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $fieldTypes;

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

		$this->fieldTypes = array(
			"text"=>JText::_('COM_QAZAP_FIELD_TYPE_TEXT'),
			"checkbox"=>JText::_('COM_QAZAP_FIELD_TYPE_CHECKBOX'),
			"calender"=>JText::_('COM_QAZAP_FIELD_TYPE_DATE'),
			"list"=>JText::_('COM_QAZAP_FIELD_TYPE_SELECT'),
			"list:multiple"=>JText::_('COM_QAZAP_FIELD_TYPE_SELECT_MULTI'),
			"email"=>JText::_('COM_QAZAP_FIELD_TYPE_EMAIL'),
			"editor"=>JText::_('COM_QAZAP_FIELD_TYPE_EDITOR'),
			"textarea"=>JText::_('COM_QAZAP_FIELD_TYPE_TEXTAREA'),
			"radio"=>JText::_('COM_QAZAP_FIELD_TYPE_RADIO'),
			"url"=>JText::_('COM_QAZAP_FIELD_TYPE_WEBADDRESS'),
			"qazapcountries"=>JText::_('COM_QAZAP_FIELD_TYPE_COUNTRY'),
			"qazapstates"=>JText::_('COM_QAZAP_FIELD_TYPE_STATE'),
			"fieldset"=>JText::_('COM_QAZAP_FIELD_TYPE_FIELDSET'),
			"media"=>JText::_('COM_QAZAP_FIELD_TYPE_MEDIA')
		);	

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}

		QazapHelper::addSubmenu('userfields');

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
		require_once JPATH_COMPONENT.'/helpers/qazap.php';

		$state	= $this->get('State');
		$canDo	= QazapHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_USERFIELDS'), ' qzicon-file');

		//Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/userfield';
		if (file_exists($formPath)) 
		{
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::addNew('userfield.add','JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0])) 
			{
				JToolBarHelper::editList('userfield.edit','JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state')) 
		{
			if (isset($this->items[0]->state)) 
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('userfields.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('userfields.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			} 
			else if (isset($this->items[0])) 
			{
				//If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'userfields.delete','JTOOLBAR_DELETE');
			}
			if (isset($this->items[0]->checked_out)) 
			{
				JToolBarHelper::custom('userfields.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) 
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) 
			{
				JToolBarHelper::deleteList('', 'userfields.delete','JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			} 
			else if ($canDo->get('core.edit.state')) 
			{
				JToolBarHelper::trash('userfields.trash','JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin')) 
		{
		JToolBarHelper::preferences('com_qazap');
		}

		//Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_qazap&view=userfields');

		$this->extra_sidebar = '';

		JHtmlSidebar::addFilter(

		JText::_('JOPTION_SELECT_PUBLISHED'),

		'filter_published',

		JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)

		);

		//Filter for the field required
		$select_label = JText::sprintf('COM_QAZAP_FILTER_SELECT_LABEL', 'Required');
		$options = array();
		$options[0] = new stdClass();
		$options[0]->value = "0";
		$options[0]->text = "No";
		$options[1] = new stdClass();
		$options[1]->value = "1";
		$options[1]->text = "Yes";
		JHtmlSidebar::addFilter(
			$select_label,
			'filter_required',
			JHtml::_('select.options', $options , "value", "text", $this->state->get('filter.required'), true)
		);

		//Filter for the field show_in_vendorgilling_form
		$select_label = JText::sprintf('COM_QAZAP_FILTER_SELECT_LABEL', 'Show In Vendor Billing Form');
		$options = array();
		$options[0] = new stdClass();
		$options[0]->value = "0";
		$options[0]->text = JText::_('JNO');
		$options[1] = new stdClass();
		$options[1]->value = "1";
		$options[1]->text = JText::_('JYES');
		JHtmlSidebar::addFilter(
			$select_label,
			'filter_show_in_vendorbilling_form',
			JHtml::_('select.options', $options , "value", "text", $this->state->get('filter.show_in_vendorbilling_form'), true)
		);		

		//Filter for the field show_in_account_maintainance
		$select_label = JText::sprintf('COM_QAZAP_FILTER_SELECT_LABEL', 'Show In UserBilling Form');
		$options = array();
		$options[0] = new stdClass();
		$options[0]->value = "0";
		$options[0]->text = JText::_('JNO');
		$options[1] = new stdClass();
		$options[1]->value = "1";
		$options[1]->text = JText::_('JYES');
		JHtmlSidebar::addFilter(
		$select_label,
		'filter_show_in_userbilling_form',
		JHtml::_('select.options', $options , "value", "text", $this->state->get('filter.show_in_userbilling_form'), true)
		);

		//Filter for the field show_in_shipment_form
		$select_label = JText::sprintf('COM_QAZAP_FILTER_SELECT_LABEL', 'Show In Shipment Form');
		$options = array();
		$options[0] = new stdClass();
		$options[0]->value = "0";
		$options[0]->text = JText::_('JNO');
		$options[1] = new stdClass();
		$options[1]->value = "1";
		$options[1]->text = JText::_('JYES');
		JHtmlSidebar::addFilter(
		$select_label,
			'filter_show_in_shipment_form',
			JHtml::_('select.options', $options , "value", "text", $this->state->get('filter.show_in_shipment_form'), true)
		);


	}
    
	protected function getSortFields()
	{
		return array(
			'a.id' => JText::_('JGRID_HEADING_ID'),
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.field_name' => JText::_('COM_QAZAP_USERFIELDS_FIELD_NAME'),
			'a.field_title' => JText::_('COM_QAZAP_USERFIELDS_FIELD_TITLE'),
			'a.field_type' => JText::_('COM_QAZAP_USERFIELDS_FIELD_TYPE'),
			'a.required' => JText::_('COM_QAZAP_USERFIELDS_REQUIRED'),
			'a.show_in_registration_form' => JText::_('COM_QAZAP_USERFIELDS_SHOW_IN_REGISTRATION_FORM'),
			'a.show_in_account_maintainance' => JText::_('COM_QAZAP_USERFIELDS_SHOW_IN_ACCOUNT_MAINTAINANCE'),
			'a.show_in_shipment_form' => JText::_('COM_QAZAP_USERFIELDS_SHOW_IN_SHIPMENT_FORM'),
		);
	}

    
}
