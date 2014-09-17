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
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

/**
 * View class for a list of qazap.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapViewUsers extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canDo		= JHelperContent::getActions('com_users');
		
		$lang = JFactory::getLanguage();
		$lang->load('', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		$lang->load('com_users', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_users/helpers/html');

		parent::display($tpl);
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
			'a.name' => JText::_('COM_USERS_HEADING_NAME'),
			'a.username' => JText::_('JGLOBAL_USERNAME'),
			'a.block' => JText::_('COM_USERS_HEADING_ENABLED'),
			'a.activation' => JText::_('COM_USERS_HEADING_ACTIVATED'),
			'a.email' => JText::_('JGLOBAL_EMAIL'),
			'a.lastvisitDate' => JText::_('COM_USERS_HEADING_LAST_VISIT_DATE'),
			'a.registerDate' => JText::_('COM_USERS_HEADING_REGISTRATION_DATE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
