<?php
/**
 * default_category.php
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

$params = $this->category->params;//qzdump($this->category);exit;
$desc_class = 'span12';
?>
<?php if(($this->category->category_id == 'root') && $params->get('show_category_title', 1)) : ?>
	<h2 class="category-title"><?php echo JText::_('COM_QAZAP_ALL_CATEGORIES') ?></h2>
<?php elseif($params->get('show_category_title', 1) || $params->get('show_category_image', 1) || $params->get('show_category_description', 1)) : ?>
	<div class="parent-category-info">
		<div class="row-fluid">
			<?php if($params->get('show_category_image', 1)) : 
				$desc_class = 'span8';
				?>
				<div class="span4 parent-cat-image">
					<?php echo QZImages::display($this->category->getImages()) ?>
				</div>
			<?php endif; ?>
			<div class="<?php echo $desc_class ?>">
				<?php if($params->get('show_category_title', 1) && !empty($this->category->title)) : ?>
					<h2 class="category-title"><?php echo $this->escape($this->category->title) ?></h2>						
				<?php endif; ?>
				<?php if($params->get('show_category_description', 1) && !empty($this->category->description)) : ?>
					<div class="description">
						<?php echo $this->category->description; ?>
					</div>
				<?php endif; ?>		
			</div>
		</div>			
	</div>
<?php endif; ?>
