<?php
/**
 * qazapvendorpayments.php
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
JFormHelper::loadFieldClass('list');
/**
 * Supports a modal article picker.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.0.0
 */
class JFormFieldQazapvendorpayments extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var		string
	* @since   1.0.0
	*/
	protected $type = 'Qazapvendorpayments';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
					->select('extension_id,name')
					->from('`#__extensions`')
					->where('folder = '.$db->quote('qazapvendorpayment'));
		$db->setQuery($sql);
		$extensions = $db->loadObjectList();
		$options = array();
		
		if(!empty($extensions))
		{
			foreach($extensions as $extension)
			{
				$options[] = JHtml::_('select.option', (int) $extension->extension_id, $extension->name);
			}	
		}		
		
		array_unshift($options, JHtml::_('select.option', 'm', JText::_('COM_QAZAP_PAYMENT_MANUAL')));
		
		$options = array_merge(parent::getOptions(), $options);
		
		return $options;		
	}
	
	protected function getInput()
	{
		$payment_id = $this->form->getValue('payment_id', 0);
		
		if(!$payment_id)
		{
			$this->loadScripts();
		}		
		
		$this->readonly = $payment_id ? true : false;
		
		return parent::getInput();
	}
	
	protected function loadScripts()
	{
		$doc = JFactory::getDocument();
		$id = $this->id;
		$url = JRoute::_('');
		$doc->addScriptDeclaration("
		
			function getPaymentParams(extension_id)
			{
				if(!extension_id)
				{
					return;
				}
				
				jq.ajax({
					type: 'GET',
					url: 'index.php?option=com_qazap&view=payment&layout=params&format=raw&extension_id='+extension_id,
					dataType: 'html',
					cache: false,
					beforeSend: function() {
						Qazap.addloader('#paymentmethod-selector');
					},
					success: function(data) {
						jq('#paymentmethod-params').html(data);
						Qazap.removeloader('#paymentmethod-selector');
					},
					error: function() {
						alert('Params could not be loaded');
					}
				})
			}
			jq(document).ready(function(){
				Qazap.spinnervars();
				jq('#$id').change(function(){
					getPaymentParams(jq(this).val());
				});	
			});	
		");
	}
}
