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

// no direct access
defined('_JEXEC') or die;

QZApp::loadCSS();			
QZApp::loadJS();
?>
<section class="download-page<?php echo $this->pageclass_sfx ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="qz-page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>
	<?php if(empty($this->download)) : ?>
		<?php echo $this->loadTemplate('validate'); ?>
	<?php else : ?>
		<?php echo $this->loadTemplate('download'); ?>
	<?php endif; ?>	
</section>