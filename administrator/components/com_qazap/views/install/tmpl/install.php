<?php
/**
 * install.php
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

// no direct access
defined('_JEXEC') or die;

?>
<div id="qazap-installation-running">
	<h2 class="qazap-subheader"><?php echo JText::_('COM_QAZAP_INSTL_INSTALLING') ?></h2>
	<p class="qazap-installer-process"><?php echo JText::_('COM_QAZAP_INSTL_PLEASE_WAIT') ?></p>
	<div class="progress progress-striped active">
	  <div class="bar"></div>
	</div>

	<div class="table-installer-cont">
		<table id="qazap-steps-table" class="table table-striped table-condensed table-installer-status">
			<tbody>
				<?php foreach ($this->steps as $step) : 
					$state = null;
					$class = 'pending';
					if($step->state == 'C')
					{
						$state = JText::_('COM_QAZAP_INSTL_DONE');
						$class = 'completed success';
					}
					elseif($step->state == 'F')
					{
						$state = JText::_('COM_QAZAP_INSTL_FAILED');
						$class = 'failed error';
					}
					elseif($step->state == 'A')
					{
						$state = '<span class="pending-process" title="' . JText::_('COM_QAZAP_INSTL_RUNNING') . '"></span>';
						$class = 'running info';
					}	?>
				<tr class="install-process <?php echo $class ?>">
					<td class="item">
						<?php echo $step->label; ?>
					</td>
					<td class="narrow center">
						<span>
							<?php echo $state ?>
							<?php if ($step->notice):?>
								<i class="icon-notification-2 icon-white light hasTooltip" title="<?php echo $step->notice; ?>"></i>
							<?php endif;?>
						</span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2"></td>
				</tr>
			</tfoot>
		</table>
		<div id="jtoken">
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</div>

<div id="qazap-installation-failed">
	<h2 class="qazap-subheader"><?php echo JText::_('COM_QAZAP_INSTL_FAILED_TITLE') ?></h2>
	<p><?php echo JText::_('COM_QAZAP_INSTL_FAILED_DESC') ?></p><br/>
	<a class="btn btn-large" href="<?php echo JRoute::_('index.php', false) ?>"><?php echo JText::_('JTOOLBAR_CLOSE') ?></a>	
</div>

<div id="qazap-installation-completed">
	<h2 class="qazap-subheader"><?php echo JText::_('COM_QAZAP_INSTL_COMPLETE_TITLE') ?></h2>
	<p><?php echo JText::_('COM_QAZAP_INSTL_COMPLETE_DESC') ?></p><br/>
	<a class="btn btn-large btn-inverse" href="<?php echo JRoute::_('index.php?option=com_qazap', false) ?>"><i class="icon-home-2"></i> <?php echo JText::_('COM_QAZAP_INSTL_COMPLETE_CONTROL_PANEL') ?></a>	
	<?php if($this->installSampleData) : 
		$content  = '<p>' . JText::_('COM_QAZAP_INSTL_COMPLETE_INSTALL_SAMPLE_DATA_DESC') . '</p>';
		$content .= '<button type="button" class="btn btn-primary btn-success" onclick="QazapInstaller.installSampleData();" title="' . JText::_('COM_QAZAP_INSTL_INSTALL_NOW') . '">'. JText::_('COM_QAZAP_INSTL_INSTALL_NOW') . '</button><br/>';
		?>
		<a href="#" class="btn btn-large btn-success" id="QZSampleDataPop" title="" data-content="<?php echo htmlspecialchars($content) ?>" data-original-title="<?php echo JText::_('COM_QAZAP_INSTL_COMPLETE_INSTALL_ARE_YOU_SURE') ?>"><i class="qzicon-download5"></i> <?php echo JText::_('COM_QAZAP_INSTL_COMPLETE_INSTALL_SAMPLE_DATA') ?></a>
		<div id="jtoken">
			<?php echo JHtml::_('form.token'); ?>
		</div>		
	<?php endif; ?>	
</div>