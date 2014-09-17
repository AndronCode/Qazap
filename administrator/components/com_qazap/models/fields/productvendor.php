<?php
/**
 * productvendor.php
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
 * Form Field class for the Qazap Platform.
 * Supports a generic list of options.
 *
 * @package     Qazap.Platform
 * @subpackage  Form
 * @since       1.0.0
 */
class JFormFieldProductVendor extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'ProductVendor';

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
		$multiple = $this->element['multiple'] ? ' multiple="' . (int) $this->element['multiple'] . '"' : '';
		$options = array();
		$attr = '';
		$db = JFactory::getDBO();
		$sql = "SELECT id, shop_name from #__qazap_vendor";
		$db->setQuery($sql);
		$vendors = $db->loadObjectList();
		foreach($vendors as $vendor)
		{
			$options[] = JHtml::_('select.option', (string) $vendor->id, $vendor->shop_name);
		}
		$html = JHtml::_('select.genericlist', $options, $this->name, trim($multiple), 'value', 'text', $this->value, $this->id);

		return $html;
	}
}
