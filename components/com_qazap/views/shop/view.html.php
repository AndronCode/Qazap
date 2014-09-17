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
class QazapViewShop extends JViewLegacy
{
	protected $state;	
	protected $shop;
	protected $store;
	protected $productLists;
	protected $params;
	protected $pageclass_sfx;	

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$app								= JFactory::getApplication(); 
		$user								= JFactory::getUser();
		$dispatcher					= JEventDispatcher::getInstance();	
		$this->state				= $this->get('State');
		$this->shop					= $this->get('Item');
		$this->store				= $this->get('StoreInfo');
		$this->user					= $user;
		$this->productLists	= $this->get('ProductLists');
		$this->params				= $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		if(empty($this->shop))
		{
			return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_SHOP_NOT_FOUND'));
		}
		
		// Merge product params. If this is single product view, menu params override product params
		// Otherwise, product params override menu item params
		$this->params = $this->state->get('params');
		$active = $app->getMenu()->getActive();
		$temp = clone($this->params);	
		
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

		$id = (int) @$menu->query['vendor_id'];

		if($menu && $menu->query['view'] == 'shop' && $id == $this->shop->id)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', '');
		}
		
		$title = $this->params->get('page_title', $this->shop->shop_name);
		
		// if the menu item does not concern this product
		if ($menu && ($menu->query['option'] != 'com_qazap' || $menu->query['view'] != 'shop' || $id != $this->shop->id))
		{
			// If this is not a single product menu item, set the page title to the product title
			if ($this->shop->shop_name)
			{
				$title = $this->shop->shop_name;
			}
			
			$path = array();
			
			if($menu->query['view'] != 'shops')
			{
				$path[] = array('title' => JText::_('COM_QAZAP_SHOPS'), 'link' => 'index.php?option=com_qazap&view=shops');
			}
						
			$path[] = array('title' => $this->shop->shop_name, 'link' => '');

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
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

		if (isset($this->shop->metadesc) && $this->shop->metadesc)
		{
			$this->document->setDescription($this->shop->metadesc);
		}
		elseif ((!isset($this->shop->metadesc) || !$this->shop->metadesc) && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if (isset($this->shop->metakey) && $this->shop->metakey)
		{
			$this->document->setMetadata('keywords', $this->shop->metakey);
		}
		elseif ((!isset($this->shop->metakey) || !$this->shop->metakey) && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	
	}
}
