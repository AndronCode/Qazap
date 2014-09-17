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
 * HTML Article View class for the Qazap component
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapViewSellerform extends JViewLegacy
{
	protected $form;
	protected $vendor;
	protected $return_page;
	protected $state;	
	protected $user;
	protected $group;
	protected $menu;

	public function display($tpl = null)
	{
		$user								= JFactory::getUser();
		$app								= JFactory::getApplication();
		$this->state				= $this->get('State');
		$this->vendor				= $this->get('Item');
		$this->form					= $this->get('Form');
		$this->return_page	= $this->get('ReturnPage');
		$this->user					= JFactory::getUser();
		$this->group				= $this->get('Group');
		$this->params				= $this->state->get('params');
		
		QZHelper::addMenu('sellerform', 'edit');
		$this->menu = JHtml::_('qzmenu.render');

		$this->params = $this->state->get('params');
					
		$this->_prepareDocument();		
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		
		parent::display($tpl);
	}

	/**
	* Prepares the document
	*/
	protected function _prepareDocument()
	{
		$app			= JFactory::getApplication();
		$menus		= $app->getMenu();
		$title 		= JText::_('COM_QAZAP_SELLER_EDIT_BILLING_ADDRESS');
		$pathway	= $app->getPathway();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		$menuOption = ($menu && isset($menu->query['option'])) ? $menu->query['option'] : null;
		$menuView = ($menu && isset($menu->query['view'])) ? $menu->query['view'] : null;
		$isThisPage = ($menuOption == 'com_qazap' && $menuView == 'sellerform');

		if($isThisPage)
		{
			$temp = clone ($this->params);
			$menu->params->merge($temp);
			$this->params = $menu->params;			
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			$title = $this->params->get('page_title', $menu->title);
		}
		else
		{
			$this->params->set('page_heading', $title);
			$this->params->set('show_page_heading', 1);	
			$pathway->addItem($title, '');		
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

		if ($this->params->get('menu-meta_description') && $isThisPage)
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords') && $isThisPage)
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots') && $isThisPage)
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}		
	}
	
	protected function valueToString($value)
	{
		if(strpos($value, '[') !== false && strpos($value, ']') !== false)
		{
			$tmp = json_decode($value, true);
			if(!empty($tmp))
			{
				$value = implode(',', $tmp);
			}
		}
		
		return (string) $value;
	}
}
