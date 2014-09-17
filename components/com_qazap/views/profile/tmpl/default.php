<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
QZApp::loadCSS();			
QZApp::loadJS();
$skip_fields = array('id', 'address_type', 'address_name', 'country', 'states_territory');
$iCol = 1;
$i = 1;
$addresses_per_row = 3;
$width = ' span'.floor ( 12 / $addresses_per_row);
$shipping_total = count($this->STAddresses);
?>
<div class="profile-<?php echo $this->layout . $this->pageclass_sfx ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>	
	<?php echo $this->menu; ?>
	<div class="qz-page-header clearfix">		
		<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_BUYER_HOME_PAGE') ?></h2>		
	</div>
	<h3>
		<?php echo JText::_('COM_QAZAP_BUYER_BILLING_ADDRESS') ?>
		&nbsp;
		<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=profile&task=profile.edit') ?>" class="btn btn-success btn-small">
			<?php echo JText::_('COM_QAZAP_EDIT') ?>
		</a>			
	</h3>
	<div class="row-fluid">
		<div class="profile-billing-address address-container span12">
			<div class="user-address">
				<div class="address">
					<?php if(!empty($this->BTAddress)) : ?>
						<?php echo QZHelper::displayAddress($this->BTAddress, $skip_fields) ?>
					<?php else : ?>
						<p><?php echo JText::_('COM_QAZAP_BUYER_BILLING_ADDRESS_MISSING_MSG') ?></p>
					<?php endif; ?>
				</div>			
			</div>		
		</div>
	</div>
	
	<h3><?php echo JText::_('COM_QAZAP_BUYER_SHIPPING_ADDRESSES') ?></h3>
	<?php if(!empty($this->STAddresses)) : ?>
		<div class="shipping-addresses">
			<?php foreach($this->STAddresses as $address) : ?>
				<?php if($iCol == 1) : ?>
				<div class="row-fluid">
				<?php endif; ?>
					<div class="address-container<?php echo $width ?>">
						<div class="user-address">
							<div class="address-title">
								<?php if(isset($address->address_name) && !empty($address->address_name)) : ?>
									<?php echo $address->address_name ?>
								<?php else : ?>
									<?php echo JText::_('COM_QAZAP_BUYER_SHIPPING_ADDRESS') ?>
								<?php endif; ?>
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=profile&task=profile.edit&type=st&id=' . (int) $address->id) ?>" class="pull-right">
									<?php echo JText::_('COM_QAZAP_EDIT') ?>
								</a>
							</div>
							<div class="address">
								<?php echo QZHelper::displayAddress($address, $skip_fields) ?>
							</div>
						</div>			
					</div>
				<?php if ($iCol == $addresses_per_row || $i == $shipping_total) : ?>
					<?php if($i == $shipping_total && $iCol < $addresses_per_row) : ?>
					<div class="add-new-box address-container<?php echo $width ?>">
						<div class="user-address">
							<div class="address">
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=profile&task=profile.add&type=st') ?>">
									<?php echo JText::_('COM_QAZAP_ST_ADD') ?>
								</a>
							</div>			
						</div>			
					</div>
					<?php endif; ?>
				</div>
				<?php if($i == $shipping_total && $iCol >= $addresses_per_row) : ?>
				<div class="row-fluid">
					<div class="add-new-box address-container<?php echo $width ?>">
						<div class="user-address">
							<div class="address">
								<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=profile&task=profile.add&type=st') ?>">
									<?php echo JText::_('COM_QAZAP_ST_ADD') ?>
								</a>
							</div>			
						</div>			
					</div>
				</div>
				<?php endif; ?>		
				<!-- end of row -->
				<?php
					$iCol = 1;
			 	else :
					$iCol ++;
			 	endif;
			 	$i ++;
			 	?>	
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="row-fluid">
			<div class="add-new-box address-container<?php echo $width ?>">
				<div class="user-address">
					<div class="address">
						<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=profile&task=profile.edit&type=st') ?>">
							<?php echo JText::_('COM_QAZAP_ST_ADD') ?>
						</a>
					</div>			
				</div>			
			</div>
		</div>	
	<?php endif; ?>
</div>