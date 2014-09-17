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
class QazapViewReviews extends JViewLegacy
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

	QazapHelper::addSubmenu('reviews');

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

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_REVIEWS'), ' qzicon-pencil2');

			//Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/review';
		if (file_exists($formPath)) 
		{

			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::addNew('review.add','JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0])) 
			{
				JToolBarHelper::editList('review.edit','JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state')) 
		{

			if (isset($this->items[0]->state)) 
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('reviews.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('reviews.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			} 
			else if (isset($this->items[0])) 
			{
				//If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'reviews.delete','JTOOLBAR_DELETE');
			}

		}

		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) 
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) 
			{
				JToolBarHelper::deleteList('', 'reviews.delete','JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			} 
			else if ($canDo->get('core.edit.state')) 
			{
				JToolBarHelper::trash('reviews.trash','JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::preferences('com_qazap');
		}

		//Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_qazap&view=reviews');

		$this->extra_sidebar = '';

		JHtmlSidebar::addFilter(

		JText::_('JOPTION_SELECT_PUBLISHED'),

		'filter_published',

		JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)

		);

		//Filter for the field product
		$select_label = JText::sprintf('COM_QAZAP_FILTER_SELECT_LABEL', 'Product');
		$options = array();
		$options[0] = new stdClass();
		$options[0]->value = "";
		$options[0]->text = "*";
		JHtmlSidebar::addFilter(
			$select_label,
			'filter_product',
			JHtml::_('select.options', $options , "value", "text", $this->state->get('filter.product'), true)	
		);
	}

	protected function getSortFields()
	{
		return array(
			'a.id' => JText::_('JGRID_HEADING_ID'),
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.checked_out' => JText::_('COM_QAZAP_REVIEWS_CHECKED_OUT'),
			'a.checked_out_time' => JText::_('COM_QAZAP_REVIEWS_CHECKED_OUT_TIME'),
			'a.created_by' => JText::_('COM_QAZAP_REVIEWS_CREATED_BY'),
			'a.product' => JText::_('COM_QAZAP_REVIEWS_PRODUCT'),
			'a.comment' => JText::_('COM_QAZAP_REVIEWS_COMMENT'),
			'a.rating' => JText::_('COM_QAZAP_REVIEWS_RATING'),
		);
	}

    
}
