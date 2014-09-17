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

$desc_class = 'span12';
?>
<div class="categories-page<?php echo $this->pageclass_sfx ?>">
	<?php if ($this->params->get('show_page_heading', 1) && $this->params->get('page_heading')) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php endif; ?>	
	<?php if(($this->parent->category_id != 'root') && ($this->params->get('show_category_title', 1) || $this->params->get('show_category_image_cat', 1) || $this->params->get('show_category_description_cat', 1))) : ?>
		<div class="parent-category-info">
			<div class="row-fluid">
				<?php if($this->params->get('show_category_image_cat', 1)) : 
					$desc_class = 'span8';
					?>
					<div class="span4 parent-cat-image">
						<?php echo QZImages::display($this->parent->getImages()) ?>
					</div>
				<?php endif; ?>
				<div class="<?php echo $desc_class ?>">
					<?php if($this->params->get('show_category_description_cat', 1) && !empty($this->parent->description)) : ?>
						<div class="description">
							<?php echo $this->parent->description; ?>
						</div>
					<?php endif; ?>		
				</div>
			</div>			
		</div>
	<?php endif; ?>

	<?php echo $this->loadTemplate('categories'); ?>

	<?php echo $this->loadTemplate('products'); ?>
</div>
