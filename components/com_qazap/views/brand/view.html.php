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
class QazapViewBrand extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $params;
	protected $pageclass_sfx;	

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
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->params	= $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		
		$this->_prepareDocument();
		return parent::display($tpl);
	}
	
	protected function paragraph($string)
	{
		if(strpos($string, '<p>') === false)
		{
			$string = '<p>' . $string . '</p>';
		}
		
		return $string;
	}
	
	protected function weblink($url, $target = '_blank')
	{
		$url = rtrim(trim($url), '/');
		
		if(strpos($url, 'http') !== 0)
		{
			$uri = 'http://' . $url;
		}
		else
		{
			$uri = $url;
			$url = str_replace(array('http://', 'https://'), array('', ''), $url);
		}
		
		$html = '<a href="' . $uri . '" title="' . $url . '"';
		
		if(!empty($target))
		{
			$html .= ' target="' . $target . '"';
		}	
		
		$html .= '>' . $url . '</a>';
		
		return $html;
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

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['brand_id'];

		// if the menu item does not concern this product
		if ($menu && ($menu->query['option'] != 'com_qazap' || $menu->query['view'] != 'brand' || $id != $this->item->id))
		{
			$title = $this->item->manufacturer_name;
			
			$path = array();
			
			if($menu->query['view'] != 'brands')
			{
				$path[] = array('title' => JText::_('COM_QAZAP_BRANDS'), 'link' => 'index.php?option=com_qazap&view=brands');
			}
			else
			{
				$menu->params->merge($this->params);
				$this->params = $menu->params;
			}
						
			$path[] = array('title' => $this->item->manufacturer_name, 'link' => '');

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

		if(empty($title))
		{
			$title = $this->item->manufacturer_name;
		}
		$this->document->setTitle($title);

		if (isset($this->item->metadesc) && $this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ((!isset($this->item->metadesc) || !$this->item->metadesc) && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if (isset($this->item->metakey) && $this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif ((!isset($this->item->metakey) || !$this->item->metakey) && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	
	}
}
