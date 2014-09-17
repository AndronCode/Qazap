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
class QazapViewProduct extends JViewLegacy
{
	protected $state;	
	protected $item;
	protected $parent;
	protected $children;
	protected $print;
	protected $siblings;
	protected $quantity;
	protected $params;
	protected $layout_position;
	protected $product_url;
	protected $userReviewDone;
	protected $vendor_id;
	protected $isActiveVendor;
	protected $isVendor;
	protected $pageclass_sfx;
	

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$app					= JFactory::getApplication(); 
		$user					= JFactory::getUser();
		$dispatcher		= JEventDispatcher::getInstance();
		$qzuser				= QZUser::get();

		$this->state						= $this->get('State');
		$this->item							= $this->get('Item');
		$this->print						= $app->input->getBool('print');
		$this->user							= $user;
		$this->reviews					= $this->get('Reviews');
		$this->layout_position	= 'standard';
		$this->product_url			= QazapHelperRoute::getProductRoute($this->item->slug, $this->item->category_id);		
		$this->isActiveVendor		= $qzuser->get('activeVendor', 0);
		$this->isVendor					= $qzuser->isVendor;
		$this->vendor_id				= $qzuser->get('vendor_id', 0);
	
		if($this->item)
		{
			$this->siblings = $this->item->getSiblings();
			$this->children = $this->item->getChildren();
			
			if(!empty($this->item->parent_id))
			{
				$this->parent = $this->item->getParent();

				if(empty($this->parent) || !$this->parent->state || $this->parent->block)
				{
					return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_PRODUCT_NOT_FOUND'));
				}
				
				if($this->parent->params)
				{
					// Merge parent params to the children but give priority to its own
					$this->parent->params = $this->parent->getParams();
					$ownParams = clone($this->item->params);
					$this->item->params = $this->parent->params;
					$this->item->params->merge($ownParams);					
				}
			}			
		}
		
		if(!$user->guest)
		{
			$this->userReviewDone = $this->get('UserReviewDone');
		}
		else
		{
			$this->userReviewDone = false;
		}		
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		// Create a shortcut for $item.
		$item = $this->item;
		$item->tagLayout = new JLayoutFile('joomla.content.tags');
		
		// Merge product params. If this is single product view, menu params override product params
		// Otherwise, product params override menu item params
		$this->params = $this->state->get('params');
		$active = $app->getMenu()->getActive();
		$temp = clone ($this->params);		
		
		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;

			// If the current view is the active item and an product view for this product, then the menu item params take priority
			if (strpos($currentLink, 'view=product') && (strpos($currentLink, '&product_id='.(string) $item->product_id)))
			{
				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout']))
				{
					$this->setLayout($active->query['layout']);
				}
				// Check for alternative layout of product
				elseif ($layout = $item->params->get('product_layout', 'default'))
				{
					$this->setLayout($layout);
				}

				// $item->params are the product params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$item->params->merge($temp);
			}
			else
			{
				// Current view is not a single product, so the product params take priority here
				// Merge the menu item params with the product params so that the product params take priority
				$temp->merge($item->params);
				$item->params = $temp;
				$layout = $this->getLayout();
				$layoutFile = QZHelper::getLayoutFile($layout);
				
				// If layout is notify
				if(!empty($layoutFile))
				{
					$this->setLayout($layout);
				}				
				// Check for alternative layouts (since we are not in a single-product menu item)
				// Single-product menu item layout takes priority over alt layout for an product
				elseif ($layout = $item->params->get('product_layout', 'default'))
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that product params take priority
			$temp->merge($item->params);
			$item->params = $temp;
			$layout = $this->getLayout();
			$layoutFile = QZHelper::getLayoutFile($layout);
			
			// If layout is notify
			if(!empty($layoutFile))
			{
				$this->setLayout($layout);
			}				
			// Check for alternative layouts (since we are not in a single-product menu item)
			// Single-product menu item layout takes priority over alt layout for an product
			elseif ($layout = $item->params->get('product_layout', 'default'))
			{
				$this->setLayout($layout);
			}
		}				

		$offset = $this->state->get('list.offset');

		// Check the view access to the product (the model has already computed the values).
		if ($item->params->get('access-view') != true && (($item->params->get('show_noauth') != true &&  $user->get('guest') )))
		{
			JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}
		
		$item->tags = new JHelperTags;
		//$item->tags->getItemTags('com_qazap.product', $this->item->product_id);
		
		// Decide if add to cart button to be displayed
		$item->buy_action = 'addtocart';
		
		if($this->item->in_stock - $this->item->booked_order <= 0 && $this->params->get('enablestockcheck'))
		{
			$stockout_handle = $item->params->get('stockout_action', 'notify');
			if($stockout_handle == 'notify')
			{
				$item->buy_action = 'notify';
			}
			elseif($stockout_handle == 'hide')
			{
				$item->buy_action = null;
			}	
		}

		$item->tags = new JHelperTags;
		$item->tags->getItemTags('com_qazap.product' , $this->item->product_id);
		
		// Process the product plugins.
		JPluginHelper::importPlugin('qazapproduct');
		$dispatcher->trigger('onProductPrepare', array (&$item, &$this->params, $offset));

		$item->event = new stdClass;
		$results = $dispatcher->trigger('onProductAfterTitle', array(&$item, &$this->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onProductBeforeDisplay', array(&$item, &$this->params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onProductAfterDisplay', array(&$item, &$this->params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));		
		
		// Increase the hit count
		$model = $this->getModel();
		$model->hit();
		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
    $this->_prepareDocument();
    parent::display($tpl);
	}


	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app			= JFactory::getApplication();
		$menus		= $app->getMenu();
		$menu			= $menus->getActive();
		$pathway	= $app->getPathway();
		$metadata	= $this->item->getMetadata();
		$title		= $this->params->get('page_title', $metadata->get('page_title', $this->item->product_name));

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_QAZAP_TITLE_PRODUCT'));
		}

		$id = (int) @$menu->query['product_id'];
		$path = array();
		// if the menu item does not concern this product
		if ($menu && ($menu->query['option'] != 'com_qazap' || $menu->query['view'] != 'product' || $id != $this->item->product_id))
		{
			// If this is not a single product menu item, set the page title to the product title
			if ($metadata->get('page_title', $this->item->product_name))
			{
				$title = $metadata->get('page_title', $this->item->product_name);
				$path = array(array('title' => $this->item->product_name, 'link' => ''));
			}			
			
			$category = QZCategories::getInstance();
			$category = $category->get($this->item->category_id);

			while ($category && ($menu->query['option'] != 'com_qazap' || $menu->query['view'] == 'product' || $id != $category->category_id) && $category->category_id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => QazapHelperRoute::getCategoryRoute($category));
				$category = $category->getParent();
			}
			
			$path = array_reverse($path);

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

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif (!$this->item->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif (!$this->item->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}		

		if($this->print)
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}		
		elseif($metadata->get('robots'))
		{
			$this->document->setMetadata('robots', $metadata->get('robots'));
		}
		elseif (!$metadata->get('robots') && $this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($app->getCfg('MetaAuthor') == '1' && $metadata->get('author'))
		{
			$this->document->setMetaData('author', $metadata->get('author'));
		}
		
		if($metadata->get('author'))
		{
			$this->document->setMetadata('author', $metadata->get('author'));
		}	
		
		if($metadata->get('rights'))
		{
			$this->document->setMetadata('rights', $metadata->get('rights'));
		}
	}    	
}
