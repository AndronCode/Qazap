<?php
/**
 * qazapcategoryedit.php
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
class JFormFieldQazapCategoryEdit extends JFormFieldList
{
	/**
	* A flexible category list that respects access controls
	*
	* @var        string
	* @since   1.0.0
	*/
	public $type = 'QazapCategoryEdit';

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
		$name = (string) $this->element['name'];

		// Let's get the id for the current item, either category or content item.
		$jinput = JFactory::getApplication()->input;
		// Load the category options for a given extension.

		// For categories the old category is the category id or 0 for new category.
		if ($this->element['parent'] || $jinput->get('option') == 'com_qazap')
		{
			$oldCat = $jinput->get('category_id', 0);
			$oldParent = $this->form->getValue($name, 0);
		}
		else
			// For items the old category is the category they are in when opened or 0 if new.
		{
			$oldCat = $this->form->getValue($name, 0);
		}
		
		$lang = JFactory::getLanguage();
		$multiple_language = count(JLanguageHelper::getLanguages()) > 1 ? true : false;
		$present_langauge = $lang->getTag();
		$default_language = $lang->getDefault();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
					->select('a.category_id AS value, a.level, a.published')
					->from('#__qazap_categories AS a')
					->join('LEFT', $db->quoteName('#__qazap_categories') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
			
		if($multiple_language)
		{
			$query->select('d.title AS text')
					->join('LEFT','#__qazap_category_details AS d ON d.category_id = a.category_id AND ((d.language = ' . $db->quote($present_langauge) .' AND d.title != null) OR (d.language = ' . $db->quote($default_language) .'))');
		}
		else
		{
			$query->select('d.title AS text');
			$query->join('LEFT','#__qazap_category_details AS d ON d.category_id = a.category_id AND d.language = ' . $db->quote($present_langauge));
		}
			
		// If parent isn't explicitly stated but we are in com_categories assume we want parents
		if ($oldCat != 0 && ($this->element['parent'] == true || $jinput->get('option') == 'com_qazap'))
		{
			// Prevent parenting to children of this item.
			// To rearrange parents and children move the children up, not the parents down.
			$query->join('LEFT', $db->quoteName('#__qazap_categories') . ' AS p ON p.category_id = ' . (int) $oldCat)
					->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');

			$rowQuery = $db->getQuery(true);
			$rowQuery->select('a.category_id AS value, a.level, a.parent_id')
						->from('#__qazap_categories AS a');
				
			if($multiple_language)
			{
				$rowQuery->select('IF(b.title IS NULL, bb.title, b.title) AS text');
				$rowQuery->join('LEFT','#__qazap_category_details AS b ON b.category_id = a.category_id AND b.language = ' . $db->quote($present_langauge));
				$rowQuery->join('LEFT','#__qazap_category_details AS bb ON bb.category_id = a.category_id AND bb.language = ' . $db->quote($default_language));
			}
			else
			{
				$rowQuery->select('b.title AS text');
				$rowQuery->join('LEFT','#__qazap_category_details AS b ON b.category_id = a.category_id AND b.language = ' . $db->quote($default_language));
			}			
				
			$rowQuery->where('a.category_id = ' . (int) $oldCat);
			
			$db->setQuery($rowQuery);
			$row = $db->loadObject();
		}

		// Filter on the published state

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$query->where('a.published IN (' . implode(',', $published) . ')');
		}

		$query->group('a.category_id, a.level, a.published');
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			// Translate ROOT
			if ($this->element['parent'] == true || $jinput->get('option') == 'com_qazap')
			{
				if ($options[$i]->level == 0)
				{
					$options[$i]->text = JText::_('JGLOBAL_ROOT_PARENT');
				}
			}
			if ($options[$i]->published == 1)
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
			}
			else
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . '[' . $options[$i]->text . ']';
			}
		}

		// Get the current user object.
		$user = JFactory::getUser();

		// For new items we want a list of categories you are allowed to create in.
		if ($oldCat == 0)
		{
			foreach ($options as $i => $option)
			{
				// To take save or create in a category you need to have create rights for that category
				// unless the item is already in that category.
				// Unset the option if the user isn't authorised for it. In this field assets are always categories.
				if ($user->authorise('core.create', 'com_qazap.category.' . $option->value) != true)
				{
					unset($options[$i]);
				}
			}
		}
		// If you have an existing category id things are more complex.
		else
		{
			// If you are only allowed to edit in this category but not edit.state, you should not get any
			// option to change the category parent for a category or the category for a content item,
			// but you should be able to save in that category.
			foreach ($options as $i => $option)
			{
				if ($user->authorise('core.edit.state', 'com_qazap.category.' . $oldCat) != true && !isset($oldParent))
				{
					if ($option->value != $oldCat)
					{
						unset($options[$i]);
					}
				}
				if ($user->authorise('core.edit.state', 'com_qazap.category.' . $oldCat) != true
					&& (isset($oldParent))
					&& $option->value != $oldParent
				)
				{
					unset($options[$i]);
				}

				// However, if you can edit.state you can also move this to another category for which you have
				// create permission and you should also still be able to save in the current category.
				if (($user->authorise('core.create', 'com_qazap.category.' . $option->value) != true)
					&& ($option->value != $oldCat && !isset($oldParent))
				)
				{
					{
						unset($options[$i]);
					}
				}
				if (($user->authorise('core.create', 'com_qazap.category.' . $option->value) != true)
					&& (isset($oldParent))
					&& $option->value != $oldParent
				)
				{
					{
						unset($options[$i]);
					}
				}
			}
		}
		if (($this->element['parent'] == true || $jinput->get('option') == 'com_qazap')&& (isset($row) && !isset($options[0]))&& isset($this->element['show_root']))
		{
			if ($row->parent_id == '1')
			{
				$parent = new stdClass;
				$parent->text = JText::_('JGLOBAL_ROOT_PARENT');
				array_unshift($options, $parent);
			}
			array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
