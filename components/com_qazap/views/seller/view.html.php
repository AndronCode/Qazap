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
class QazapViewSeller extends JViewLegacy
{
	protected $state;	
	protected $params;
	protected $orders;
	protected $recentOrders;
	protected $user;
	protected $isVendor;
	protected $summery;
	protected $menu;
	protected $products;
	protected $paymentDetails;
	protected $defaultTitle;
	protected $path;	

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{	
		$app 				= JFactory::getApplication();	
		$this->state		= $this->get('State');
		$this->params		= $this->state->get('params');
		$this->user			= QZUser::get();
		$this->isVendor		= $this->user->get('isVendor');
		$this->activeVendor	= $this->user->get('activeVendor');
		$this->pagination	= $this->get('Pagination');
		$this->path			= array();
		
		$layout = $this->getLayout();
		
		if($layout == 'orderlist')
		{
			$this->orders = $this->get('Items');
			$this->defaultTitle	= JText::_('COM_QAZAP_SELLER_ORDER_LIST');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_MENU'), 'link' => 'index.php?option=com_qazap&view=seller');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_ORDER_LIST'), 'link' => '');
		}
		elseif($layout == 'order')
		{
			$this->orderDetails = $this->get('OrderDetails');
			$this->defaultTitle	= JText::_('COM_QAZAP_SELLER_ORDER_DETAILS') . " : ". $this->orderDetails->vendor_carts[$this->state->get('vendor.id')]->order_number;
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_MENU'), 'link' => 'index.php?option=com_qazap&view=seller');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_ORDER_DETAILS'), 'link' => '');
		}
		elseif($layout == 'paymentlist')
		{
			$this->payments = $this->get('Items');
			$this->summery = $this->get('PaymentSummary');
			$this->defaultTitle	= JText::_('COM_QAZAP_SELLER_PAYMENT_LIST');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_MENU'), 'link' => 'index.php?option=com_qazap&view=seller');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_PAYMENT_LIST'), 'link' => '');
		}
		elseif($layout == 'productlist')
		{
			$this->products = $this->get('Items');
			$this->defaultTitle	= JText::_('COM_QAZAP_SELLER_PRODUCT_LIST');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_MENU'), 'link' => 'index.php?option=com_qazap&view=seller');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_PRODUCT_LIST'), 'link' => '');
		}
		elseif($layout == 'paymentdetails')
		{
			$this->paymentDetails = $this->get('PaymentDetails');
			$this->defaultTitle	= JText::_('COM_QAZAP_SELLER_PAYMENT_DETAILS') . ' : ' . $this->paymentDetails->payment_id;
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_MENU'), 'link' => 'index.php?option=com_qazap&view=seller');
			$this->path[]		= array('title' => JText::_('COM_QAZAP_SELLER_PAYMENT_DETAILS'), 'link' => '');
		}
		else
		{
			$this->recentOrders = $this->get('RecentOrders');
			$this->summery = $this->get('PaymentSummary');
			$this->defaultTitle	= JText::_('COM_QAZAP_SELLER_MENU');
		}
		
		$this->layout = $layout;
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}
		
		QZHelper::addMenu('seller', $layout);
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
			if (strpos($currentLink, 'view=seller'))
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
	
	protected function prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$menu 		= $menus->getActive();
		$pathway	= $app->getPathway();
		$user		= JFactory::getUser();
		$login		= $user->get('guest') ? true : false;
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself		
		$menuLayout = isset($menu->query['layout']) ? $menu->query['layout'] : 'default';
		
		if($menu)
		{
			if(($menu->query['option'] == 'com_qazap' && $menu->query['view'] == 'seller'))
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
			elseif(($menu->query['option'] == 'com_qazap' && $menu->query['view'] == 'profile') && empty($this->path))
			{
				$this->params->def('page_heading', $this->defaultTitle);
				$title = $this->defaultTitle;				
				$this->path[] = array('title' => JText::_('COM_QAZAP_SELLER_MENU'), 'link' => 'index.php?option=com_qazap&view=seller');				
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
