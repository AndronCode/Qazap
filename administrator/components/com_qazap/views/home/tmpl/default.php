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
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.keepalive');

$doc = JFactory::getDocument();
$doc->addStyleDeclaration(".subhead-collapse .subhead{height:3px;}")
?>
<div class="row-fluid">
  <div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
  </div>
  <div id="j-main-container" class="qz-home-container span10">
    <div class="row-fluid">
      <div class="well well-small span6">
        <h2 class="module-title nav-header"><?php echo JText::_('COM_QAZAP_HOME_BEST_SELLING_PRODUCTS') ?></h2>
        <div class="row-striped">
        	<?php if(!empty($this->topselling_products)) : ?>
	        	<?php foreach($this->topselling_products as $key => $product) : ?>
	          <div class="row-fluid">
	            <div class="span9">
	            	<?php if(($product->ordered + $product->booked_order) > 0) : ?>
	              <span class="badge badge-info hasTooltip" title="<?php echo JText::_('COM_QAZAP_HOME_PRODUCT_SOLD') ?>"><?php echo ($product->ordered + $product->booked_order) ?></span>
	              <?php else : ?>
	              <span class="badge hasTooltip" title="<?php echo JText::_('COM_QAZAP_HOME_PRODUCT_SOLD') ?>"><?php echo ($product->ordered + $product->booked_order) ?></span>
	              <?php endif; ?>	              
              	<?php if(!empty($product->checked_out)) : ?>
                	<?php echo JHtml::_('jgrid.checkedout', $key, $product->editor, $product->checked_out_time, '', false); ?>
                <?php endif; ?>
      					<strong class="row-title">
                  <a href="<?php echo JRoute::_('index.php?option=com_qazap&task=product.edit&product_id=' . $product->product_id) ?>"><?php echo $this->escape($product->product_name) ?></a>	                  
                </strong>
                <small class="small hasTooltip" title="<?php echo JText::_('COM_QAZAP_TITLE_VENDOR') ?>"><?php echo $this->escape($product->shop_name) ?></small>
	            </div>
	            <div class="span3 right">
	              <span class="small hasTooltip" title="<?php echo JText::_('COM_QAZAP_FORM_PRODUCT_SALESPRICE') ?>"><?php echo QazapHelper::currencyDisplay(QazapHelper::getFinalPrice($product, 'product_salesprice')); ?></span>
	            </div>
	          </div>
	          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>       

      <div class="well well-small span6">
        <h2 class="module-title nav-header"><?php echo JText::_('COM_QAZAP_HOME_BEST_LATEST_PRODUCTS') ?></h2>
        <div class="row-striped">
        	<?php if(!empty($this->latest_products)) : ?>
	        	<?php foreach($this->latest_products as $key => $product) : ?>
	          <div class="row-fluid">
	            <div class="span9">
	            	<?php if(($product->ordered + $product->booked_order) > 0) : ?>
	              <span class="badge badge-info hasTooltip" title="<?php echo JText::_('COM_QAZAP_HOME_PRODUCT_SOLD') ?>"><?php echo ($product->ordered + $product->booked_order) ?></span>
	              <?php else : ?>
	              <span class="badge hasTooltip" title="<?php echo JText::_('COM_QAZAP_HOME_PRODUCT_SOLD') ?>"><?php echo ($product->ordered + $product->booked_order) ?></span>
	              <?php endif; ?>	              
              	<?php if(!empty($product->checked_out)) : ?>
                	<?php echo JHtml::_('jgrid.checkedout', $key, $product->editor, $product->checked_out_time, '', false); ?>
                <?php endif; ?>
      					<strong class="row-title">
                  <a href="<?php echo JRoute::_('index.php?option=com_qazap&task=product.edit&product_id=' . $product->product_id) ?>"><?php echo $this->escape($product->product_name) ?></a>	                  
                </strong>
                <small class="small hasTooltip" title="<?php echo JText::_('COM_QAZAP_TITLE_VENDOR') ?>"><?php echo $this->escape($product->shop_name) ?></small>
	            </div>
	            <div class="span3 right">
	              <span class="small"><i class="icon-calendar"></i> <?php echo JHtml::_('date', $product->created_time, 'Y-m-d'); ?></span>
	            </div>
	          </div>
	          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>      
    </div>  

    <div class="row-fluid">
      <div class="well well-small span6">
        <h2 class="module-title nav-header"><?php echo JText::_('COM_QAZAP_HOME_BEST_RECENT_ORDERS') ?></h2>
        <div class="row-striped">
        	<?php if(!empty($this->orders)) : ?>
	        	<?php foreach($this->orders as $group) : ?>
	          <div class="row-fluid">
	            <div class="span6">          
      					<strong class="row-title">
                  <a href="<?php echo JRoute::_('index.php?option=com_qazap&task=order.edit&ordergroup_id=' . (int) $group->ordergroup_id) ?>"><?php echo $this->escape($group->ordergroup_number) ?></a>	                  
                </strong>
	              <span class="badge badge-info hasTooltip" title="<?php echo JText::_('COM_QAZAP_ORDERGROUP_ORDER_STATUS_LABEL') ?>"><?php echo QazapHelper::orderStatusNameByCode($group->order_status) ?></span>                  
	            </div>
	            <div class="span3 right">
                <span class="small hasTooltip" title="<?php echo JText::_('COM_QAZAP_ORDERGROUP_CART_TOTAL_LABEL') ?>"><?php echo QazapHelper::currencyDisplay($group->cart_total); ?></span>
	            </div>	            
	            <div class="span3 right">
	              <span class="small"><i class="icon-calendar"></i> <?php echo JHtml::_('date', $group->created_on, 'Y-m-d'); ?></span>
	            </div>
	          </div>
	          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div> 
      
      <?php echo $this->loadTemplate('plot') ?>
		</div>
		
		<div class="row-fluid">
			<div class="span12">
				<a href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_qazap') ?>" class="btn btn-large qzbtn-large" title="<?php echo JText::_('COM_QAZAP_SIDEBAR_GLOBAL_CONFIGURATION') ?>">
					<i class="qzicon-settings qzicon-largesize"></i>
					<span><?php echo JText::_('COM_QAZAP_SIDEBAR_GLOBAL_CONFIGURATION') ?></span>
				</a>					
				<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=products') ?>" class="btn btn-large qzbtn-large" title="<?php echo JText::_('COM_QAZAP_SIDEBAR_PRODUCT_MANAGER') ?>">
					<i class="qzicon-stack qzicon-largesize"></i>
					<span><?php echo JText::_('COM_QAZAP_SIDEBAR_PRODUCT_MANAGER') ?></span>
				</a>
				<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=categories') ?>" class="btn btn-large qzbtn-large" title="<?php echo JText::_('COM_QAZAP_SIDEBAR_CATEGORY_MANAGER') ?>">
					<i class="qzicon-tree4 qzicon-largesize"></i>
					<span><?php echo JText::_('COM_QAZAP_SIDEBAR_CATEGORY_MANAGER') ?></span>
				</a>
				<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=orders') ?>" class="btn btn-large qzbtn-large" title="<?php echo JText::_('COM_QAZAP_SIDEBAR_ORDER_MANAGER') ?>">
					<i class="qzicon-paste2 qzicon-largesize"></i>
					<span><?php echo JText::_('COM_QAZAP_SIDEBAR_ORDER_MANAGER') ?></span>
				</a>
				<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=vendors') ?>" class="btn btn-large qzbtn-large" title="<?php echo JText::_('COM_QAZAP_SIDEBAR_VENDOR_MANAGER') ?>">
					<i class="qzicon-brain qzicon-largesize"></i>
					<span><?php echo JText::_('COM_QAZAP_SIDEBAR_VENDOR_MANAGER') ?></span>
				</a>
				<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=userinfos') ?>" class="btn btn-large qzbtn-large" title="<?php echo JText::_('COM_QAZAP_SIDEBAR_USER_MANAGER') ?>">
					<i class="qzicon-user6 qzicon-largesize"></i>
					<span><?php echo JText::_('COM_QAZAP_SIDEBAR_USER_MANAGER') ?></span>
				</a>																
			</div>
		</div>

    
  </div>
</div>