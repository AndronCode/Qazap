<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a nested check box field listing user groups.
 * Multiselect is available by default.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldQazapUsergroup extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'QazapUsergroup';

	/**
	 * Method to get the user group field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$options = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= $this->size ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';
		$attr .= !empty($this->onclick) ? ' onclick="' . $this->onclick . '"' : '';


		if($this->readonly)
		{
			$done = false;
			$html = '';
			
			if(!empty($this->value))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.id, a.title')
					->from('#__usergroups AS a')
					->where('a.id = ' . (int) $this->value);
					
				$db->setQuery($query);
				$access = $db->loadObject();
				
				if(!empty($access))
				{
					$html .= '<input type="text" id="' . $this->id . '" disabled value="' . htmlspecialchars($access->title) . ' [' . $access->id . ']" />';
					$html .= '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" />';
					$done = true;
				}									
			}
			
			if($done === false)
			{
				$html .= '<input type="text" name="' . $this->name . '" id="' . $this->id . '" ' . $attr . ' value="' . $this->value . '" />';
			}
			
			return $html;
		}

		// Iterate through the children and build an array of options.
		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			$disabled = (string) $option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option['value'], trim((string) $option), 'value', 'text',
				$disabled
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		return JHtml::_('access.usergroup', $this->name, $this->value, $attr, $options, $this->id);
	}
}
