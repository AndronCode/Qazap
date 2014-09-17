<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JAccess
 * @since       11.1
 */
class JFormFieldQazapAccessLevel extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'QazapAccessLevel';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= $this->readonly ? ' readonly="true"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';		
		
		if($this->readonly)
		{
			$done = false;
			$html = '';
			
			if(!empty($this->value))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.id, a.title')
					->from('#__viewlevels AS a')
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

		// Get the field options.
		$options = $this->getOptions();		

		return JHtml::_('access.level', $this->name, $this->value, $attr, $options, $this->id);
	}
}
