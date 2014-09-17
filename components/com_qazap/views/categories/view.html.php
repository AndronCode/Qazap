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
 * Categories view base class.
 *
 * @package     Qazap.Site
 * @subpackage  View
 * @since       1.0.0
 */
class QazapViewCategories extends JViewLegacy
{
	protected $user;
	/**
	 * State data
	 *
	 * @var    JRegistry
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Category items data
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $items;
	
	protected $productLists;
	
	protected $url;
	
	protected $maxLevelcat;
	
	protected $subCatPerRow;
	
	protected $subCatColumns;

	/**
	 * Language key for default page heading
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $pageHeading;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{		
		$user   = JFactory::getUser();
		$state  = $this->get('State');
		$items  = $this->get('Items');
		$parent = $this->get('Parent');
		$productLists = $this->get('ProductLists');

		$app = JFactory::getApplication();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage($errors, 'error');

			return false;
		}

		if ($items === false)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');

			return false;
		}

		if ($parent == false)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');

			return false;
		}

		$params = &$state->params;

		$items = array($parent->category_id => $items);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->maxLevelcat			= $params->get('maxLevelcat', -1);
		$this->params						= &$params;
		$this->parent						= &$parent;
		$this->items						= &$items;
		$this->productLists 		= &$productLists;
		$this->user       			= &$user;
		$this->url							= QazapHelperRoute::getCategoryRoute($this->parent);

		$this->prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_($this->pageHeading));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
