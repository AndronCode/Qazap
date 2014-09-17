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
 * View to edit
 */
class QazapViewProduct extends JViewLegacy
{
	protected $item;
	
	public function display($tpl = null)
	{
		
		$layoutName = JRequest::getWord('layout', 'field');
		$this->setLayout($layoutName);	
		
		if($layoutName == 'field') {
			$this->item = $this->get('CustomField');
		}
		elseif($layoutName == 'attribute') {
			$this->item = $this->get('Attribute');
		}			

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}

}
