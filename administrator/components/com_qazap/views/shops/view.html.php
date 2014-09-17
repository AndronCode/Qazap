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
 * View class for a list of Qazap.
 */
class QazapViewShops extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		//qzdump($this->items);exit;
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
		throw new Exception(implode("\n", $errors));
		}

		QazapHelper::addSubmenu('shop');

		$this->addToolbar();

		$this->sidebar = QZHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.0.0
	*/
	protected function addToolbar()
	{
		$canDo	= QazapHelper::getActions();

		JToolBarHelper::title(JText::_('COM_QAZAP') . ': ' .JText::_('COM_QAZAP_TITLE_SHOPS'), ' qzicon-home12');
		if ($canDo->get('core.edit.state')) 
		{
			JToolBarHelper::custom('shops.createMultiple', 'checkin.png', 'checkin_f2.png', 'COM_QAZAP_CREATE_MULTIPLE_SHOPS', false);
			JToolBarHelper::custom('shops.createMultiple', 'checkin.png', 'checkin_f2.png', 'COM_QAZAP_RECREATE_MULTIPLE_SHOPS', false);

			JToolBarHelper::custom('shops.disableMultiple', 'checkin.png', 'checkin_f2.png', 'COM_QAZAP_DISABLE_MULTIPLE_SHOPS', false);
		}


		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::preferences('com_qazap');
		}

	}

    
}
