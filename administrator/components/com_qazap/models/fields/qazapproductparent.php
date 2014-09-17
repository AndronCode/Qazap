<?php
/**
 * qazapproductparent.php
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
* @package     Joomla.Platform
* @subpackage  Form
* @since       1.0.0
*/
class JFormFieldQazapProductParent extends JFormFieldList
{
	/**
	* The form field type.
	*
	* @var    string
	* @since  1.0.0
	*/
	protected $type = 'QazapProductParent';

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
		$products = JHtml::_('qazapproduct.products');		
		$app = JFactory::getApplication();
		$product_id = $app->input->get('product_id', 0, 'int');	
		$vendor_id = QZUser::get()->get('vendor_id', 0);
		$user = JFactory::getUser();
		$isAdmin = $user->get('isRoot');
				
		$options = array(JHtml::_('select.option', 0, JText::_('COM_QAZAP_ROOT_PARENT')));
		
		if(!empty($products))
		{
			foreach($products as $product)
			{
				if($product->product_id != $product_id && $product->parent_id == '0')
				{
					if($isAdmin || $app->isAdmin())
					{
						$options[] = JHtml::_('select.option', (int) $product->product_id, '- '.$product->product_name);
					}
					elseif($app->isSite() && ($product->vendor_id == $vendor_id))
					{
						$options[] = JHtml::_('select.option', (int) $product->product_id, '- '.$product->product_name);
					}
				}
			}			
		}

		return array_merge(parent::getOptions(), $options);
	}
}
