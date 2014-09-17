<?php
/**
 * cartattributestype.php
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
 * Cartattributestype controller class.
 */
class QazapControllerCartattributestype extends JControllerForm
{
	function __construct() 
	{
		$this->view_list = 'cartattributestypes';
		parent::__construct();
	}

	public function getCartAttrParams() 
	{
		$app = JFactory::getApplication();
		$post = JRequest::get('POST');
		$app->setUserState('com_qazap.new.cartattributetype', $post['attrtype_id']);
		$this->setRedirect('index.php?option=com_qazap&view=cartattributestype&format=json&layout=attributetype');	
	}
}