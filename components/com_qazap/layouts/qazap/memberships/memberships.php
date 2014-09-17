<?php
/**
 * memberships.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Site
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('JPATH_BASE') or die;

// Store the data in a local variable before display
$membership = $displayData;
$config = QZApp::getConfig();

/**
* Field class for membership field in add to cart form
* 
* @var		string
* @since	1.0
*/
$attr = $config->get('membership_required', 1) ? 'class="chosen" required="true"' : 'class="chosen"';

$options = array();

if($config->get('membership_show_select', 1))
{
  $options[] = JHtml::_('select.option', '', JText::_('COM_QAZAP_SELECT_MEMBERSHIP_PLAN'));
}

if(count($membership->data) == 1 && $config->get('membership_hidden', 0))
{
  echo '<input type="hidden" name="'. $membership->field_name .'" id="' . $membership->field_id . '" value="' . (int) $membership->data[0]->id . '" />'; 
}
else
{
  foreach($membership->data as $data) 
  {
  	$title = $data->plan_name;
  	$data->price = (float) $data->price;
  	
  	if($data->price > 0)
  	{
  		$title .= ' (+' . QZHelper::currencyDisplay($data->price) . ')';
  	}
  	elseif($data->price < 0)
  	{
  		$title .= ' (-' . QZHelper::currencyDisplay($data->price) . ')';
  	}
  	
  	$options[] = JHtml::_('select.option', (int) $data->id, trim($title));
  }

  echo JHtml::_('select.genericlist', $options, $membership->field_name, $attr, 'value', 'text', '', $membership->field_id);  
}
?>
