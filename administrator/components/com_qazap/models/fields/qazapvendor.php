<?php
/**
 * qazapvendor.php
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
 * Supports a modal article picker.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.0.0
 */
class JFormFieldQazapvendor extends JFormField
{
	/**
	* The form field type.
	*
	* @var		string
	* @since   1.0.0
	*/
	protected $type = 'Qazapvendor';

	/**
	* Method to get the field input markup.
	*
	* @return  string	The field input markup.
	* @since   1.0.0
	*/
	protected function getInput()
	{
		$paymentForm	= ((string) $this->element['paymentform'] == 'true') ? true : false;
		$allowEdit		= ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear		= ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		JFactory::getLanguage()->load('com_qazap', JPATH_ADMINISTRATOR);

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();		

		// Select button script
		$script[] = '	function jSelectVendor_'.$this->id.'(id, title) {';
		$script[] = '		document.getElementById("'.$this->id.'_id").value = id;';
		$script[] = '		document.getElementById("'.$this->id.'_name").value = title;';
		
		if($paymentForm)
		{
			$script[] = "
			jq.getJSON('index.php?option=com_qazap&view=payment&layout=history&vendor_id='+id+'&format=json', 
				function(data) {
					console.log(data);
					jq.each(data, function(index, value){
						if(jq('#jform_'+index).length)
						{
							jq('#jform_'+index).val(value);
						}
					});			
				});
			";
		}		
		
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

			$script[] = '		function jClearVendor(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "'.htmlspecialchars(JText::_('COM_QAZAP_PAYMENT_NOTICE_PLEASE_SELECT_VENDOR', true), ENT_COMPAT, 'UTF-8').'";';
			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_qazap&amp;view=vendors&amp;layout=modal&amp;tmpl=component&amp;function=jSelectVendor_'.$this->id;
		
		$title = '';

		if($this->value)
		{
			$db	= JFactory::getDbo();			
			$query = $db->getQuery(true)
				->SELECT ('shop_name')
				->from('#__qazap_vendor')
				->where('id = '. $this->value);
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
			$title = JText::_('COM_QAZAP_PAYMENT_NOTICE_PLEASE_SELECT_VENDOR');
		}
		
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		$payment_id = $this->form->getValue('payment_id', 0);
		
		// The current article display field.
		$html[] = '<span class="input-append">';		
		$html[] = '<input type="text" id="'.$this->id.'_name" disabled="disabled" value="'.$title.'" />';
		
		if(!$payment_id)
		{
			$html[] = '<a class="modal btn hasTooltip" title="'.JHtml::tooltipText('COM_QAZAP_CHANGE_VENDOR').'"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> '.JText::_('JSELECT').'</a>';
			$html[] = '<button id="'.$this->id.'_clear" class="btn'.($this->value ? '' : ' hidden').'" onclick="return jClearVendor(\''.$this->id.'\')"><span class="icon-remove"></span> ' . JText::_('JCLEAR') . '</button>';
		}		


		$html[] = '</span>';

		// class='required' for client side validation
		$class = '';
		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$this->value.'" />';

		return implode("\n", $html);
	}
}
