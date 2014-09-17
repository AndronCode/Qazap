<?php
/**
 * cartattrtypes.php
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
class JFormFieldCartattrtypes extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	protected $type = 'cartattrtypes';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since   1.0.0
	 */
	protected function getInput()
	{		
		$html = array();			
		
		if($this->value && $this->form->getValue('id', 0)) 
		{
			$plugin = QazapHelper::getPlugin($this->value);
			
			$html[] = '<input type="text" name="type'.$this->name.'" id="type'.$this->id.'" value="'.$plugin->name.'" disabled="disabled" />';	
			$html[] = '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->value.'" />';							
		} 
		else 
		{
			$this->getScripts();
			$options = $this->getOptions();
			$attr = 'required="true" onchange="return getQazapAttributeParams(this);"';	
			$html[] = JHtml::_('select.genericlist', $options, $this->name, $attr, 'value', 'text', $this->value);							
		}
			
		return implode($html);
	}
	
	protected function getOptions()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
					->select(array('e.extension_id','e.name'))
					->from('#__extensions as e')
					->where('e.folder = '. $db->quote('qazapcartattributes'))
					->where('e.enabled = 1')
					->where('e.type = ' . $db->quote('plugin'))
					->where('e.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		$db->setQuery($query);
		$datas = $db->loadObjectList();
		
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('JSELECT'));
		
		if(count($datas))
		{
			foreach($datas as $data)
			{
				$options[] = JHtml::_('select.option', (string) $data->extension_id, $data->name);
			}				
		}
		
		return $options;	
	}
	
	protected function getScripts()
	{
		$doc = JFactory::getDocument();
	
		$doc->addScriptDeclaration("
			function getQazapAttributeParams(field) {
				var id = jq(field).val();
				if(id > 0) {
					jq.ajax({
						type: 'GET',
						dataType: 'html',
						url: window.qzuri,
						data: 'option=com_qazap&view=cartattributestype&format=raw&layout=params&plugin_id='+id,
						beforeSend: function() {
							Qazap.addloader(jq(field).parent());
						},
						success: function(data) {
							jq('#cartAttrParams').html(data);
							Qazap.removeloader(jq(field).parent());
						},
						error: function() {
							Qazap.removeloader(jq(field).parent());
						},
						complete: function() {
							jq('.radio.btn-group label').addClass('btn');
							jq('.btn-group label:not(.active)').click(function()
							{
								var label = jq(this);
								var input = jq('#' + label.attr('for'));

								if (!input.prop('checked')) {
									label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
									if (input.val() == '') {
										label.addClass('active btn-primary');
									} else if (input.val() == 0) {
										label.addClass('active btn-danger');
									} else {
										label.addClass('active btn-success');
									}
									input.prop('checked', true);
								}
							});
							jq('.btn-group input[checked=checked]').each(function()
							{
								if (jq(this).val() == '') {
									jq('label[for=' + jq(this).attr('id') + ']').addClass('active btn-primary');
								} else if (jq(this).val() == 0) {
									jq('label[for=' + jq(this).attr('id') + ']').addClass('active btn-danger');
								} else {
									jq('label[for=' + jq(this).attr('id') + ']').addClass('active btn-success');
								}
							});
						}
					});					
				}
			}		
		");			
	}
}