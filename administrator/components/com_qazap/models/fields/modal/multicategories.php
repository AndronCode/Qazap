<?php
/**
 * multicategories.php
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
 * Supports a modal multicategory picker.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.0.0
 */
class JFormFieldModal_MultiCategories extends JFormField
{
	/**
	* The form field type.
	*
	* @var		string
	* @since   1.6
	*/
	protected $type = 'Modal_MultiCategories';

	/**
	* Method to get the field input markup.
	*
	* @return  string	The field input markup.
	* @since   1.6
	*/
	protected function getInput()
	{
		$allowEdit		= ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear		= ((string) $this->element['clear'] != 'false') ? true : false;
		$defaultText	= (isset($this->element['defaultext'])) ? JText::_($this->element['defaultext']) : JText::_('COM_QAZAP_SELECT');
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_language = $lang->getTag();
		$default_language = $lang->getDefault();

		// Load language
		JFactory::getLanguage()->load('com_qazap', JPATH_ADMINISTRATOR);

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectMultiCategories_'.$this->id.'(category_id, title) {';
		$script[] = '		document.getElementById("'.$this->id.'_id").value = category_id;';
		$script[] = '		document.getElementById("'.$this->id.'_name").value = title;';

		if ($allowEdit)
		{
			$script[] = '		jQuery("#'.$this->id.'_edit").removeClass("hidden");';
		}

		if ($allowClear)
		{
			$script[] = '		jQuery("#'.$this->id.'_clear").removeClass("hidden");';
		}

		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = '		function jClearMultiCategories(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "'.htmlspecialchars(JText::_('COM_QAZAP_ALL_CATEGORIES', true), ENT_COMPAT, 'UTF-8').'";';
			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		
		$html	= array();
		if(JFactory::getApplication()->isAdmin())
		{
			$link	= 'index.php?option=com_qazap&amp;view=categories&amp;layout=multimodal&amp;tmpl=component&amp;function=jSelectMultiCategories_'.$this->id;
		}
		else
		{
			$link	= 'index.php?option=com_qazap&amp;view=multicategories&amp;layout=modal&amp;tmpl=component&amp;function=jSelectMultiCategories_'.$this->id;
		}
		
		$title = '';
		if (isset($this->element['language']))
		{
			$link .= '&amp;forcedLanguage='.$this->element['language'];
		}

		$this->value = (array) $this->value;
		$this->value = array_filter($this->value);
		
		
		
		if (!empty($this->value))
		{
			$db	= JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('group_concat(title separator ", ")')
				->from($db->quoteName('#__qazap_category_details'))
				->where($db->quoteName('category_id') . ' IN (' . implode(',', $this->value) . ')');
			if($multiple_language)
			{
				$query->where('language = '.$db->quote($present_language));				
			}
			else
			{
				$query->where('language = '.$db->quote($default_language));
			}	
				
			$db->setQuery($query);	
			try
			{
				$db->setQuery($query);
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}			
		}

		if (empty($title))
		{
			$title = $defaultText;
		}
		
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		//print_r($this->value);exit;
		// The active article id field.
		if (is_array($this->value))
		{
			$value = implode(',', $this->value);
		}
		else
		{
			$value = 0;
		}

		// The current article display field.
		$html[] = '<textarea id="'.$this->id.'_name" readonly="true" rows="5">'.$title.'</textarea>';
		$html[] = '<span class="btn-group">';		
		$html[] = '<a class="modal btn hasTooltip" title="'.JHtml::tooltipText('COM_QAZAP_CHANGE_CATEGORY').'"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> '.JText::_('COM_QAZAP_GLOBAL_SELECT').'</a>';

		// Clear article button
		if ($allowClear)
		{
			$html[] = '<button id="'.$this->id.'_clear" class="btn'.($value ? '' : ' hidden').'" onclick="return jClearMultiCategories(\''.$this->id.'\')"><span class="icon-remove"></span> ' . JText::_('COM_QAZAP_GLOBAL_CLEAR') . '</button>';
		}

		$html[] = '</span>';

		// class='required' for client side validation
		$class = '';
		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}
		//qzdump($this->name);exit;
		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return implode("\n", $html);
	}
}
