<?php
/**
 * qazapcategories.php
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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

JFormHelper::loadFieldClass('list');

/**
* Form Field class for the Qazap Platform.
* Supports an HTML select list of categories
*
* @package     Qazap.administrator
* @subpackage  Form
* @since       1.0.0
*/
class JFormFieldQazapcategories extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'Qazapcategories';

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		$options = array();
		$published = (string) $this->element['published'];
		$show_root = isset($this->element['show_root']) ? ($this->element['show_root'] == 'true') : false;
		$show_all = isset($this->element['show_all']) ? ($this->element['show_all'] == 'true') : false;

		// Filter over published state or not depending upon if it is present.
		if ($published)
		{				
			$options = JHtml::_('qazapcategory.options', array('filter.published' => explode(',', $published)));
		}
		else
		{
			$options = JHtml::_('qazapcategory.options');
		}


		
		// Verify permissions.  If the action attribute is set, then we scan the options.
		if ((string) $this->element['action'] && !empty($options))
		{
			// Get the current user object.
			$user = JFactory::getUser();

			foreach ($options as $i => $option)
			{
				/*
				* To take save or create in a category you need to have create rights for that category
				* unless the item is already in that category.
				* Unset the option if the user isn't authorised for it. In this field assets are always categories.
				*/
				if ($user->authorise('core.create', 'com_qazap.category.' . $option->value) != true)
				{
					unset($options[$i]);
				}
			}
		}
		
		if ($show_root)
		{
			array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
		}
		elseif($show_all)
		{
			array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_QAZAP_ALL_CATEGORIES')));
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
