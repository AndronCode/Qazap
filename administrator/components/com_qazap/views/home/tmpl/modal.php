<?php
/**
 * modal.php
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

$doc = JFactory::getDocument();
$doc->addScript(JUri::base(true) . '/components/com_qazap/assets/js/jquery.flot.js');
$doc->addScript(JUri::base(true) . '/components/com_qazap/assets/js/jquery.flot.time.min.js');
$doc->addScript(JUri::base(true) . '/components/com_qazap/assets/js/qazap.flot.js');

$periods = array('last7days', 'last30days', 'lastmonth', 'thismonth', 'last1year', 'all');
$options = array();
foreach($periods as $period)
{
	$options[] = JHtml::_('select.option', (string) $period, JText::_('COM_QAZAP_PLOT_LBL_' . strtoupper($period)));
}
?>
<div class="modal-plot-wrap">
	<h1 class="modal-heading"><?php echo JText::_('COM_QAZAP_HOME_ORDER_TRENDS') ?></h1>
	<div class="btn-toolbar">
		<div id="qz-plot-actions" class="add-margin-bottom form-inline">
			<?php echo JHtml::_('select.genericlist', $options, 'qz-plot-period', '', 'value', 'text', 'all', 'qz-plot-period') ?>
			<a href="#" class="active btn btn-link" data-action="value"><?php echo JText::_('COM_QAZAP_PLOT_VALUE') ?></a>
			<a href="#" data-action="count" class="btn btn-link"><?php echo JText::_('COM_QAZAP_PLOT_COUNT') ?></a>
		</div>
	</div>
	<div class="row-fluid">
		<div class="well well-small span12">
			<div class="qz-plot-container">
				<div id="qzPlotHolder" class="qz-plot-placeholder"></div>
				<div id="qz-plot-nodata" class="qz-plot-msgs"><div class="qz-msg-inner"><?php echo JText::_('COM_QAZAP_PLOT_NO_DATA_FOUND') ?></div></div>
				<div id="qz-plot-loading" class="qz-plot-msgs"><div class="qz-msg-inner"><?php echo JText::_('COM_QAZAP_PLOT_LOADING') ?></div></div>
			</div>
		</div>
	</div>
</div>