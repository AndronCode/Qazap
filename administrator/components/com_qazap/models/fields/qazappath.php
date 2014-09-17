<?php
/**
 * qazappath.php
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
class JFormFieldQazappath extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'Qazappath';


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
		// Translate placeholder text
		$hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$class        = !empty($this->class) ? ' class=" ' . $this->class . '"' : '';
		$readonly     = $this->readonly ? ' readonly' : '';
		$disabled     = $this->disabled ? ' disabled' : '';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$hint         = $hint ? ' placeholder="' . $hint . '"' : '';

		$path = $this->value;

		if(empty($path) || !is_dir($path))
		{
			$path = JPATH_ROOT . '/images';
		}

		// Initialize JavaScript field attributes.
		$onchange = '';

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);
		JHtml::_('script', 'administrator/components/com_qazap/assets/js/qazap.js', false, false);
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("
		jQuery(document).ready(function(){
			Qazap.validatePath('#$this->id');
		});
		");
		$doc->addStyleDeclaration("
			#{$this->id}-mark {
				color: #bd362f;
				font-size: 1.2em;
				vertical-align: middle;
			}
			#{$this->id}.invalid {
				border-color: #bd362f;
				color: #bd362f;
			}
		");

		$html = array();

		$html[] = '<input type="text" name="dummy" id="' . $this->id . '" value="' . htmlspecialchars($path, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $readonly . $hint . $onchange . $required . ' />';
		$html[] = '<span id="' . $this->id . '-mark" class="hasTooltip hide" title="Invalid"> <i class="icon-notification"></i></span>';
		$html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '_hidden" value="'	. htmlspecialchars($path, ENT_COMPAT, 'UTF-8') . '" />';			

		return implode($html);
	}
}
