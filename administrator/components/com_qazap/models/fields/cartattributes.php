<?php
/**
 * cartattributes.php
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
class JFormFieldCartattributes extends JFormField
{
	/**
	* The form field type.
	*
	* @var		string
	* @since   1.0.0
	*/
	protected $type = 'cartattributes';

	/**
	* Method to get the field input markup.
	*
	* @return	string	The field input markup.
	* @since   1.0.0
	*/
	protected function getInput()
	{		
		$this->loadActionScripts();
		
		$html = array();
		$multiple = $this->element['multiple'] ? ' multiple="' . (int) $this->element['multiple'] . '"' : '';

		$options = $this->getOptions();
		$html = JHtml::_('select.genericlist', $options, $this->name, 'class="reset_on_select"', 'value', 'text', '', $this->id);		 
		
		return $html;
	}
	
	protected function getOptions()
	{
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_QAZAP_SELECT'));
				
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
					->select(array('a.id', 'a.title'))
					->from('#__qazap_cartattributestype as a')
					->where('a.state = 1');
		$db->setQuery($query);
		$data = $db->loadObjectList();
		
		if(empty($data))
		{
			return $options;
		}		

		foreach($data as $item)
		{
			$options[] = JHtml::_('select.option', (int) $item->id, $item->title);
		}		
		return $options;
	}
	
	protected function loadActionScripts()
	{
		$doc = JFactory::getDocument();	
		$app = JFactory::getApplication();
		if($app->isAdmin())
		{
			$url = JUri::base(true) . '/index.php?option=com_qazap&view=product&format=json&layout=attribute';
		}		
		else
		{
			$url = JUri::base(true) . '/index.php?option=com_qazap&view=form&format=json&layout=attribute';
		}
		
		$field_id = $this->id;
		$doc->addScriptDeclaration("
			function cartAttrDelete() {				
				var delHTML = '<span class=\"delete-me\" onclick=\"return Qazap.deleteMe(this);\"><i class=\"icon-cancel\"></i></span>';
				jq('#CartAttributeDetails .qzcustom-group .html-row').each(function(){
					if(jq(this).find('td:last').find('.delete-me').length == 0) {
						jq(this).find('td:last').append(delHTML);
					}					
				});			
			}			
			function getQazapCartAttr(thisInstance) {				
				var typeid = jq(thisInstance).find('option:selected').val() ;
				var ordering = 0;
				if(jq('#CartAttributeDetails input[type=\"hidden\"].qzattribute-ordering').length > 0) {
					var maxOrdering = 0;
					jq('#CartAttributeDetails input[type=\"hidden\"].qzattribute-ordering').each(function() {
						if(jq(this).val() > maxOrdering)
						{
							maxOrdering = jq(this).val();
						}
						ordering = (parseInt(maxOrdering)+ 1);
					});
				}
				if(typeid > 0) {
					Qazap.addloader('#qazap-attr-selector');
					jq.ajax({
						dataType: 'json',
						type: 'get',
						url: '$url',
						data: 'typeid='+typeid+'&ordering='+ordering,
						success: function(e) {
							if(e.error) {
								var html = '<div class=\"qzerror-box\">'+e.html+'</div>';
								jq('#CartAttributeDetails').append(html);
							} 
							else {
								if(jq('#qzcustom-group-'+typeid).length) {
									jq('#qzcustom-group-'+typeid).append(e.html);
								}
								else {
									var html = '<li>';
									html += '<table id=\"qzcustom-group-'+typeid+'\" class=\"qzcustom-group table table-striped table-bordered\">';
									html += e.title;
									html += e.header;
									html += e.html;
									html += '</table>';
									html += '</li>';
									jq('ul#CartAttributeDetails').append(html);										
								}							
							}
							jq('#{$field_id}').trigger('liszt:updated');
							cartAttrDelete();
							jq('#CartAttributeDetails').trigger('sortupdate');
							Qazap.removeloader('#qazap-attr-selector');
						}
					});					
				}
			}
			jq(document).ready(function(){
				jq('#{$field_id}').chosen().change(function(){
					getQazapCartAttr(this);
					jq(this).find('option:first').removeAttr('selected');
					jq(this).val('');
				});
				cartAttrDelete();
				jq('#CartAttributeDetails').sortable();
				jq('#CartAttributeDetails').bind('sortupdate', function(event, ui) {
					jq(this).find('input[type=\"hidden\"].qzattribute-ordering').each(function(index,element) {
						jq(element).val(index);
					});
				});
				jq('#CartAttributeDetails').disableSelection();				
			});
			jq(window).load(function(){
				jq('#{$field_id}_chzn').focus(function () {
					jq('select.reset_on_select').val('').trigger('change');
				});				
			});
		");			
	}
}