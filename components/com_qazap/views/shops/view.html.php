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

jimport('joomla.application.component.view');

/**
 * View class for a list of Qazap.
 */
class QazapViewShops extends JViewLegacy
{
	protected $shops;
	protected $store;
	protected $pagination;
	protected $state;
	protected $params;
	protected $pageclass_sfx;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$app 							= JFactory::getApplication();						
		$this->state			= $this->get('State');
		$this->shops			= $this->get('Items');
		$this->store			= $this->get('ShopDetails');
		$this->pagination	= $this->get('Pagination');
		$this->params			= $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->params = $this->state->get('params');
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));		
		$this->_prepareDocument();
		parent::display($tpl);
	}
    
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if($menu)
		{
			if($menu->query['view'] == 'shops')
			{
				$temp = clone($this->params);
				$menu->params->merge($temp);
				$this->params = $menu->params;
				$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			}
			else
			{
				$pathway->addItem(JText::_('COM_QAZAP_SHOPS'), '');
				$this->params->def('page_heading', JText::_('COM_QAZAP_SHOPS'));
			}
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_QAZAP_SHOPS'));
		}
		
		
		$title = $this->params->get('page_title', JText::_('COM_QAZAP_SHOPS'));
		
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

		if($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	
	}
    
}
