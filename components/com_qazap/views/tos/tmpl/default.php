<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="qazap-tos-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<header class="page-header">
		<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</header>
	<?php endif; ?>
	<?php if ($this->print) : ?>
		<div id="pop-print" class="btn hidden-print">
			<?php echo JHtml::_('icon.print_screen', $this->item, $this->params); ?>
		</div>
		<div class="clearfix"> </div>
	<?php else : ?>
		<?php echo JLayoutHelper::render('joomla.content.icons', array('params' => $this->params, 'item' => $this->item, 'print' => false)); ?>
	<?php endif; ?>
	<article>
		<?php echo $this->item ?>
	</article>
</div>