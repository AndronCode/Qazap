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
class QazapViewCategory extends JViewLegacy
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
		$this->canDo = JHelperContent::getActions('com_qazap', 'category', $this->item->category_id);

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

		$user		= JFactory::getUser();
		$userId = $user->get('id');
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_langauge = $lang->getTag();
		$default_language = $lang->getDefault();
		$isNew		= ($this->item->category_id == 0);

		if (isset($this->item->checked_out)) 
		{
			$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		} 
		else 
		{
			$checkedOut = false;
		}
		
		$names = array();
		
		if(isset($this->item->title))
		{
			$names = clone $this->item->title;
		}
		
		$names = (object) $names;
		
		if($multiple_language)
		{
			$names->$default_language = isset($names->$default_language) ? $names->$default_language : '';
			$name = isset($names->$present_langauge) ? $names->$present_langauge : $names->$default_language;
		}
		else
		{
			$name = isset($names->$present_langauge) ? $names->$present_langauge : '';
		}
		
		if(empty($name) && !$isNew)
		{
			$name = JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_CATEGORY_ADD');
		}
		elseif(empty($name))
		{
			$name = JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_CATEGORY_ADD');
		}
		else
		{
			$name =JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_TITLE_CATEGORIES') . ' - ' . $name;
		}

		JToolBarHelper::title($name, ' qzicon-tree4');
		

		$canDo				= $this->canDo;
		$canEdit			= $user->authorise('core.edit', 'com_qazap.category.' . $this->item->category_id);
		$canCheckin		= $user->authorise('core.admin', 'com_checkin') || $this->item->checked_out == $userId || $item->checked_out == 0;
		$canEditOwn		= $user->authorise('core.edit.own', 'com_qazap.category.' . $this->item->category_id) && $this->item->created_user_id == $userId;
		$canChange		= $user->authorise('core.edit.state', 'com_qazap.category.' . $this->item->category_id) && $canCheckin;		

		$lang					= JFactory::getLanguage();
		$presentLang	= $lang->getTag();
		$defaultLang	= $lang->getDefault();

		$savedTitle		=  (isset($this->item->title->$presentLang) &&	empty($this->item->title->$presentLang)) ? 
							$this->item->title->$defaultLang : 
							(isset($this->item->title->$presentLang) ? $this->item->title->$presentLang : '');

		$title = empty($savedTitle) ? JText::_('COM_QAZAP_TITLE_CATEGORY') : $savedTitle;


		// If not checked out, can save the item.
		if (!$checkedOut && ($canEdit || $canEditOwn ||($canDo->get('core.create'))))
		{
			JToolBarHelper::apply('category.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('category.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('category.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) 
		{
			JToolBarHelper::custom('category.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		if (empty($this->item->category_id)) 
		{
			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CANCEL');
		}
		else 
		{
			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}
