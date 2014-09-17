<?php
/**
 * view.json.php
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
class QazapViewHome extends JViewLegacy
{
	protected $data;
	protected $error;
	protected $error_msg;
	/**
	* Display the view
	*/
	public function display($tpl = null)
	{
		$layout = $this->getLayout();
		
		if(strtolower($layout) == 'history')
		{
			$this->data = $this->get('OrderHistory');
		}
		else
		{
			$this->data = $this->get('Counts');
		}		

		if (count($errors = $this->get('Errors'))) 
		{
			$errors = array_unique($errors);
			$this->error = 1;
			$this->error_msg = implode("<br/>", $errors);
		}

		$this->setLayout('ajax');  
		parent::display($tpl);
	}

}
