<?php
/**
 * textarea.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazapcustomfields Textarea Plugin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

if(!class_exists('QZApp'))
{
	require(JPATH_ADMINISTRATOR . '/components/com_qazap/app.php');
	// Setup Qazap for autload classes
	QZApp::setup();
}

class PlgQazapCustomFieldsTextarea extends QZFieldPlugin
{

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}
	
	
	public function onDisplayProductAdmin($data, &$html) {
		
		if($data->element != $this->_name)
		{
			return;
		}		

		$html .= '<textarea name="qzfield['.$data->id.'][value]" row="10" class="span12">'.$data->value.'</textarea>';
		$html .= '<input type="hidden" name="qzfield['.$data->id.'][typeid]" value="'.$data->typeid.'" />';
		$html .= '<input type="hidden" name="qzfield['.$data->id.'][id]" value="'.$data->id.'" />';
		$html .= '<input type="hidden" class="qzfield-ordering" name="qzfield['.$data->id.'][ordering]" value="'.$data->ordering.'" />';
				
		return true;		
	}

	public function onDisplayProduct(&$customfield) 
	{		
		if($customfield->plugin != $this->_name) 
		{
			return;
		}
		
		$display = array();
		foreach($customfield->data AS $data)
		{
			$display[] = '<p>' . $data->value . '</p>';
		}
		
		$customfield->display = implode($display);
		
		return true;
	}
	
}
