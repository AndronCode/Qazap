<?php
/**
 * length.php
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
defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldLength extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  11.1
	*/
	protected $type = 'Length';

	/**
	* Method to get the field input markup for a generic list.
	* Use the multiple attribute to enable multiselect.
	*
	* @return  string  The field input markup.
	*
	* @since   11.1
	*/
	protected function getInput()
	{
		$options = array();
		$attr = '';
		$db = JFactory::getDBO();
		$sql = "SELECT id, product_measure_unit_name from #__qazap_product_uom WHERE product_attributes='length'";
		$db->setQuery($sql);
		$lengths = $db->loadObjectList();
		foreach($lengths as $length)
		{
			$options[] = JHtml::_('select.option', (string) $length->id, $length->product_measure_unit_name);
		}
		$html = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);

		return $html;
	}
}
