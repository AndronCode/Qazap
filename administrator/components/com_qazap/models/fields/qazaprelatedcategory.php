<?php
/**
 * qazaprelatedcategory.php
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
JFormHelper::loadFieldClass('list');
/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Qazap.Platform
 * @subpackage  Form
 * @since   1.0.0
 */
class JFormFieldQazapRelatedCategory extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var    string
	* @since   1.0.0
	*/
	protected $type = 'QazapRelatedCategory';

	/**
	* Method to get the field input markup for a generic list.
	* Use the multiple attribute to enable multiselect.
	*
	* @return  string  The field input markup.
	*
	* @since   1.0.0
	*/
	protected function getOptions()
	{		
		$name = isset($this->element['name']) ? (string) $this->element['name'] : 'category_id';		
		$category_id = $this->form->getValue($name, 0);	
		$categories = JHtml::_('qazapcategory.options', array('filter.published' => array(0, 1), 'skip' => null));

		return array_merge(parent::getOptions(), $categories);
	}

}
