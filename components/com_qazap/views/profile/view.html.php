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
 * Profile view class for Qazap.
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapViewProfile extends JViewLegacy
{
	protected $BTAddress;
	
	protected $STAddresses;
	
	protected $data;

	protected $form;

	protected $params;

	protected $state;
	
	protected $orders;
	
	protected $wishList;
	
	protected $menu;
	
	protected $defaultTitle;
	
	protected $layout;
	
	protected $path;

	/**
	 * Method to display the view.
	 *
	 * @param   string	$tpl	The template file to include
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		// Get the view data.
		$app			= JFactory::getApplication();
		$this->state	= $this->get('State');
		$this->params	= $this->state->get('params');
		$this->path		= array();				
		
		$layout = $this->getLayout();
		
		if(($layout == 'wishlist') && ($this->params->get('wishlist_system', 1) != 1))
		{
			$this->setLayout('default');
			$layout = 'default';
		}

		if($layout == 'default') 
		{
			$this->BTAddress	= $this->get('BTAddress');
			$this->STAddresses 	= $this->get('STAddresses');
			$this->defaultTitle	= JText::_('COM_QAZAP_PROFILE');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE'), 'link' => '');
		} 
		elseif($layout == 'edit') 
		{
			$this->item			= $this->get('Item');
			$this->form			= $this->get('Form');
			$this->defaultTitle	= JText::_('COM_QAZAP_PROFILE_EDIT');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE'), 'link' => 'index.php?option=com_qazap&view=profile');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE_EDIT'), 'link' => '');
		} 
		elseif($layout == 'wishlist') 
		{
			$this->wishList 	= $this->get('Items', 'profilelists');
			$this->pagination	= $this->get('Pagination', 'profilelists');	
			$this->defaultTitle = JText::_('COM_QAZAP_PROFILE_WISHLIST');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE'), 'link' => 'index.php?option=com_qazap&view=profile');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE_WISHLIST'), 'link' => '');			
		}
		elseif($layout == 'waitinglist') 
		{
			$this->waitingList = $this->get('Items', 'profilelists');
			$this->pagination	= $this->get('Pagination', 'profilelists');
			$this->defaultTitle = JText::_('COM_QAZAP_PROFILE_WAITING_LIST');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE'), 'link' => 'index.php?option=com_qazap&view=profile');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE_WAITING_LIST'), 'link' => '');					
		}
		elseif($layout == 'orderlist') 
		{
			$this->state				= $this->get('State', 'profilelists');
			$this->orders 			= $this->get('Items', 'profilelists');
			$this->pagination		= $this->get('Pagination', 'profilelists');	
			$this->defaultTitle	= JText::_('COM_QAZAP_PROFILE_ORDER_LIST');
			$this->path[]				= array('title' => JText::_('COM_QAZAP_PROFILE'), 'link' => 'index.php?option=com_qazap&view=profile');
			$this->path[]				= array('title' => JText::_('COM_QAZAP_PROFILE_ORDER_LIST'), 'link' => '');				
		}
		elseif($layout == 'order')
		{
			$this->orderDetails = $this->get('Order');
			$this->defaultTitle	= JText::_('COM_QAZAP_ORDERGROUP_NUMBER_LABEL') . ': ' . $this->orderDetails->ordergroup_number;
			$this->path[]		= array('title' => JText::_('COM_QAZAP_PROFILE'), 'link' => 'index.php?option=com_qazap&view=profile');
			$this->path[]		= array('title' => $this->defaultTitle, 'link' => '');				
		}
		
		$this->layout = $layout;
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		QZHelper::addMenu('profile', $layout);
		$this->menu = JHtml::_('qzmenu.render');
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
			if (strpos($currentLink, 'view=profile'))
			{
				// Load layout from active query (in case it is an alternative menu item)
				// $item->params are the article params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$active->params->merge($temp);
				$this->params = $active->params;
			}
		}		
		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->prepareDocument();
		parent::display($tpl);
	}

	/**
	* Prepares the document
	*
	* @since   1.0.0
	*/
	protected function prepareDocument()
	{
		$app			= JFactory::getApplication();
		$menus		= $app->getMenu();
		$menu 		= $menus->getActive();
		$pathway	= $app->getPathway();
		$user			= JFactory::getUser();
		$login		= $user->get('guest') ? true : false;
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself		
		$menuLayout = isset($menu->query['layout']) ? $menu->query['layout'] : 'default';
		
		if($menu && ($menu->query['option'] = 'com_qazap' && $menu->query['view'] = 'profile'))
		{
			if($this->layout == $menuLayout)
			{
				$this->params->def('page_heading', $this->params->get('page_heading', $menu->title));
				$title = $this->params->get('page_title',  $menu->title);
				$this->path = array();
			}
			else
			{
				$this->params->def('page_heading', $this->defaultTitle);
				$title = $this->defaultTitle;
				
				if(!empty($this->path))
				{
					array_shift($this->path);
				}				
			}
		}
		else
		{
			$this->params->def('page_heading', $this->defaultTitle);
			$title = $this->defaultTitle;
		}
		
		if(!empty($this->path))
		{
			foreach ($this->path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}			
		}
		
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

		if ($this->params->get('menu-meta_description') && ($this->layout == $menuLayout))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords') && ($this->layout == $menuLayout))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots') && ($this->layout == $menuLayout))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
