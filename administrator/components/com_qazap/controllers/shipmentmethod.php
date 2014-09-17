<?php
/**
 * shipmentmethod.php
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

jimport('joomla.application.component.controllerform');

/**
* Shipmentmethod controller class.
*/
class QazapControllerShipmentmethod extends JControllerForm
{
	function __construct() 
	{
		$this->view_list = 'shipmentmethods';
		parent::__construct();
	}
	public function getShipmentAttr() 
	{			
		$app = JFactory::getApplication();
		$post = JRequest::get('POST');		
		$app->setUserState('com_qazap.new.shipmentmethod', $post);
		$this->setRedirect('index.php?option=com_qazap&view=shipmentmethod&format=json&layout=params');	
	}
}