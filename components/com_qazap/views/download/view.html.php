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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Qazap.
 */
class QazapViewDownload extends JViewLegacy
{
	protected $state;	
	protected $params;
	protected $form;	
	protected $user;
	protected $download;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
    $this->state					= $this->get('State');
		$this->params					= $this->state->get('params');
		$this->form						= $this->get('ValidationForm');		
		$this->user						= JFactory::getUser();
		$this->download				= $this->get('Download');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) 
    {
			throw new Exception(implode("\n", $errors));
    }
		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->_prepareDocument();		
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;		
		$default_title = !empty($this->download) ? 
											JText::_('COM_QAZAP_DOWNLOAD_PAGE_TITLE') . ' - ' . $this->download->name : 
											JText::_('COM_QAZAP_DOWNLOAD_PAGE_TITLE');
											
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', $default_title);
		}

		$title = $this->params->get('page_title', '');

		// if the menu item does not concern this product
		if ($menu && ($menu->query['option'] != 'com_qazap' || $menu->query['view'] != 'download'))
		{
			$title = $default_title;
			$pathway->addItem(JText::_('COM_QAZAP_DOWNLOAD_PAGE_TITLE'), '');
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->getCfg('sitename');
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
		else
		{
			$this->document->setMetadata('robots', 'NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');
		}						

	}
}
