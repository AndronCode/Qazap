<?php
/**
 * @package     Qazap.Admin
 *
 * @copyright   Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');
/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       11.1
 */
class JFormFieldQazapTags extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'QazapTags';	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$doc = JFactory::getDocument();
		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);		
		$doc->addScript(JUri::base(true) . '/components/com_qazap/assets/js/angular.min.js');
		JHtml::_('bootstrap.framework');
		$doc->addScript(JUri::base(true) . '/components/com_qazap/assets/js/bootstrap-tagsinput.js');
		$doc->addScript(JUri::base(true) . '/components/com_qazap/assets/js/bootstrap-tagsinput-angular.js');
		$doc->addStyleSheet(JUri::base(true) . '/components/com_qazap/assets/css/bootstrap-tagsinput.css');
		$doc->addScriptDeclaration("
		jQuery(function($) {
			$('input[data-role=qazaptags]').tagsinput({
					trimValue: true,
					allowDuplicates: false				  
			});
		});
		");

		$html = parent::getInput();
		$html = str_replace('type="text"', 'type="text" data-role="qazaptags"', $html);
		
		return $html;		
	}

}
