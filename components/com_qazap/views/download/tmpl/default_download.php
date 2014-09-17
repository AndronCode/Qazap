<?php
/**
 * default_download.php
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
$isAdmin = (int) $this->user->get('isRoot');
$download_id = (int) $this->download->download_id;
$passcode = $this->state->get('passcode');
$doc->addScriptDeclaration("
if (typeof jQ === 'undefined' || typeof jQ === undefined) {
    var jQ = jQuery.noConflict();
}
jQ(document).ready(function() {
        var downloadButtonClicked = false;
        var isAdmin = $isAdmin;
        jQ('#qazap-download-btn').click(function(){
          //jQ(this).attr('disabled', 'disabled');
          jQ(this).parents('form').submit();
          downloadButtonClicked = true;
          if(!isAdmin) {
              updateDownloadStatus();
          } else {
              jQ(this).removeAttr('disabled');
          }
          return false;
        });
        function updateDownloadStatus() {
            var download_id = $download_id;
            var passcode = '$passcode';
            setInterval(function() {
                    if(downloadButtonClicked) {
                        jQ.getJSON(window.qzuri+'?option=com_qazap&view=download&format=json&task=download.getstatus&download_id='+download_id+'&passcode='+passcode, function(data) {                        	
                                if(data.error == 0)
                                {
                                    var old_download_count = jQ('#qazap-download-form').find('span.download_count').text();
                                    if(data.download_count > old_download_count)
                                    {
                                        // refresh status
                                        for (var key in data) {
                                            var value = data[key];
                                            jQ('#qazap-download-form').find('span.'+key).text(value);
                                        }
                                        jQ('#qazap-download-btn').removeAttr('disabled');
                                        downloadButtonClicked = false;
                                    }
                                }
                            });
                    }
                }, 1000);
        }
    });
");
?>
<form id="qazap-download-form" action="<?php echo JRoute::_('index.php?option=com_qazap&view=download&download_id=' . $download_id); ?>" method="post" class="form-horizontal no-label" enctype="multipart/form-data">
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('COM_QAZAP_DOWNLOAD_FILE_NAME') ?>:
		</div>
		<div class="controls">
			<?php echo $this->escape($this->download->name) ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('COM_QAZAP_DOWNLOAD_FILE_SIZE') ?>:
		</div>
		<div class="controls">
			<?php echo $this->download->file_size ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('COM_QAZAP_DOWNLOAD_START_DATE') ?>:
		</div>
		<div class="controls">
			<?php echo QZHelper::displayDate($this->download->download_start_date) ?>
		</div>
	</div>
	<div class="control-group">
		<?php
		$validity = (int) $this->params->get('download_validity', 0);
		if($validity > 0) 
		{
			$date = JFactory::getDate(($this->download->download_start_date . '+ ' . $validity . ' days'), 'UTC');
			$expiryDate =  $date->format('Y-m-d H:i:s', true, false);
			$expiryDate = QazapHelper::displayDate($expiryDate);
		}
		else
		{
			$expiryDate = JText::_('COM_QAZAP_DOWNLOAD_LIFETIME');
		} ?>
		<div class="control-label">
			<?php echo JText::_('COM_QAZAP_DOWNLOAD_EXPIRY_DATE') ?>:
		</div>
		<div class="controls">
			<?php echo $expiryDate ?>
		</div>		
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('COM_QAZAP_DOWNLOAD_COUNT') ?>:
		</div>
		<div class="controls">
			<span class="download_count"><?php echo (int) $this->download->download_count ?></span>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('COM_QAZAP_LAST_DOWNLOAD_DATE') ?>:
		</div>
		<div class="controls">
			<span class="last_download"><?php echo QZHelper::displayDate($this->download->last_download) ?></span>
		</div>
	</div>	
	<div class="control-group">
		<?php
		$limit = (int) $this->params->get('download_limit', 0);
		if($limit > 0) 
		{
			$download_left = ($limit - (int) $this->download->download_count);
		}
		else
		{
			$download_left = JText::_('COM_QAZAP_DOWNLOAD_NOLIMIT');
		} ?>	
		<div class="control-label">
			<?php echo JText::_('COM_QAZAP_DOWNLOADS_LEFT') ?>:
		</div>
		<div class="controls">
			<span class="download_left"><?php echo $download_left ?></span>
		</div>											
	</div>			
	
	<div class="form-actions">
		<button type="submit" id="qazap-download-btn" class="btn btn-primary"><?php echo JText::_('COM_QAZAP_DOWNLOAD');?></button>
		<input type="hidden" name="passcode" value="<?php echo $passcode ?>" />
		<input type="hidden" name="option" value="com_qazap" />
		<input type="hidden" name="task" value="download.download" />		
		<?php echo JHtml::_('form.token'); ?>	
	</div>
</form>
