<?php
/**
 * validate.json.php
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
require_once JPATH_COMPONENT.'/controller.php';

/**
 * Validate controller class.
 */
class QazapControllerValidate extends JControllerForm
{
	public function path()
	{
		$path = $this->input->get('path', '', 'string');		
		$return = array('valid' => 1);
		
		if(!is_dir($path))
		{
			$return['valid'] = 0;
		}

		echo json_encode($return);
		JFactory::getApplication()->close();		
	}
}