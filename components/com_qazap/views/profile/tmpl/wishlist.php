<?php 
/**
 * wishlist.php
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
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
QZApp::loadCSS();			
QZApp::loadJS();
$returnURL = 'index.php?option=com_qazap&view=profile&layout=wishlist';
?>
<div class="profile-<?php echo $this->layout . $this->pageclass_sfx ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>	
	<?php echo $this->menu; ?>
	<div class="qz-page-header clearfix">		
		<h2 class="pull-left"><?php echo JText::_('COM_QAZAP_PROFILE_WISHLIST') ?></h2>		
	</div>
	<?php if (empty($this->wishList)) : ?>
	<div class="alert alert-no-items">
		<?php echo JText::_('COM_QAZAP_GLOBAL_NO_RESULTS'); ?>
	</div>
	<?php else : ?>
	<table class="table table-stripped">
		<thead>
			<tr>
				<th class="center">#</th>
				<th colspan="3"><?php echo JText::_('COM_QAZAP_PROFILE_WISHLIST') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 0;
			foreach($this->wishList as $product)
			{
				$i++;
				$product_url = QazapHelperRoute::getProductRoute($product->product->product_id, $product->product->category_id);	
				?>
				<tr>
					<td width="5%" class="center">
						<?php echo $i ?>
					</td>
					<td width="60">
						<a href="<?php echo JRoute::_($product_url);?>" title="<?php echo $product->product->product_name ?>">
							<?php echo QZImages::displaySingleImage($product->product->images) ?>
						</a>
					</td>
					<td>
						<a href="<?php echo JRoute::_($product_url);?>" title="<?php echo $product->product->product_name ?>">
							<p><strong><?php echo $this->escape($product->product->product_name) ?></strong></p>
						</a>
						<p><?php echo $product->product->short_description?></p>
						<?php if($this->params->get('show_availablity')) : ?>
						<div class="product-availablity-status">
							<?php if($product->product->in_stock - $product->product->booked_order > 0 || !$this->params->get('enablestockcheck')) : ?>
								<?php if($image = $this->params->get('in_stock_image', null) ) : ?>
								<span class="product-availablity label label-success">
									<img src="<?php echo Juri::base(true).$image?>" alt="<?php echo JText::_('COM_QAZAP_IN_STOCK') ?>" />
								</span>
								<?php else : ?>
								<span class="product-availablity label label-success"><?php echo JText::_('COM_QAZAP_IN_STOCK') ?></span>
								<?php endif; ?>
							<?php else : ?>
								<?php if($image = $this->params->get('out_of_stock_image', null) ) : ?>
								<span class="product-availablity label label-warning">
									<img src="<?php echo Juri::base(true).$image?>" alt="<?php echo JText::_('COM_QAZAP_OUT_OF_STOCK') ?>" />
								</span>
								<?php else : ?>
								<span class="product-availablity label label-warning"><?php echo JText::_('COM_QAZAP_OUT_OF_STOCK') ?></span>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<?php endif; ?>					
					</td>
					<td width="100">
						<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=profile&layout=wishlist')?>" method="post">
							<button type="submit" class="btn btn-danger"><?php echo JText::_('COM_QAZAP_REMOVE') ?></button>
							<input type="hidden" name="option" value="com_qazap"/>
							<input type="hidden" name="task" value="profile.deleteWishList" />
							<input type="hidden" name="return" value="<?php echo base64_encode($returnURL) ?> "/>
							<input type="hidden" name="qzform[id]" value="<?php echo $product->id ?>" />
							<?php echo JHtml::_('form.token'); ?>
						</form>
					</td>		
				</tr>
			<?php		
			}
			?>
		</tbody>
	</table>
	<?php endif;?>
</div>