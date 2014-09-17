<?php
/**
 * shipping_method.php
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
$method 		= $displayData;
$field_id		= $method->field_id.'_'.$method->id;
$lbl_id			= $method->field_id.'_'.$method->id.'-lbl';
$desc_id		= $method->field_id.'_'.$method->id.'-desc';
$addHTML_id	= $desc_id = $method->field_id.'_'.$method->id.'-html';
$checked		= $method->selected ? 'checked="checked"' : '';
$active_class = $method->selected ? ' active"' : '';
?>
<label for="<?php echo $field_id ?>" id="<?php echo $lbl_id ?>" class="radio">
	<input type="radio" name="<?php echo $method->field_name ?>" id="<?php echo $field_id ?>" value="<?php echo $method->id; ?>" <?php echo $checked ?> />
	<?php if(!empty($method->logo)) : ?>
		<img src="<?php echo JUri::base(true) . '/' . $method->logo ?>" class="shipment-method-logo" alt="<?php echo $method->shipment_name ?>" />
	<?php endif; ?>
	<?php echo $method->display ?>
	<?php if($method->shipment_description) : ?>
	<div id="<?php echo $desc_id ?>" class="qazap_shipment_description<?php echo $active_class ?>">
		<?php echo $method->shipment_description ?>
	</div>
	<?php endif; ?>
	<?php if($method->additional_html) : ?>
	<div id="<?php echo $addHTML_id ?>" class="qazap_shipment_html<?php echo $active_class ?>">
		<?php echo $method->additional_html ?>
	</div>
	<?php endif; ?>	
</label>