<?php
/**
 * qazapaddresstype.php
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
defined('JPATH_PLATFORM') or die;

/**
* Form Field class for the Joomla Platform.
* Supports a generic list of options.
*
* @package     Joomla.Platform
* @subpackage  Form
* @since       1.0.0
*/
class JFormFieldQazapAddresstype extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'QazapAddresstype';

	/**
	* Method to get the field input markup for a generic list.
	* Use the multiple attribute to enable multiselect.
	*
	* @return  string  The field input markup.
	*
	* @since   1.0.0
	*/
	protected function getInput()
	{
		$app = JFactory::getApplication('administrator');
		if($app->getUserState( "com_qazap.userinfo.address_type") == '') 
		{
			$presentVal = $this->value;
		} 
		else 
		{
			$presentVal = $app->getUserState( "com_qazap.userinfo.address_type");
		}
		$options = array();
		$onchange = $this->element['onchange'] ? ' onchange="' . $this->element['onchange'] . '"' : '';
		$AddressTypes = array("bt"=>JText::_('COM_QAZAP_BILLTO_ADDRESS') , "st"=>JText::_('COM_QAZAP_SHIPTO_ADDRESS'));
		foreach($AddressTypes as $key=>$value)
		{
			$options[] = JHtml::_('select.option', (string) $key, $value);
		}
		$html = JHtml::_('select.genericlist', $options, $this->name, trim($onchange), 'value', 'text', $presentVal, $this->id);

		return $html;
	}

}
