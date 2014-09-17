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
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){
        
    });
    
    Joomla.submitbutton = function(task)
    {
        if(task == 'category.cancel')
		{
            Joomla.submitform(task, document.getElementById('category-form'));
        }
        else{
            
            if (task != 'category.cancel' && document.formvalidator.isValid(document.id('category-form'))) 
			{
                
                Joomla.submitform(task, document.getElementById('category-form'));
            }
            else 
			{
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=category&layout=edit&category_id=' . (int) $this->item->category_id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="category-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6 form-vertical">
			<?php echo QazapHelper::displayFieldByLang($this->form, 'title', true) ?>
		</div>
		<div class="span6 form-vertical">
			<?php echo QazapHelper::displayFieldByLang($this->form, 'alias'); ?>
		</div>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_QAZAP_CATEGORY_FIELDSET_GENERAL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform form-vertical">
					<?php echo QazapHelper::displayFieldByLang($this->form, 'description') ?>
				</fieldset>
			</div>
			<div class="span3">
				<div class="form-vertical side-panel-cont">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('parent_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('parent_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('note'); ?></div>
					</div>		
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_QAZAP_CATEGORY_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_time'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_time'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_user_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_user_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_time'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_time'); ?></div>
				</div>	
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_user_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_user_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('hits'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('hits'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('category_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('category_id'); ?></div>
				</div>																									
			</div>
			<div class="span6">
				<?php echo QazapHelper::displayFieldByLang($this->form, 'page_title'); ?>
				<?php echo QazapHelper::displayFieldByLang($this->form, 'metadesc'); ?>
				<?php echo QazapHelper::displayFieldByLang($this->form, 'metakey'); ?>
				<?php echo QazapHelper::displayFieldByLang($this->form, 'author'); ?>
				<?php echo QazapHelper::displayFieldByLang($this->form, 'robots'); ?>
			</div>			
			
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>					
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'images', JText::_('COM_QAZAP_CATEGORY_FIELDSET_IMAGES', true)); ?>
		<div class="product-images">
			<?php echo $this->form->getInput('images'); ?>		
		</div>			
		<?php echo JHtml::_('bootstrap.endTab'); ?>	
		
		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
		
		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permission', JText::_('COM_QAZAP_CATEGORY_FIELDSET_PERMISSION', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>		
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>			
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