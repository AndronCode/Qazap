<?php
/**
 * qzbatch.php
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
defined('JPATH_BASE') or die;

/**
 * Utility class for categories
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.5
 */
abstract class JHtmlQzbatch
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  1.5
	 */
	protected static $options = null;

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option e.g. com_something.
	 * @param   array   $config     An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function orderStatusOptions($select = false)
	{
		if (static::$options == null)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
							->select('status_code, status_name')
							->from('#__qazap_order_status');
			$db->setQuery($query);
			$options = $db->loadObjectList();

			// Assemble the list options.
			static::$options = array();
			
			if($select)
			{
				static::$options[] = JHtml::_('select.option', '', '- ' . JText::_('JSELECT'));
			}

			foreach ($options as &$option)
			{
				static::$options[] = JHtml::_('select.option', (string) $option->status_code, JText::_($option->status_name));
			}
		}

		return static::$options;
	}
	
	public static function orderstatus()
	{
		$options = self::orderStatusOptions(true);
		
		return JHtml::_('select.genericlist', $options, 'batch[order_status]', '', 'value', 'text', '', 'batch-order-status');
	}
	
	public static function applytoall()
	{
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('JNO'), 'value', 'text', false);
		$options[] = JHtml::_('select.option', 1, JText::_('JYES'), 'value', 'text', false);
		
		$html = array();
		$html[] = '<fieldset id="batch-apply-to-all" class="form-inline">';
		// Build the radio field output.
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = ($i == 0) ? ' checked="checked"' : '';
			$class = ' class="radio"';
			
			$html[] = '<label for="batch-apply-to-all-' . $i . '"' . $class . ' >';
			$html[] = '<input type="radio" id="batch-apply-to-all-' . $i . '" name="batch[apply_to_all_orders]" value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . ' />';			
			$html[] = JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', 'batch[apply_to_all]')) . '</label>&nbsp;&nbsp;&nbsp;';

		}

		// End the radio field output.
		$html[] = '</fieldset>';			
		return implode($html);
	}	
	
	public static function comment()
	{
		$html = '<textarea id="batch-comment" name="batch[comment]" rows="5"></textarea>';
		
		return $html;
	}
	
	public static function notify()
	{
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('JNO'), 'value', 'text', false);
		$options[] = JHtml::_('select.option', 1, JText::_('JYES'), 'value', 'text', false);
		
		$html = array();
		$html[] = '<fieldset id="batch-notify" class="form-inline">';
		// Build the radio field output.
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = ($i == 0) ? ' checked="checked"' : '';
			$class = ' class="radio"';
			
			$html[] = '<label for="batch-notify-' . $i . '"' . $class . ' >';
			$html[] = '<input type="radio" id="batch-notify-' . $i . '" name="batch[notify]" value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . ' />';			
			$html[] = JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', 'batch[notify]')) . '</label>&nbsp;&nbsp;&nbsp;';

		}

		// End the radio field output.
		$html[] = '</fieldset>';			
		return implode($html);
	}		

}
