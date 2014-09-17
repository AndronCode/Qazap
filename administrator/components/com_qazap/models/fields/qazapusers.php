<?php
/**
 * qazapusers.php
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
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldQazapUsers extends JFormField
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  11.1
	*/
	protected $type = 'QazapUsers';

	/**
	* Method to get the field input markup for a generic list.
	* Use the multiple attribute to enable multiselect.
	*
	* @return  string  The field input markup.
	*
	* @since   11.1
	*/
	protected function getInput()
	{
		$label = $this->element['label'] ? (string) $this->element['label'] : '';
		$options = array();
		$app = JFactory::getApplication();
		$attr = '';
		$db = JFactory::getDBO();
		
		ob_start(); 
		if(empty($this->value)) 
		{ 
			$sql = $db->getQuery(true)
			->select(array('id', 'name', 'username'))
			->from('`#__users`');
			$db->setQuery($sql);
			$users = $db->loadObjectList();
			foreach($users as $user)
			{
				$options[] = JHtml::_('select.option', (string) $user->id, $user->name);
			} 
		?>		

			<div class="control-group">
			<div class="control-label"><?php echo JText::_($label) ?></div>
			<div class="controls"><?php echo JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id) ?></div>
			</div>
	<?php 
		} 
		else 
		{ 
			$sql = $db->getQuery(true)
			     ->select(array('a.id', 'a.name', 'a.username'))
				 ->from('`#__users` AS a')
				 ->where('a.id = '.$this->value);
			$db->setQuery($sql);
			$user = $db->loadObject();	
			?>
			<div class="control-group">
				<div class="control-label"><?php echo JText::_('COM_QAZAP_USERFIELD_NAME') ?></div>
				<div class="controls"><?php echo $user->name; ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo JText::_('COM_QAZAP_USERFIELD_USERNAME') ?></div>
				<div class="controls"><?php echo $user->username; ?></div>
			</div>
			<input type="hidden" name="<?php echo $this->name ?>" id="<?php echo $this->id ?>" value="<?php echo $this->value ?>" />		
	<?php 
		}		
		$html = ob_get_clean();
		return $html;
	}
}
