<?php
/**
 * qazapmanufacturercats.php
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
 * Form Field class for the Qazap Framework.
 *
 * @package     Qazap.Administrator
 * @subpackage  com_categories
 * @since       1.0.0
 */
class JFormFieldQazapManufacturerCats extends JFormFieldList
{
	/**
	* A flexible category list that respects access controls
	*
	* @var        string
	* @since   1.0.0
	*/
	public $type = 'QazapManufacturerCats';

	/**
	 * Method to get a list of categories that respects access controls and can be used for
	 * either category assignment or parent category assignment in edit screens.
	 * Use the parent element to indicate that the field will be used for assigning parent categories.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		$options = array();
		$published = $this->element['published'] ? $this->element['published'] : array(0, 1);
		$show_all = $this->element['show_all'] ? (bool) $this->element['show_all'] : false;
		
		$lang = JFactory::getLanguage();
		$multiple_language = count(JLanguageHelper::getLanguages()) > 1 ? true : false;
		$present_langauge = $lang->getTag();
		$default_language = $lang->getDefault();
		
		static $results = null;
		
		if($results === null)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('id, manufacturer_category_name AS title')
						->from('#__qazap_manufacturercategories');

			// Filter on the published state
			if (is_numeric($published))
			{
				$query->where('state = ' . (int) $published);
			}
			elseif (is_array($published))
			{
				JArrayHelper::toInteger($published);
				$query->where('state IN (' . implode(',', $published) . ')');
			}
			
			$query->order('ordering ASC');

			// Get the options
			try
			{
				$db->setQuery($query);
				$results = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}			
		}

		
		$options = array();
		
		if($show_all)
		{
			$options[] = JHtml::_('select.option', '0', JText::_('COM_QAZAP_ALL_CATEGORIES'));
		}		
		
		if(!empty($results))
		{
			foreach($results as $result)
			{
				$options[] = JHtml::_('select.option', (string) $result->id, JText::_($result->title));
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
