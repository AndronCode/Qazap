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

$css = array('jquery.fancybox-1.3.4.css');
$js = array('jquery.fancybox-1.3.4.pack.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
?>
<div class="order-details-page">
	<?php if($this->canSee) : ?>
		<?php if(!empty($this->menu)) : ?>
			<?php echo $this->menu ?>
		<?php endif; ?>
		<?php echo $this->loadTemplate('order'); ?>
	<?php else : ?>
		<?php echo $this->loadTemplate('verify'); ?>
	<?php endif; ?>
</div>
