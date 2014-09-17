<?php
/**
 * params.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

if(!$this->params)
{
	return;
}

foreach($this->params->getFieldset('params') as $field) : 
	if ($field->hidden): 
		 echo $field->input;
	else: ?>
		<div class="control-group">
			<div class="control-label">
			<?php echo $field->label; ?>
			<?php if (!$field->required && $field->type != 'Spacer') : ?>
				<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
			<?php endif; ?>
			</div>
			<div class="controls">
				<?php echo $field->input;?>
			</div>
		</div>
	<?php endif;?>		
<?php endforeach; ?>
