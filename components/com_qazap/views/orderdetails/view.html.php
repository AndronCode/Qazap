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

jimport('joomla.application.component.view');

/**
 * View class for a list of Qazap.
 */
class QazapViewOrderdetails extends JViewLegacy
{
	protected $state;
	protected $params;
	protected $orderDetails;	
	protected $canSee;
	protected $menu;

	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$this->state				= $this->get('State');
		$this->params				= $this->state->get('params');
		$this->orderDetails	= $this->get('Item');
		$this->canSee				= $this->get('CanSee');
		
		$user	= JFactory::getUser();
		
		if(!$user->guest)
		{
			QZHelper::addMenu('profile', 'order');
			$this->menu = JHtml::_('qzmenu.render');			
		}

				
		// Check for errors.
		if(count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}
		
		$app = JFactory::getApplication();
		$pathway	= $app->getPathway();
		$title = JText::_('COM_QAZAP_ORDERGROUP_NUMBER_LABEL') . ': ' . $this->orderDetails->ordergroup_number;
		$pathway->addItem($title, '');
		$this->document->setTitle($title);
		$this->document->setMetadata('robots', 'NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');
		
		parent::display($tpl);
	}
    

    
}
