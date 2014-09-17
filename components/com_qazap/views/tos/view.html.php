<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Article View class for the Content component
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class QazapViewTos extends JViewLegacy
{
	protected $item;

	protected $params;

	protected $print;
	
	protected $state;


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$dispatcher	= JEventDispatcher::getInstance();

		$this->item		= $this->get('Item');
		$this->print	= $app->input->getBool('print');
		$this->state	= $this->get('State');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		// Create a shortcut for $item.
		$item = $this->item;
		
		// Merge article params. If this is single-article view, menu params override article params
		// Otherwise, article params override menu item params
		$this->params = $this->state->get('params');
		$active = $app->getMenu()->getActive();
		$temp = clone ($this->params);
		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;
			// If the current view is the active item and an article view for this article, then the menu item params take priority
			if (strpos($currentLink, 'view=tos'))
			{
				// Load layout from active query (in case it is an alternative menu item)
				// $item->params are the article params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$active->params->merge($temp);
				$this->params = $active->params;
			}
		}
	
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();
		
		parent::display($tpl);
	}
	
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$title		= null;
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		
		if ($menu && strpos($menu->link, 'view=tos'))
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_QAZAP_TOS'));
		}
		
		$title = $this->params->get('page_title',  JText::_('COM_QAZAP_TOS'));
		
		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $this->escape($this->params->get('page_heading'));
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		
		$this->document->setTitle($title);
			
	}
}
