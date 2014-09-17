<?php
/**
 * qzselect.php
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
defined('JPATH_BASE') or die;

/**
 * Utility class for categories
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.5
 */
abstract class JHtmlQzselect
{
	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	public static function checklist($options, $name, $attribs, $value_key, $text_key, $checkedOptions, $id, $required = '')
	{
		$html = array();

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		// Start the checkbox field output.
		$html[] = '<fieldset id="' . $id . '"' . $attribs . ' ' . $required . '>';

		// Build the checkbox field output.
		$html[] = '<ul>';

		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = (in_array((string) $option->$value_key, (array) $checkedOptions) ? ' checked' : '');

			$checked = empty($checked) && $option->checked ? ' checked' : $checked;
			
			$disabled = !empty($option->disable) ? ' disabled' : '';
			$class = !empty($option->class) ? ' class="' . $option->class . $disabled . '"' : (!empty($disabled) ? ' class="disabled"' : '');

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

			$html[] = '<li>';	
			$html[] = '<label for="' . $id . $i . '"' . $class . '>';		
			$html[] = '<input type="checkbox" id="' . $id . $i . '" name="' . $name . '" value="'
				. htmlspecialchars($option->$value_key, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $onchange . $disabled . '/>';			
			$html[] = JText::_($option->$text_key) . '</label>';
			$html[] = '</li>';
		}

		$html[] = '</ul>';

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}
}
