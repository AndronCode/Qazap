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
$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js', 'cloud-zoom.1.0.2.modified.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
?>
<div class="brand-page">
	<div class="row-fluid">
		<div class="span4">
			<figure class="brand-logo">
				<?php echo QZImages::display($this->item->images) ?>
			</figure>
		</div>
		<div class="span8">
			<h1><?php echo $this->escape($this->item->manufacturer_name); ?></h1>
			<div class="brand-email">
				<span class="type-title"><?php echo JText::_('COM_QAZAP_BRAND_EMAIL') ?>:&nbsp;</span>
				<?php echo JHtml::_('email.cloak', $this->item->manufacturer_email); ?>
			</div>
			<div class="brand-url">
				<span class="type-title"><?php echo JText::_('COM_QAZAP_BRAND_URL') ?>:&nbsp;</span>
				<?php echo $this->weblink($this->item->manufacturer_url); ?>
			</div>
			<div class="brand-description">
				<?php echo $this->paragraph($this->item->description) ?>
			</div>
			<div class="brand-products">
				<?php $url = QazapHelperRoute::getBrandRoute($this->item, true); ?>
				<a href="<?php echo JRoute::_($url) ?>" class="btn btn-primary"><?php echo JText::_('COM_QAZAP_BRAND_SHOW_PRODUCTS') ?></a>
			</div>		
		</div>		
	</div>
</div>
