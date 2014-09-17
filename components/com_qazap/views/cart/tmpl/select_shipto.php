<?php
/**
 * select_shipto.php
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

// no direct access
defined('_JEXEC') or die;
QZApp::loadCSS();
QZApp::loadJS();

$iCol = 1;
$i = 1;
$addresses_per_row = 3;
$width = ' span'.floor ( 12 / $addresses_per_row);
$total = count($this->UserAddresses);
$skip_fields = array('id', 'address_type', 'address_name', 'country', 'states_territory');
?>
<div class="cart-shipping-address-page">
	<div class="page-header">
		<h1><?php echo JText::_('COM_QAZAP_CART_CONFIRM_SHIPPING_ADDRESS') ?></h1>
	</div>
	<?php if(empty($this->UserAddresses)) : ?>
		<div class="row-fluid">
			<div class="add-new-box address-container<?php echo $width ?>">
				<div class="user-address">
					<div class="address">
						<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('layout' => 'edit_shipto'))) ?>">
							<?php echo JText::_('COM_QAZAP_ST_ADD') ?>
						</a>
					</div>			
				</div>			
			</div>
		</div>
	<?php else : ?>
		<form id="shipto-selection-form" method="post" action="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('layout'=>'confirm_shipto'))) ?>" class="form-validate form-horizontal">
			<?php foreach($this->UserAddresses as $address) : ?>
				<?php if($iCol == 1) : ?>
				<div class="row-fluid">
				<?php endif; ?>
					<div class="address-container<?php echo $width ?>">
						<div class="user-address">
							<div class="address-title">
								<?php
								$selected = '';
								if((!isset($this->cart->shipping_address['id']) || empty($this->cart->shipping_address['id'])) && $address->address_type == 'bt')	
								{
									$selected = 'checked="checked"';
								}			
								elseif(isset($this->cart->shipping_address['id']) && ($this->cart->shipping_address['id'] == $address->id))
								{
									$selected = 'checked="checked"';
								}
								?>
								<?php if($address->address_type != 'bt') :?>
								<input type="radio" name="qzform[shipto_id]" value="<?php echo $address->id ?>" <?php echo $selected ?>/>
								<?php else : ?>
								<input type="radio" name="qzform[shipto_id]" value="-1" <?php echo $selected ?>/>
								<?php endif; ?>
								<?php echo $address->address_name ?>
								<?php if($address->address_type != 'bt') :?>
								<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('layout' => 'edit_shipto', 'id' => $address->id))) ?>" class="pull-right">
									<?php echo JText::_('COM_QAZAP_CART_EDIT_ADDRESS') ?>
								</a>
								<?php endif; ?>
							</div>
							<div class="address">
								<?php echo QZHelper::displayAddress($address, $skip_fields) ?>
							</div>
						</div>			
					</div>
				<?php if ($iCol == $addresses_per_row || $i == $total) : ?>
					<?php if($i == $total && $iCol < $addresses_per_row) : ?>
							<div class="add-new-box address-container<?php echo $width ?>">
								<div class="user-address">
									<div class="address">
										<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('layout' => 'edit_shipto'))) ?>">
											<?php echo JText::_('COM_QAZAP_ST_ADD') ?>
										</a>
									</div>			
								</div>			
							</div>
							<?php endif; ?>
						</div>
						<?php if($i == $total && $iCol >= $addresses_per_row) : ?>
						<div class="row-fluid">
							<div class="add-new-box address-container<?php echo $width ?>">
								<div class="user-address">
									<div class="address">
										<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute(array('layout' => 'edit_shipto'))) ?>">
											<?php echo JText::_('COM_QAZAP_ST_ADD') ?>
										</a>
									</div>			
								</div>			
							</div>
						</div>
						<?php endif; ?> <!-- end of row -->
				<?php
					$iCol = 1;
		   	else :
					$iCol ++;
		   	endif;
		   	$i ++;
		   	?>	
			<?php endforeach; ?>
			<div class="cart-continue-area">
				<div class="row-fluid">
					<div class="span6">
						<a href="<?php echo JRoute::_(QazapHelperRoute::getCartRoute()) ?>" class="cart-continue-button btn btn-large">
							<?php echo JText::_('COM_QAZAP_CART_GO_BACK') ?>
						</a>	
					</div>	
					<div class="span6">
						<?php echo $this->confirmButton ?>
					</div>
				</div>
			</div>		
		</form>
	<?php endif; ?>
</div>


