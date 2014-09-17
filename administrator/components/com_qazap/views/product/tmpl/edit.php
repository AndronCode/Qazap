<?php
/**
 * edit.php
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
JHtml::_('behavior.keepalive');

$document = JFactory::getDocument();
$params = $this->state->get('params');
$minimum_purchase_quantity = $params->get('minimum_purchase_quantity');
$maximum_purchase_quantity = $params->get('maximum_purchase_quantity');
$document->addScriptDeclaration("
minimum_purchase_quantity = $minimum_purchase_quantity;
maximum_purchase_quantity = $maximum_purchase_quantity;
jq(document).ready(function(){
	Qazap.spinnervars();
	Qazap.unsetzero('#qzform_minimum_purchase_quantity, #qzform_purchase_quantity_steps, #qzform_maximum_purchase_quantity');
	Qazap.checkcustomprice();
	Qazap.quantityPricing(minimum_purchase_quantity, maximum_purchase_quantity);
});
");
JText::script('COM_QAZAP_NO_NEGATIVE_ALERT');
JText::script('COM_QAZAP_PRODUCT_INVALID_QUANTITY_ALERT');
JText::script('COM_QAZAP_PRODUCT_MAXIMUM_QUANTITY_ALERT');
JText::script('COM_QAZAP_PRODUCT_MINIMUM_QUANTITY_ALERT');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'product.cancel'){
            Joomla.submitform(task, document.getElementById('product-form'));
        }
        else{
            
            if (task != 'product.cancel' && document.formvalidator.isValid(document.id('product-form'))) {
                
                Joomla.submitform(task, document.getElementById('product-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>
<div id="qazap-system-message-box"></div>
<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=product&layout=edit&product_id=' . (int) $this->item->product_id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="product-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6 form-vertical">
			<?php echo QazapHelper::displayFieldByLang($this->form, 'product_name', true) ?>
		</div>
		<div class="span3 form-vertical">
			<?php echo QazapHelper::displayFieldByLang($this->form, 'product_alias') ?>
		</div>
		<div class="span3 form-vertical">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_sku'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_sku'); ?></div>
			</div>			
		</div>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_PRODUCT_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="form-vertical">
					<?php echo QazapHelper::displayFieldByLang($this->form, 'short_description') ?>
				</fieldset>
				<fieldset class="form-vertical">
					<?php echo QazapHelper::displayFieldByLang($this->form, 'product_description') ?>
				</fieldset>				
			</div>
			<div class="span3">
				<div class="form-vertical side-panel-cont">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('category_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('category_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('vendor'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('vendor'); ?></div>
					</div>
					<?php if($this->params->get('downloadable')) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('downloadable_file'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('downloadable_file'); ?></div>
					</div>
					<?php endif; ?>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('tags'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('tags'); ?></div>
					</div>								
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('block'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('block'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('featured'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('featured'); ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('parent_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('parent_id'); ?></div>
					</div>				
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('manufacturer_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('manufacturer_id'); ?></div>
					</div>		
		
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'pricing', JText::_('COM_QAZAP_PRODUCT_FIELDSET_PRICING_AND_VARIENTS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span9">				
				<div class="pricing-content">
					<div class="pricing-pane<?php echo ($this->item->multiple_pricing == 0 || $this->params->get('multiple_product_pricing', 0) == 0) ? ' active' : ''; ?>" id="standard_pricing">
						<table id="standard_pricing_table" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>
										<span class="acl-action hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_BASEPRICE'), 0) ?>"><?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE') ?>&nbsp;*</span>
									</th>
									<th>
										<span class="acl-action hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_FINALPRICE'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_FINALPRICE'), 0) ?>"><?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_FINALPRICE') ?></span>
									</th>
									<th>
										<span class="acl-action hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_QAZAP_FORM_LBL_PRODUCT_CUSTOMPRICE'), JText::_('COM_QAZAP_FORM_DESC_PRODUCT_CUSTOMPRICE'), 0) ?>"><?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_CUSTOMPRICE') ?></span>
									</th>																			
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<?php echo $this->form->getInput('product_baseprice'); ?>
									</td>
									<td>
										<?php 
										$product = clone $this->item;
										$product->product_customprice = null;
										$salesPrice = QazapHelper::getFinalPrice($product, 'product_salesprice', true) ?>
										<input type="text" id="product_salesprice" name="qzform[product_salesprice]" readonly="true" value ="<?php echo $salesPrice ?>" />
									</td>
									<td>
										<?php echo $this->form->getInput('product_customprice'); ?>
									</td>																		
								</tr>								
							</tbody>	
						</table>
					</div>
					<?php if($this->params->get('multiple_product_pricing', 0)) : ?>
					<div class="pricing-pane<?php echo ($this->item->multiple_pricing == 1) ? ' active' : ''; ?>" id="usergroup_pricing">
						<?php echo $this->form->getInput('product_user_price'); ?>
					</div>
					<div class="pricing-pane<?php echo ($this->item->multiple_pricing == 2) ? ' active' : ''; ?>" id="quantity_pricing">
						<?php echo $this->form->getInput('product_quantity_price'); ?>		
					</div>
					<?php endif; ?>		
					<div class="clear"></div>									
				</div>				
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('cartattributes'); ?>
					</div>
					<div id="qazap-attr-selector" class="controls">
						<?php echo $this->form->getInput('cartattributes'); ?>
					</div>
					<ul id="CartAttributeDetails">					
						<?php 
						if(!empty($this->savedAttributes)) :
							foreach($this->savedAttributes as $tyepid=>$table) : ?>
							<li>
								<table id="qzcustom-group-<?php echo $tyepid ?>" class="qzcustom-group table table-striped table-bordered">
									<tr class="info">
										<td colspan="<?php echo ($table->columns - 1) ?>"><span class="attribute-name"><?php echo $table->title ?></span></td>
										<td><span class="field-sortable-handler"><i class="icon-menu"></i></span></td>
									</tr>
									<tr>
										<?php foreach($table->header as $name) : ?>
										<td>
											<?php echo JText::_($name) ?>
										</td>										
										<?php endforeach; ?>
									</tr>
									<?php foreach($table->rows as $row) : ?>
									<tr class="html-row">
										<?php foreach($row as $html) : ?>
											<td>
												<?php echo $html ?>
											</td>
										<?php endforeach; ?>
									</tr>
									<?php endforeach; ?>							
								</table>
							</li>						
							<?php endforeach; 
						endif; ?>
					</ul>
					<div class="clear"></div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('membership'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('membership'); ?></div>
					</div>											
				</div>	
			</div>
			<div class="span3">
				<div class="form-vertical side-panel-cont">
					<?php if($this->params->get('multiple_product_pricing', 0)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('multiple_pricing'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('multiple_pricing'); ?></div>
					</div>
					<?php else : ?>
						<input type="hidden" name="jform[multiple_pricing]" value="0" />
					<?php endif; ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('dbt_rule_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('dbt_rule_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('dat_rule_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('dat_rule_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('tax_rule_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('tax_rule_id'); ?></div>
					</div>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>			
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'relateditems', JText::_('COM_QAZAP_PRODUCT_FIELDSET_RELATED_ITEMS_FIELDS', true)); ?>
		<div class="row-fluid">
			<div class="span8 form-horizontal-desktop">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('custom_field'); ?></div>
					<div id="qazap-field-selector" class="controls">
						<?php echo $this->form->getInput('custom_field'); ?>
					</div>
				</div>
				<ul id="custom_field_details">					
					<?php
					if(!empty($this->savedFields)) :
						foreach($this->savedFields as $field) : ?>
						<li>
							<table class="custom-field-table">
								<tr class="field-row">
									<td class="field-label"><?php echo $field['title']; ?></td>
									<td class="field-html"><?php echo $field['html']; ?></td>
									<td class="field-order"><span class="field-sortable-handler"><i class="icon-menu"></i></span></td>
									<td class="field-delete"><span class="delete-me" onclick="return Qazap.deleteMe(this);"><i class="icon-cancel"></i></span></td>
								</tr>								
							</table>
						</li>						
						<?php endforeach; 
					endif; ?>
				</ul>
			</div>			
			<div class="span4">
				<div class="form-vertical side-panel-cont">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('related_categories'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('related_categories'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('related_products'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('related_products'); ?></div>
					</div>
				</div>
			</div>									
		</div>			
		<?php echo JHtml::_('bootstrap.endTab'); ?>	
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'images', JText::_('COM_QAZAP_PRODUCT_FIELDSET_IMAGES', true)); ?>
		<div class="product-images">
			<?php echo $this->form->getInput('images'); ?>		
		</div>			
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php if($this->params->get('intangible', 0) == 0 && $this->params->get('downloadable', 0) == 0 ) : ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'stock', JText::_('COM_QAZAP_PRODUCT_FIELDSET_STOCK', true)); ?>
		<div class="form-horizontal-desktop">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('in_stock'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('in_stock'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('ordered'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('ordered'); ?></div>
			</div>				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('booked_order'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('booked_order'); ?></div>
			</div>				
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>	
		<?php endif; ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'packaging', JText::_('COM_QAZAP_PRODUCT_FIELDSET_PACKAGING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_length'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_length'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_width'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_width'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_height'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_height'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_length_uom'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_length_uom'); ?></div>
			</div>			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_weight'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_weight'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_weight_uom'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_weight_uom'); ?></div>
			</div>			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_packaging'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_packaging'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_packaging_uom'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('product_packaging_uom'); ?></div>
			</div>			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('units_in_box'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('units_in_box'); ?></div>
			</div>		
		</div>			
		<?php echo JHtml::_('bootstrap.endTab'); ?>			

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'settings', JText::_('COM_QAZAP_PRODUCT_FIELDSET_SETTINGS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php $fields = $this->form->getFieldset('basic'); // basic fieldset of params displayed under params tab
				if (count($fields)):	?>
				<fieldset class="form-horizontal">
					<?php foreach ($fields as $field) :
						if ($field->hidden):
							echo $field->input;
						else:
						?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input;?>
							</div>
						</div>	
						<?php endif;?>
					<?php endforeach;?>															
				</fieldset>						
				<?php endif;?>			
			</div>
		</div>		
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_QAZAP_PRODUCT_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('urls'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('urls'); ?></div>
				</div>
				<?php $fields = $this->form->getFieldset('publishing'); // publishing fieldset of params displayed under publishing tab
				if (count($fields)):	?>
				<fieldset class="form-horizontal">
					<?php foreach ($fields as $field) :
						if ($field->hidden):
							echo $field->input;
						else://qzdump($field);
						?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input;?>
							</div>
						</div>	
						<?php endif;?>
					<?php endforeach;?>															
				</fieldset>						
				<?php endif;?>					
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_time'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_time'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_by'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_by'); ?></div>
				</div>				
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_time'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_time'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('hits'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('hits'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('product_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('product_id'); ?></div>
				</div>					
			</div>
			<div class="span6">
				<div class="form-horizontal">
					<?php echo QZHelper::displayFieldByLang($this->form, 'page_title', true) ?>
					<?php echo QZHelper::displayFieldByLang($this->form, 'metakey', true) ?>
					<?php echo QZHelper::displayFieldByLang($this->form, 'metadesc', true) ?>
					<?php echo QZHelper::displayFieldByLang($this->form, 'robots', true) ?>
					<?php echo QZHelper::displayFieldByLang($this->form, 'author', true) ?>
					<?php echo QZHelper::displayFieldByLang($this->form, 'rights', true) ?>
					<?php echo QZHelper::displayFieldByLang($this->form, 'xreference', true) ?>	
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>			
		
	</div>	


	<?php $fields = $this->form->getFieldset('language_set'); ?>
	<?php if (count($fields)):?>
		<?php foreach ($fields as $field) : ?>
			<?php echo $field->input;?>
		<?php endforeach;?>
	<?php endif;?>					
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>