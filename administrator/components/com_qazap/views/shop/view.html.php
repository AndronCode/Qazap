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
 * @subpackage Admin
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
 * View to edit
 */
class QazapViewShop extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$canDo		= QazapHelper::getActions();

		$languages = JLanguageHelper::getLanguages('lang_code');

		if($this->item->lang == '*')
		{
			JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_SHOP').' - '.Jtext::_('JALL') , ' qzicon-home12');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_SHOP').' - '. $languages[$this->item->lang]->title, ' qzicon-home12');
		}


		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('shop.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('shop.save', 'JTOOLBAR_SAVE');
		}

		JToolBarHelper::cancel('shop.cancel', 'JTOOLBAR_CLOSE');

	}
}
