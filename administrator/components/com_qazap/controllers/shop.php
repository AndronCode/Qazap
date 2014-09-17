<?php
/**
 * shop.php
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
 * Shop controller class.
 */
class QazapControllerShop extends JControllerForm
{

	function __construct() 
	{
		$this->view_list = 'shops';
		parent::__construct();
	}
	/**
	* Gets the URL arguments to append to an item redirect.
	*
	* @param   integer  $recordId  The primary key id for the item.
	* @param   string   $urlVar    The name of the URL variable for the id.
	*
	* @return  string  The arguments to append to the redirect URL.
	*
	* @since   12.2
	*/
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$tmpl   = $this->input->get('tmpl');
		$layout = $this->input->get('layout', 'edit', 'string');
		$lang = $this->input->get('lang', null, 'string');
		
		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}
		
		if(!empty($lang))
		{
			$append .= '&lang=' . $lang;
		}		

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		return $append;
	}	
}