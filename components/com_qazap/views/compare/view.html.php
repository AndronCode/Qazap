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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_qazap/helpers/html');
/**
 * HTML Article View class for the Qazap
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapViewCompare extends JViewLegacy
{
	protected $state;
	protected $list;
	protected $products;	
	protected $params;
	protected $continue_url;
	protected $display_fields;
	protected $pageclass_sfx;
	protected $prices;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		// Get model data.
		$this->state		= $this->get('State');
		$this->list			= $this->get('List');
		$this->products		= $this->get('Products');
		$this->params		= $this->state->get('params');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$lastvisited_category_id = (int) $app->getUserState('com_qazap.category.lastvisted.id', 0);
		
		if($lastvisited_category_id > 0)
		{
			$this->continue_url = JRoute::_(QazapHelperRoute::getCategoryRoute($lastvisited_category_id));
		}
		else
		{
			$this->continue_url = JUri::base();
		}
		
		$fields = JHtml::_('qzproductobject.options');
		$fieldsConfig = $this->params->get('display_fields', array_keys($fields));
		$fields = array_merge(array('product_id'), $fieldsConfig);		
		$this->display_fields = array_unique($fields);
		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->_prepareDocument();
		parent::display($tpl);
	}


	protected function _prepareDocument()
	{
		$app			= JFactory::getApplication();
		$menus		= $app->getMenu();
		$menu			= $menus->getActive();
		$title		= null;
		
		if($menu && isset($menu->query['view']) && $menu->query['view'] == 'compare')
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			$title = $this->params->get('page_title', $menu->title);
		}
		else
		{
			$this->params->set('page_heading', JText::_('COM_QAZAP_COMPARE'));
			$title = JText::_('COM_QAZAP_COMPARE');
			$pathway = $app->getPathWay();
			$pathway->addItem($title, '');
		}		
		
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
		
		if($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}		
			
	}

}
