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

JHtml::_('bootstrap.tooltip');
$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js', 'jquery.raty.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
?>
<div class="shop-page<?php echo $this->pageclass_sfx ?>" itemscope itemtype="http://schema.org/Organization">
	<?php if ($this->params->get('show_page_heading', 1) && $this->params->get('page_heading')) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>
	<meta itemprop="name" content="<?php echo $this->escape($this->store->name) ?>" />
	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
		<meta itemprop="streetAddress" content="<?php echo $this->store->address_1 ? $this->escape($this->store->address_1) . ' ' : '' ?><?php echo $this->escape($this->store->address_2) ?>" />
		<meta itemprop="postalCode" content="<?php echo $this->escape($this->store->zip) ?>" />
		<meta itemprop="addressLocality" content="<?php echo $this->store->city ? $this->escape($this->store->city) . ', ' : '' ?><?php echo $this->escape($this->store->country_name) ?>" />
		<meta  itemprop="telephone" content="<?php echo $this->escape($this->store->phone_1) ?>" />
		<meta  itemprop="faxNumber" content="<?php echo $this->escape($this->store->fax) ?>" />
	</div>	
	
	<?php echo $this->loadTemplate('seller'); ?>
	
	<?php echo $this->loadTemplate('products'); ?>	
	
</div>

