<?php
/**
 * default_sorter.php
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

$params = $this->category->params;
?>

<span class="sorter-title"><?php echo JText::_('COM_QAZAP_SORT_BY') ?></span>
<span class="qazap-product-sorter">
	<span class="active-sorter"></span>
	<ul class="qazap-sorter-options">	
	<?php foreach($this->sorter->orders as $order) : 
		$class = $order->active ? ' class="active"' : ''; ?>
		<li<?php echo $class ?>><a href="<?php echo $order->url ?>" title="<?php echo $order->title ?>"><?php echo $order->title ?></a></li>
	<?php endforeach; ?>
	</ul>
</span>
<a class="btn-direction" href="<?php echo $this->sorter->direction->url ?>" title="<?php echo $this->sorter->direction->title ?>">
	<?php echo ($this->sorter->direction->action == 'desc') ? '&uarr;' : '&darr;'; ?>
</a>