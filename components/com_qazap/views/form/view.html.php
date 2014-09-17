<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;

/**
 * HTML Article View class for the Qazap component
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
class QazapViewForm extends JViewLegacy
{
	protected $form;
	
	protected $params;

	protected $item;

	protected $return_page;

	protected $state;
	protected $savedAttributes;
	protected $savedFields;	
	protected $user;
	protected $isActiveVendor;
	protected $vendor_id;
	protected $title;

	public function display($tpl = null)
	{
		// Get model data.
		$this->state			= $this->get('State');			
		$this->item				= $this->get('Item');
		$this->form				= $this->get('Form');
		$this->return_page		= $this->get('ReturnPage');
		$this->savedAttributes 	= $this->get('SavedAttributes');
		$this->savedFields		= $this->get('SavedCustomFields');	
		$this->params			= $this->state->get('params');		
		$this->user				= JFactory::getUser();
		$qzuser					= $this->state->get('qzuser');
		$this->isActiveVendor	= $qzuser->get('activeVendor', 0);
		$this->vendor_id		= $qzuser->get('vendor_id', 0);
		
		if (!$this->isActiveVendor || ($this->item->product_id > 0 && ($this->vendor_id != $this->item->vendor)))
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		//$this->item->tags = new JHelperTags;
		if (!empty($this->item->product_id))
		{
			$this->item->tags->getItemTags('com_qazap.product.', $this->item->product_id);
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

/*		if ($params->get('enable_category') == 1)
		{
			$this->form->setFieldAttribute('catid', 'default', $params->get('catid', 1));
			$this->form->setFieldAttribute('catid', 'readonly', 'true');
		}*/
		
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
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		$menuOption = isset($menu->query['option']) ? $menu->query['option'] : null;
		$menuView = isset($menu->query['view']) ? $menu->query['view'] : null;
		
		if($menuOption == 'com_qazap' && $menuView == 'form')
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			$title = $this->params->get('page_title', $menu->title);
		}
		elseif(!empty($this->item->product_id))
		{
			$title = JText::_('COM_QAZAP_EDIT_PRODUCT');
			$this->params->def('page_heading', $title);
			$this->params->set('show_page_heading', 1);
		}
		else
		{
			$title = JText::_('COM_QAZAP_ADD_NEW_PRODUCT');
			$this->params->def('page_heading', $title);
			$this->params->set('show_page_heading', 1);			
		}		
		
		if($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');
		$this->title = $title;
		
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
	}
}
