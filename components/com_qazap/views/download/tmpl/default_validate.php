<?php
/**
 * default_validate.php
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


$doc = JFactory::getDocument();

?>
<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=download'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
	
	<p><?php echo JText::_('COM_QAZAP_DOWNLOAD_VALIDATION_MESSAGE') ?></p>
	<hr/>
	<fieldset>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('download_id'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('download_id'); ?></div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('passcode'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('passcode'); ?></div>
		</div>
	</fieldset>			
	
	<div class="form-actions">
		<button type="submit" id="qazap-download-btn" class="btn btn-primary">
			<?php echo JText::_('COM_QAZAP_GLOBAL_SUBMIT');?>				
		</button>
		<input type="hidden" name="option" value="com_qazap" />
		<input type="hidden" name="task" value="download.getDownload" />		
		<?php echo JHtml::_('form.token'); ?>	
	</div>
</form>
