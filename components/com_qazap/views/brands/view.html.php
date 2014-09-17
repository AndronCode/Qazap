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
class QazapViewBrands extends JViewLegacy
{
	
	protected $state;
	protected $items;
	protected $title;
	protected $pagination;
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
		$this->state			= $this->get('State');
		$this->items			= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->params			= $this->state->get('params');
		$this->title			= $this->get('Title');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		$this->_prepareDocument();
		return parent::display($tpl);
	}
	
	protected function _prepareDocument()
	{
		$app			= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if($menu)
		{
			if($menu->query['view'] == 'brands')
			{
				$temp = clone($this->params);
				$menu->params->merge($temp);
				$this->params = $menu->params;
				$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			}
			else
			{
				$pathway->addItem(JText::_('COM_QAZAP_BRANDS'), 'index.php?option=com_qazap&view=brands');
				$heading = (!empty($this->title)) ? $this->title : JText::_('COM_QAZAP_BRANDS');
				$this->params->def('page_heading', $heading);
			}
		}
		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$title = $this->params->get('page_title', (!empty($this->title)) ? $this->title : JText::_('COM_QAZAP_BRANDS'));
		
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
