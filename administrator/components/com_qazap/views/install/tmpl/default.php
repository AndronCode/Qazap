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

JHtml::_('jquery.framework');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
$doc = JFactory::getDocument();
$doc->addStyleDeclaration(".header,.subhead{display: none !important;}");
$actions = json_encode($this->actions);
$doc->addScriptDeclaration("// <![CDATA[
window.qzinstaller_stepvalue = {$this->stepValue};
window.qzinstaller_actions = $actions;
// ]]>");
?>
<div id="qazap-installer-container">	
	<div class="qazap-installer-header">
		<h1><?php echo JText::_('COM_QAZAP_INSTL_TITLE') ?></h1>
	</div>
	<div id="qazap-system-message-box">
		<?php if(!$this->canInstall) : ?>
			<div class="alert alert-error">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_FAILED_MSG') ?>
			</div>			
		<?php endif; ?>
		<?php if(!$this->packagesOk) : ?>
			<div class="alert alert-error">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<?php echo JText::_('COM_QAZAP_INSTL_PACKAGE_CHECK_FAILED_MSG') ?>
			</div>			
		<?php endif; ?>		
	</div>
	<div class="qazap-installer-inner">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-primary" onclick="location.reload(true);" title="<?php echo JText::_('JCHECK_AGAIN'); ?>"><i class="icon-loop icon-white"></i> <?php echo JText::_('JCHECK_AGAIN'); ?></button>
				<?php if($this->canInstall && $this->packagesOk) : ?>
				<button type="button" class="btn btn-primary btn-success" onclick="QazapInstaller.submitForm();" title="<?php echo JText::_('COM_QAZAP_INSTL_INSTALL_NOW'); ?>"><i class="qzicon-download2 icon-white"></i> <?php echo JText::_('COM_QAZAP_INSTL_INSTALL_NOW'); ?></button>
				<?php endif; ?>
			</div>
		</div>
		<form action="index.php" method="post" id="adminForm" class="form-horizontal qazap-installer-form">
			<div class="row-fluid">
				<div class="span12">
					<h3><?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_PACKAGE_TITLE'); ?></h3>
					<hr class="hr-condensed" />
					<?php if(!empty($this->packages)) : ?>
					<p class="install-text">
						<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_PACKAGE_DESC'); ?>
					</p>					
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>
									<?php echo JText::_('COM_QAZAP_INSTL_PACKAGE_NAME'); ?>
								</th>
								<th class="center">
									<?php echo JText::_('COM_QAZAP_INSTL_PACKAGE_EXISTS'); ?>
								</th>
								<th class="center" width="100px">
									<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_HASH'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($this->packages as $package) : ?>
							<tr>
								<td>
									<?php echo $package->label; ?>
								</td>
								<td class="center" width="200px">
									<span class="label label-<?php echo $package->exists ? 'success' : 'important'; ?>">
										<?php echo $package->exists ? JText::_('COM_QAZAP_OK') : JText::_('COM_QAZAP_MISSING'); ?>
									</span>
								</td>
								<td class="center">
									<span class="label label-<?php echo $package->hash ? 'success' : 'important'; ?>">
										<?php echo $package->hash ? JText::_('JYES') : JText::_('JNO'); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3"></td>
							</tr>
						</tfoot>
					</table>
					<?php else : ?>
					<p class="install-text text-error">
						<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_PACKAGE_FAILED_DESC'); ?>
					</p>						
					<?php endif; ?>
				</div>
			</div>	
			<div class="row-fluid">
				<div class="span12">
					<h3><?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_TITLE'); ?></h3>
					<hr class="hr-condensed" />
					<p class="install-text">
						<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_DESC'); ?>
					</p>
					<table class="table table-striped table-condensed">
						<tbody>
							<?php foreach ($this->options as $option) : ?>
							<tr>
								<td class="item">
									<?php echo $option->label; ?>
								</td>
								<td class="center" width="100px">
									<span class="label label-<?php echo ($option->state) ? 'success' : 'important'; ?>">
										<?php echo JText::_(($option->state) ? 'JYES' : 'JNO'); ?>
										<?php if ($option->notice):?>
											<i class="icon-notification-2 icon-white light hasTooltip" title="<?php echo $option->notice; ?>"></i>
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
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<h3><?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_RECOMMENDED_PHP_SETTINGS_TITLE'); ?></h3>
					<hr class="hr-condensed" />
					<p class="install-text">
						<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_RECOMMENDED_PHP_SETTINGS_DESC'); ?>
					</p>
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>
									<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_DIRECTIVE'); ?>
								</th>
								<th class="center" width="200px">
									<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_RECOMMENDED'); ?>
								</th>
								<th class="center" width="100px">
									<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_ACTUAL'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->phpSettings as $setting) : ?>
							<tr>
								<td>
									<?php echo $setting->label; ?>
								</td>
								<td class="center" width="200px">
									<span class="label label-success disabled">
										<?php echo $setting->recommended ?>
									</span>
								</td>
								<td class="center">
									<span class="label label-<?php echo ($setting->state >= $setting->recommended) ? 'success' : 'warning'; ?>">
										<?php echo $setting->state ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3"></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<h3><?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_RECOMMENDED_MYSQL_SETTINGS_TITLE'); ?></h3>
					<hr class="hr-condensed" />
					<p class="install-text">
						<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_RECOMMENDED_MYSQL_SETTINGS_DESC'); ?>
					</p>
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th>
									<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_DIRECTIVE'); ?>
								</th>
								<th class="center" width="200px">
									<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_RECOMMENDED'); ?>
								</th>
								<th class="center" width="100px">
									<?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_ACTUAL'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->MySQLSettings as $setting) : ?>
							<tr>
								<td>
									<?php echo $setting->label; ?>
								</td>
								<td class="center">
									<span class="label label-success disabled">
										<?php echo $setting->recommended ?>
									</span>
								</td>
								<td class="center">
									<span class="label label-<?php echo ($setting->state >= $setting->recommended) ? 'success' : 'warning'; ?>">
										<?php echo $setting->state ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3"></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>

			<input type="hidden" name="task" value="preinstall" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
</div>