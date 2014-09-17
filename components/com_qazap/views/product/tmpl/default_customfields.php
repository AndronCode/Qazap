<?php
/**
 * default_customfields.php
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
$params  = $this->item->params;
?>
<?php if(isset($this->item->custom_fields[$this->layout_position])) : ?>
	<?php foreach($this->item->custom_fields[$this->layout_position] as $field) : ?>
		<?php if(!$field->hidden) :?>
			<div class="qazap-field-group field-type-<?php echo $field->plugin ?>">
				<?php if($field->show_title) : ?>
					<div class="qazap-field-title">
						<?php
						$class = '';
						$title = '';
						if($field->tooltip)
						{
							$class = ' class="hasTooltip"';
							$title = ' title="'.$field->tooltip.'"';
						}?>
						<h4 id="<?php echo $field->field_id ?>-lbl" class="qazap-attribute-label">
							<span<?php echo $class.$title ?>><?php echo $field->title ?>:&nbsp;</span>
						</h4>
					</div>
				<?php endif; ?>
				<div class="qazap-field-display">
					<?php echo $field->display ?>
				</div>
				<?php if(!empty($field->description)) : ?>
					<div class="qazap-field-description">
						<?php echo $this->escape($field->description) ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>