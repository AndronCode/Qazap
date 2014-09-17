<?php
/**
 * vendorgroup.php
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

class JFormFieldVendorgroup extends JFormField
{

	protected $type = 'Vendorgroup';

	protected function getInput()
	{	
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';		
	
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			->select(array('a.vendor_group_id', 'a.title', 'a.commission'))
			->from('`#__qazap_vendor_groups` AS a')
			->where('a.state = 1');
		$db->setQuery($sql);
		$vendorGroups = $db->loadObjectList();
		
		$json_data = json_encode($vendorGroups);
		$this->loadScripts($json_data);
		
		$options = array();			
		$options[] = JHtml::_('select.option', '', JText::_('COM_QAZAP_SELECT'));
		foreach($vendorGroups as $vendorGroup)
		{
			$options[] = JHtml::_('select.option', (int) $vendorGroup->vendor_group_id, $vendorGroup->title);
		}
		
		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);	}
	
	protected function loadScripts($json_data) {
		$id = $this->id;
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("
			if(typeof jq === 'undefined') {
				jq = jQuery.noConflict();
			}
			jq(document).ready(function(){
				var cval = jq('#$id').val();
				var commission = '';
				jq.each( $json_data, function( key, value ) {					
					if(value['vendor_group_id'] == cval) {
						commission = value['commission'];						
					}
				});
				if(commission) commission = commission +'%';
				jq('#vendor_commission').val(commission);			
				
				jq('#$id').change(function(){
					var cval = jq(this).val();
					var commission = '';
					jq.each( $json_data, function( key, value ) {					
						if(value['vendor_group_id'] == cval) {
							commission = value['commission'];						
						}
					});
					if(commission) commission = commission +'%';
					jq('#vendor_commission').val(commission);
					
				});
				
			});
		");		
	}
}
