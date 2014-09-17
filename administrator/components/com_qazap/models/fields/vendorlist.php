<?php
/**
 * vendorlist.php
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

class JFormFieldVendorlist extends JFormFieldList
{
	protected $type = 'Vendorlist';

	protected function getOptions()
	{	
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			->select(array('a.id', 'a.shop_name'))
			->from('`#__qazap_vendor` AS a');
		$db->setQuery($sql);
		$vendors = $db->loadObjectList();
		
		$options = array();	
		if(!empty($vendors))
		{
			foreach($vendors as $vendor)
			{
				$options[] = JHtml::_('select.option', (int) $vendor->id, $vendor->shop_name);
			}	
		}		

		$options = array_merge(parent::getOptions(), $options);		
		return $options;
	}
}
