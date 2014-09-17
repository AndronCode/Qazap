<?php
/**
 * qazapcategory.php
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

/**
 * Utility class for categories
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.5
 */
abstract class JHtmlQazapcategory
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  1.5
	 */
	protected static $items = array();

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option e.g. com_something.
	 * @param   array   $config     An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function options($config = array('filter.published' => array(0, 1), 'skip' => null))
	{
		$hash = md5(serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$lang = JFactory::getLanguage();
			$presentLang = $lang->getTag();
			$defaultLang = $lang->getDefault();
				
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.category_id, b.title, a.level')
				->from('#__qazap_categories AS a')
				->join('LEFT','#__qazap_category_details AS b ON b.category_id = a.category_id AND ((b.language = ' . $db->quote($presentLang) .' AND b.title != null) OR (b.language = ' . $db->quote($defaultLang) .'))')
				->where('a.parent_id > 0');

			if(isset($config['skip']) && is_numeric($config['skip']))
			{
				$query->where('a.category_id != ' . (int) $config['skip']);
			}

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					JArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			// Filter on the language
			if (isset($config['filter.language']))
			{
				if (is_string($config['filter.language']))
				{
					$query->where('b.language = ' . $db->quote($config['filter.language']));
				}
				elseif (is_array($config['filter.language']))
				{
					foreach ($config['filter.language'] as &$language)
					{
						$language = $db->quote($language);
					}

					$query->where('b.language IN (' . implode(',', $config['filter.language']) . ')');
				}
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				static::$items[$hash][] = JHtml::_('select.option', $item->category_id, $item->title);
			}
		}

		return static::$items[$hash];
	}

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option.
	 * @param   array   $config     An array of configuration options. By default, only published and unpublished categories are returned.
	 *
	 * @return  array   Categories for the extension
	 *
	 * @since   1.6
	 */
	public static function categories($config = array('filter.published' => array(0, 1), 'skip' => null))
	{
		$hash = md5(serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$lang = JFactory::getLanguage();
			$presentLang = $lang->getTag();
			$defaultLang = $lang->getDefault();
						
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.category_id, b.title, a.level, a.parent_id')
				->from('#__qazap_categories AS a')
				->join('LEFT','#__qazap_category_details AS b ON b.category_id = a.category_id AND ((b.language = ' . $db->quote($presentLang) .' AND b.title != null) OR (b.language = ' . $db->quote($defaultLang) .'))')
				->where('a.parent_id > 0');
			
			if(isset($config['skip']) && is_numeric($config['skip']))
			{
				$query->where('a.category_id != ' . (int) $config['skip']);
			}

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					JArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				static::$items[$hash][] = JHtml::_('select.option', $item->category_id, $item->title);
			}
			// Special "Add to root" option:
			static::$items[$hash][] = JHtml::_('select.option', '1', JText::_('JLIB_HTML_ADD_TO_ROOT'));
		}

		return static::$items[$hash];
	}
}
