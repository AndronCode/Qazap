<?php
/**
 * selected_shipping.php
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
?>
<?php if($method->logo) : ?>
	<span class="shipping-method-logo-cont">
		<img src="<?php echo JUri::base(true).'/'. $method->logo ?>" alt="<?php echo $method->shipment_name ?>" class="shipping-method-logo" />
	</span>
<?php endif; ?>
<span class="shipping-method-details"><?php echo $method->shipment_name ?></span>
<?php if($method->total_price) : ?>
	<span class="shipping-method-price"> (<?php echo QZHelper::currencyDisplay($method->total_price) ?>)</span>
<?php endif; ?>