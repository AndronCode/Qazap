<?php
/**
 * confirm_button.php
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
defined('JPATH_BASE') or die;

?>
<button type="submit" id="<?php echo $displayData->buttonID ?>" class="<?php echo $displayData->confirmButtonClass ?>" title="<?php echo $displayData->buttonTxt ?>">
	<span><?php echo $displayData->buttonTxt ?></span>
</button>
<?php if(count($displayData->inputs)) : ?>
	<?php foreach($displayData->inputs as $name=>$value) : ?>
	<input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>" />
	<?php endforeach; ?>
<?php endif; ?>
<?php echo JHtml::_('form.token'); ?>