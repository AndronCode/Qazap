<?php
/**
 * qazapproduct.php
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
abstract class JHtmlQazapproduct
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
	public static function products($config = array('filter.published' => array(0, 1)))
	{
		$config = (array) $config;
		$hash = md5(serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$lang = JFactory::getLanguage();
			$multiple_language = JLanguageMultilang::isEnabled();
			$present_language = $lang->getTag();
			$default_language = $lang->getDefault();				
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
					->select('p.product_id, p.parent_id, p.category_id, p.vendor AS vendor_id')
					->from('#__qazap_products AS p');

			if($multiple_language)
			{
				$query->select('CASE WHEN pd.product_name IS NULL THEN pdd.product_name ELSE pd.product_name END AS product_name');			
			
				$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = p.product_id AND pd.language = '.$db->quote($present_language));
				$query->join('LEFT', '#__qazap_product_details AS pdd ON pdd.product_id = p.product_id AND pdd.language = '.$db->quote($default_language));				
			}
			else
			{
				$query->select('pd.product_name');
				$query->join('INNER', '#__qazap_product_details AS pd ON pd.product_id = p.product_id AND pd.language = '.$db->quote($default_language));
			}	

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('p.state = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					JArrayHelper::toInteger($config['filter.published']);
					$query->where('p.state IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			$query->order('p.ordering');

			$db->setQuery($query);
			static::$items[$hash] = $db->loadObjectList();
		}

		return static::$items[$hash];
	}

}
