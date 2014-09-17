<?php
/**
 * @package			Qazap
 * @subpackage		Site
 *
 * @author			Qazap Team
 * @link			http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			1.0.0
 */

defined('_JEXEC') or die;

//qzdump($this->item);exit;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$document = JFactory::getDocument();
$params = $this->state->get('params');
$minimum_purchase_quantity = $params->get('minimum_purchase_quantity');
$maximum_purchase_quantity = $params->get('maximum_purchase_quantity');
$css = array('smoothness/jquery-ui-1.10.4.custom.min.css', 'jquery.fancybox-1.3.4.css', 'qazap.css');
$js = array('jquery-ui-1.10.4.custom.min.js', 'jquery.fancybox-1.3.4.pack.js', 'jquery.easing.1.3.min.js', 'spin.min.js', 'qazap.js');
QZApp::loadCSS($css);			
QZApp::loadJS($js);
$document->addScriptDeclaration("
minimum_purchase_quantity = $minimum_purchase_quantity;
maximum_purchase_quantity = $maximum_purchase_quantity;
jQuery(document).ready(function(){
	Qazap.spinnervars();
	Qazap.unsetzero('#jform_minimum_purchase_quantity, #jform_purchase_quantity_steps, #jform_maximum_purchase_quantity');
	Qazap.checkcustomprice();
	Qazap.quantityPricing(minimum_purchase_quantity, maximum_purchase_quantity);
});
");
JText::script('COM_QAZAP_NO_NEGATIVE_ALERT');
JText::script('COM_QAZAP_PRODUCT_INVALID_QUANTITY_ALERT');
JText::script('COM_QAZAP_PRODUCT_MAXIMUM_QUANTITY_ALERT');
JText::script('COM_QAZAP_PRODUCT_MINIMUM_QUANTITY_ALERT');

// Create shortcut to parameters.
$params = $this->state->get('params');
//$images = json_decode($this->item->images);
//$urls = json_decode($this->item->urls);

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if (!$editoroptions)
{
	$params->show_urls_images_frontend = '0';
}
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'product.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{			
			Joomla.submitform(task);
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<div id="qazap-system-message-box"></div>
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->title); ?>
		</h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_qazap&product_id='.(int) $this->item->product_id); ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm" class="form-validate label-align-left">		
		<div class="btn-toolbar align-right">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('product.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('product.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<div>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_GENERAL') ?></a></li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_PUBLISHING') ?></a></li>
				<li><a href="#pricing" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_PRICING_AND_VARIENTS') ?></a></li>
				<li><a href="#relateditems" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_RELATED_ITEMS_FIELDS') ?></a></li>
				<li><a href="#images" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_IMAGES') ?></a></li>
				
				<?php if($params->get('intangible', 0) == 0 && $params->get('downloadable', 0) == 0 ) : ?>
				<li><a href="#stock" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_STOCK') ?></a></li>
				<?php endif; ?>
				
				<li><a href="#packaging" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_PACKAGING') ?></a></li>
				
				<li><a href="#settings" data-toggle="tab"><?php echo JText::_('COM_QAZAP_PRODUCT_FIELDSET_STOCK_SETTINGS') ?></a></li>				
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="general">
					<?php echo QZHelper::displayFieldByLang($this->form, 'product_name', true) ?>
					<?php if (is_null($this->item->product_id)) : ?>
						<?php echo QZHelper::displayFieldByLang($this->form, 'product_alias') ?>
					<?php endif; ?>
					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('product_sku'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('product_sku'); ?></div>
					</div>
						
					<fieldset class="form-vertical">
						<?php echo QZHelper::displayFieldByLang($this->form, 'short_description') ?>
					</fieldset>
					<fieldset class="form-vertical">
						<?php echo QZHelper::displayFieldByLang($this->form, 'product_description') ?>
					</fieldset>	
				</div>

				<div class="tab-pane form-horizontal" id="publishing">					
					<div class="row-fluid">
						<div class="span12">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('category_id'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('category_id'); ?></div>
							</div>
							<?php if($params->get('downloadable')) : ?>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('downloadable_file'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('downloadable_file'); ?></div>
							</div>
							<?php endif; ?>					
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('tags'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('tags'); ?></div>
							</div>		
							<?php if($this->user->authorise('core.edit.state', 'com_qazap')) : ?>						
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
							</div>
							<?php endif; ?>
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
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
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('urls'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('urls'); ?></div>
							</div>
							<?php
							/* You can also display the meta fields if you want
							echo QZHelper::displayFieldByLang($this->form, 'page_title', true) 
							echo QZHelper::displayFieldByLang($this->form, 'metakey', true) 
							echo QZHelper::displayFieldByLang($this->form, 'metadesc', true) 
							echo QZHelper::displayFieldByLang($this->form, 'robots', true) 
							echo QZHelper::displayFieldByLang($this->form, 'author', true) 
							echo QZHelper::displayFieldByLang($this->form, 'rights', true) 
							echo QZHelper::displayFieldByLang($this->form, 'xreference', true) 
							*/ ?>																						
						</div>				
					</div>	
				</div>	
				
				<div class="tab-pane form-horizontal" id="pricing">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('multiple_pricing'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('multiple_pricing'); ?></div>
					</div>				
					<div class="pricing-content">
						<div class="pricing-pane<?php echo ($this->item->multiple_pricing == 0) ? ' active' : ''; ?>" id="standard_pricing">
							<table id="standard_pricing_table" class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>
											<span class="acl-action"><?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_BASEPRICE') ?>&nbsp;*</span>
										</th>
										<th>
											<span class="acl-action"><?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_FINALPRICE') ?></span>
										</th>
										<th>
											<span class="acl-action"><?php echo JText::_('COM_QAZAP_FORM_LBL_PRODUCT_CUSTOMPRICE') ?></span>
										</th>																			
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<?php echo $this->form->getInput('product_baseprice'); ?>
										</td>
										<td>
											<?php $salesPrice = $this->item->product_baseprice ? QazapHelper::currencyDisplay(QazapHelper::getFinalPrice($this->item->product_baseprice, $this->item->product_id, 'product_salesprice')) : ''; ?>
											<input type="text" id="product_salesprice" name="qzform[product_salesprice]" readonly="true" value ="<?php echo $salesPrice ?>"/>
										</td>
										<td>
											<?php echo $this->form->getInput('product_customprice'); ?>
										</td>																		
									</tr>								
								</tbody>	
							</table>
						</div>
						<div class="pricing-pane<?php echo ($this->item->multiple_pricing == 1) ? ' active' : ''; ?>" id="usergroup_pricing">
							<?php echo $this->form->getInput('product_user_price'); ?>
						</div>
						<div class="pricing-pane<?php echo ($this->item->multiple_pricing == 2) ? ' active' : ''; ?>" id="quantity_pricing">
							<?php echo $this->form->getInput('product_quantity_price'); ?>		
						</div>		
						<div class="clear"></div>									
					</div>
					<hr/>		
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
						<hr/>	
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('membership'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('membership'); ?></div>
						</div>											
					</div>
					<hr/>	
					<div class="form-horizontal">
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
				
				<div class="tab-pane form-horizontal" id="relateditems">
					<div class="form-horizontal">
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
						<hr/>
						<div class="form-horizontal">
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
				
				<div class="tab-pane" id="images">
					<div class="row-fluid">
						<div class="span12">
							<div class="product-images">
								<?php echo $this->form->getInput('images'); ?>		
							</div>								
						</div>
					</div>
				</div>
				
				<?php if($params->get('intangible', 0) == 0 && $params->get('downloadable', 0) == 0 ) : ?>
				<div class="tab-pane" id="stock">
					<div class="row-fluid">
						<div class="span12">
							<fieldset class="form-horizontal">
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
							</fieldset>							
						</div>
					</div>
				</div>				
				<?php endif; ?>

				<div class="tab-pane" id="packaging">
					<div class="row-fluid">
						<div class="span12">
							<fieldset class="form-horizontal">
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
							</fieldset>								
						</div>
					</div>
				</div>
				
				<div class="tab-pane" id="settings">
					<div class="row-fluid">
						<div class="span12">
							<?php foreach ($this->form->getFieldsets('params') as $key=>$fieldset): ?>			
								<?php $fields = $this->form->getFieldset($fieldset->name);
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
							<?php endforeach;?>						
						</div>
					</div>
				</div>				
			</div>

		</div>
		<input type="hidden" name="task" value="" />
		<?php $fields = $this->form->getFieldset('language_set'); ?>
		<?php if (count($fields)):?>
			<?php foreach ($fields as $field) : ?>
				<?php echo $field->input; ?>
			<?php endforeach;?>
		<?php endif;?>	
		<?php echo $this->form->getInput('block'); ?>	
		<?php echo $this->form->getInput('vendor'); ?>		
		<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
		<?php echo JHtml::_('form.token'); ?>		
	</form>
</div>
