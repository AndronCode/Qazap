<?php
/**
 * qazapemailtemplates.php
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
 * Form Field class for the Qazap Platform.
 * Supports a generic list of options.
 *
 * @package     Qazap.Platform
 * @subpackage  Form
 * @since       1.0.0
 */
class JFormFieldQazapEmailTemplates extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'QazapEmailTemplates';

	/**
	 * Method to get a list of categories that respects access controls and can be used for
	 * either category assignment or parent category assignment in edit screens.
	 * Use the parent element to indicate that the field will be used for assigning parent categories.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_qazap/models', 'QazapModel');
		$emailModel =  JModelLegacy::getInstance('Emailtemplate', 'QazapModel', array('ignore_request' => true));
		$fields = $emailModel->getTemplates();		
		
		$options = array();
		$useselect = $this->element['useselect'] ? (bool) $this->element['useselect'] : false;
		
		if($useselect)
		{
			$options[] = JHtml::_('select.option', '', '- ' . JText::_('JSELECT'));
		}
		
		foreach($fields as $value=>$name)
		{
			$options[] = JHtml::_('select.option', (string) $value, JText::_($name));
		}
		
		$options = array_merge(parent::getOptions(), $options);

		return $options;		
	}
}
