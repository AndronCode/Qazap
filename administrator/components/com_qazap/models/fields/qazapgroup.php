<?php
/**
 * qazapgroup.php
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

class JFormFieldQazapGroup extends JFormField
{
	protected $type = 'qazapgroup';

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
		$query = $db->getQuery(true)
					->select('a.id AS usergroup_id, a.title AS text')
					->from('#__usergroups AS a')
					->leftJoin('#__qazap_memberships AS b ON b.jusergroup_id = a.id')
					->where('b.state = 1 OR a.id = 1')
					->order('a.id ASC');
		$db->setQuery($query);	
		$qazapGroups = $db->loadObjectList();
		$options = array();
		foreach($qazapGroups as $qazapGroup)
		{
			$options[] = JHtml::_('select.option', (string) $qazapGroup->usergroup_id, $qazapGroup->text);
		}
		
		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->usergroup_id);
	}				
}
