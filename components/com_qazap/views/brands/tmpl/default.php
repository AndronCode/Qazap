<?php
/**
 * default.php
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

JHtml::_('behavior.framework');
QZApp::loadCSS();			
QZApp::loadJS();
$iCol = 1;
$iCat = 1;
$per_row = $this->params->get('brands_per_row', 4);
$width = 'span' . floor ( 12 / $per_row );
$count = count($this->items);
?>
<div class="brands-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="qz-page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>

	<?php if(!empty($this->items)): ?>
	
		<?php foreach($this->items as $brand) : ?>
			<?php $url = QazapHelperRoute::getBrandRoute($brand); ?>
			<?php if($iCol == 1) : ?>
			<div class="row-fluid qazap-brand-list">
			<?php endif; ?>
				<div class="brand qazap-brand-list-item <?php echo $width ?>">
					<div class="brand-list-item-inner">
						<div class="image-cont">
							<a href="<?php echo JRoute::_($url);?>" title="<?php echo $this->escape($brand->manufacturer_name) ?>">
								<?php echo QZImages::displaySingleImage($brand->images) ?>
							</a>
						</div>
						<h3 class="brand-list-item-title">
							<a href="<?php echo JRoute::_($url);?>" title="<?php echo $this->escape($brand->manufacturer_name) ?>">
								<span><?php echo $this->escape($brand->manufacturer_name) ?></span>
							</a>							
						</h3>
					</div>
				</div>
			<?php if ($iCol == $per_row || $iCat == $count) {?>
			</div> <!-- end of row -->
			<?php
			$iCol = 1;
			} 
			else 
			{
			$iCol ++;
			}
			$iCat ++;
			?>		
		<?php endforeach; ?>	
		
		<div class="pagination">
			<p class="counter pull-right">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>	
	<?php else : ?>
		<?php echo JText::_('COM_QAZAP_ORDER_NO_BRANDS_LISTED') ?>
	<?php endif; ?>	
</div>