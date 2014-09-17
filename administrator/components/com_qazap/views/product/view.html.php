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
class QazapViewProduct extends JViewLegacy
{
	protected $state;
	protected $params;
	protected $item;
	protected $form;
	protected $savedAttributes;
	protected $savedFields;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state						= $this->get('State');
		$this->params						= $this->state->get('params');
		$this->item							= $this->get('Item');
		$this->form							= $this->get('Form');
		$this->savedAttributes 	= $this->get('SavedAttributes');
		$this->savedFields			= $this->get('SavedCustomFields');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
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
		$user								= JFactory::getUser();
		$lang								= JFactory::getLanguage();
		$multiple_language	= JLanguageMultilang::isEnabled();
		$present_langauge		= $lang->getTag();
		$default_language		= $lang->getDefault();
		
		$isNew		= ($this->item->product_id == 0);
		
		if (isset($this->item->checked_out)) 
		{
			$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} 
		else 
		{
			$checkedOut = false;
		}
		
		$canDo = QazapHelper::getActions();
		
		$names = array();
		
		if(isset($this->item->product_name))
		{
			$names = clone $this->item->product_name;
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
			$name = JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_PRODUCT_ADD');
		}
		elseif(empty($name))
		{
			$name = JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_PRODUCT_ADD');
		}
		else
		{
			$name = JText::_('COM_QAZAP') . ': ' . JText::_('COM_QAZAP_TITLE_PRODUCTS') . ' - ' . $name;
			$name .= '&nbsp;<a href="' . QazapHelperRoute::mail(QazapHelperRoute::getProductRoute($this->item->product_id, $this->item->category_id)) . '" class="qzproduct-preview-link" target="_blank"><span class="icon-out-2"></span></a>';
		}

		JToolBarHelper::title($name, ' qzicon-file-plus2');
		

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('product.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('product.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('product.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) 
		{
			JToolBarHelper::custom('product.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if (empty($this->item->product_id)) 
		{
			JToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CANCEL');
		}
		else 
		{
			JToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}
