<?php
/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Anik Saha <anik.saha.2007@gmail.com> - http://www.virtueplanet.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
* Orderstatus controller class.
*/
class QazapControllerOrderstatus extends JControllerForm
{
	function __construct() 
	{
		$this->view_list = 'orderstatuses';
		parent::__construct();
	}
}