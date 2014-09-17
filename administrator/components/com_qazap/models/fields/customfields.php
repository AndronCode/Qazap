<?php
/**
 * customfields.php
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

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 */
class JFormFieldCustomfields extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.0.0
	 */
	protected $type = 'customfields';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.0.0
	 */
	protected function getInput()
	{
		$multiple = $this->element['multiple'] ? ' multiple="' . (int) $this->element['multiple'] . '"' : '';
		
		$this->loadScripts();
		
		$options = $this->getOptions();
		$html = JHtml::_('select.genericlist', $options, $this->name, 'class="reset_on_select"'.$multiple, 'value', 'text', '', $this->id);		 
		
		return $html;
	}
	
	protected function getOptions()
	{
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_QAZAP_SELECT'));
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
					->select(array('a.id AS type_id', 'a.title'))
					->from('#__qazap_customfieldtype as a')
					->where('a.state = 1');
		$db->setQuery($query);
		$datas = $db->loadObjectList();
		if(empty($datas))
		{
			return $options;
		}
		foreach($datas as $data)
		{
			$options[] = JHtml::_('select.option', (int) $data->type_id, $data->title);
		}
		return $options;		
	}
	
	protected function loadScripts()
	{
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		if($app->isAdmin())
		{
			$url = JUri::base(true) . '/index.php?option=com_qazap&view=product&format=json&layout=field';
		}		
		else
		{
			$url = JUri::base(true) . '/index.php?option=com_qazap&view=form&format=json&layout=field';
		}
		$field_id = $this->id;
		$doc->addScriptDeclaration("
			function customFieldDelete() {
				var jq = jQuery;
				var delHTML = '<span class=\"delete-me\" onclick=\"return Qazap.deleteMe(this);\">Delete</span>';
				jq('#CustomFieldDetails .qzcustom-group').each(function(){
					if(jq(this).find('.controls').find('.delete-me').length == 0) {
						jq(this).find('.controls').append(delHTML);
					}					
				});			
			}
			function getQazapCustomField(thisInstance) {
				var typeid = jq(thisInstance).val();
				var ordering = 0;
				if(jq('#custom_field_details input[type=\"hidden\"].qzfield-ordering').length > 0) {
					jq('#custom_field_details input[type=\"hidden\"].qzfield-ordering').each(function() {
						ordering = jq(this).val();
						ordering = (parseInt(ordering)+ 1);
					});
				}
				if(typeid > 0) {					
					Qazap.addloader('#qazap-field-selector');
					jq.ajax({
						dataType: 'json',
						type: 'GET',
						url: '$url',
						data: 'typeid='+typeid+'&ordering='+ordering,
						success: function(e) {
							if(e.error) {
								var html = '<div class=\"qzerror-box\">'+e.html+'</div>';
								jq('#custom_field_details').append(html);
							} 
							else {
								var html = '<li>';
								html += '<table class=\"custom-field-table\">';
								html += '<tr class=\"field-row\">';
								html += '<td class=\"field-label\">'+e.title+'</td>';
								html += '<td class=\"field-html\">'+e.html+'</td>';
								html += '<td class=\"field-order\"><span class=\"field-sortable-handler\"><i class=\"icon-menu\"></i></span></td>';
								html += '<td class=\"field-delete\"><span class=\"delete-me\" onclick=\"return Qazap.deleteMe(this);\"><i class=\"icon-cancel\"></i></span></td>';
								html += '</tr>';
								html += '</table>';
								html += '</li>';
								jq('ul#custom_field_details').append(html);						
							}
							jq('#{$field_id}').trigger('liszt:updated');
							customFieldDelete();
							jq('#custom_field_details').trigger('sortupdate');
							Qazap.removeloader('#qazap-field-selector');							;
						}
					});					
				}
			}
			jq(document).ready(function(){				
				customFieldDelete();
				jq('#{$field_id}').chosen().change(function(){
					getQazapCustomField(this);
					jq(this).find('option:first').removeAttr('selected');
					jq(this).val('');
				});				
				jq('#custom_field_details').sortable();
				jq('#custom_field_details').bind('sortupdate', function(event, ui) {
					jq(this).find('input[type=\"hidden\"].qzfield-ordering').each(function(index,element) {
						jq(element).val(index);
					});
				});
				jq('#custom_field_details').disableSelection();				
			});
			jq(window).load(function(){
				jq('#{$field_id}_chzn').focus(function () {
					jq('select.reset_on_select').val('').trigger('change');
				});				
			});			
		");			
	}
}