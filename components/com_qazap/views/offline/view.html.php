<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Article View class for the Content component
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class QazapViewOffline extends JViewLegacy
{
	protected $state;	
	protected $params;
	protected $store;
	protected $user;


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();

		$this->state	= $this->get('State');
		$this->params = $this->state->get('params');
		$this->store	= $this->get('StoreInfo');
		$this->user		= $user;
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		$this->document->setTitle(JText::_('COM_QAZAP_SHOP_OFFLINE_TITLE'));
		//$this->_prepareDocument();		
		parent::display($tpl);
	}	

}
