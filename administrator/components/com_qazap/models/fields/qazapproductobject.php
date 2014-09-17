<?php
/**
 * qazapproductobject.php
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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_qazap/helpers/html');
JFormHelper::loadFieldClass('list');
/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldQazapProductObject extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'QazapProductObject';
	
	/**
	 * Method to get a list of products that respects access controls and can be used for
	 * Use the parent element to indicate that the field will be used for assigning parent categories.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		$show_select = isset($this->element['show_select']) ? ($this->element['show_select'] === 'true') : false;
		$show_all = isset($this->element['show_all']) ? ($this->element['show_all'] === 'true') : false;
				
		$array = JHtml::_('qzproductobject.options');
		$options = array();
		
		if(!empty($array))
		{
			foreach($array as $property => $name)
			{
				$options[] = JHtml::_('select.option', $property, $name);
			}
		}
		
		if ($show_select)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_('JSELECT')));
		}
		elseif($show_all)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_('COM_QAZAP_ALL')));
		}		

		return array_merge(parent::getOptions(), $options);
	}
}
