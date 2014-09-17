<?php
/**
 * qazapattributes.php
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
 * Supports an HTML select list of categories
 */
class JFormFieldQazapattributes extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var		string
	* @since	1.0.0
	*/
	protected $type = 'Qazapattributes';

	
	protected function getOptions()
	{
		$options = array();
		$useglobal = $this->element['useglobal'] ? (bool) $this->element['useglobal'] : false;
		
		if($useglobal)
		{
			$options[] = JHtml::_('select.option', '', JText::_('JGLOBAL_USE_GLOBAL'));
		}
		
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
		
		$options = array_merge(parent::getOptions(), $options);
			
		return $options;
	}
}