<?php
/**
 * default_reviews.php
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
defined('_JEXEC') or die;
$params  = $this->item->params;
?>
<div id="qazap-add-review-list-area" class="qazap-product-reviews-wrap">
	<?php if(count($this->reviews)) : ?>
	<div class="qazap-product-reviews">
		<h4><?php echo JText::_('COM_QAZAP_REVIEWS') ?></h4>
		<?php foreach($this->reviews as $review) : ?>
		<div class="user-product-review-item">
			<div class="user-product-rating-wrap">
				<div class="user-product-rating" data-score="<?php echo (float) $review->rating ?>"></div>
				<span class="review-user-name"><?php echo JText::sprintf('COM_QAZAP_REVIEW_POST_BY', $review->name) ?></span>
				<span class="review-date pull-right"><?php echo JText::sprintf('COM_QAZAP_REVIEW_POST_DATE', QZHelper::displayDate($review->review_date)) ?></span>		
			</div>
			<div class="user-product-comment">
				<p><?php echo $review->comment ?></p>
			</div>					
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<div id="qazap-add-review-area" class="qazap-review-submit-wrap">
		<h4><?php echo JText::_('COM_QAZAP_ADD_REVIEW') ?></h4>
		<?php if(!$this->user->guest) : ?>
			<?php if($this->userReviewDone) : ?>
			<div class="product-user-review-submitted"><?php echo JText::_('COM_QAZAP_MSG_REVIEW_ALREADY_SUBMITTED') ?></div>
			<?php else : ?>			
			<form id="qazap-addreview-form" class="form-vertical" action="<?php echo JRoute::_($this->product_url)?>" method="post">
			  <div class="control-group">
			    <label class="control-label"><?php echo JText::_('COM_QAZAP_RATE_PRODUCT') ?></label>
			    <div class="controls">
			      <div class="user-product-rating-field"></div>
			    </div>
			  </div>		
			  <div class="control-group">
			    <label class="control-label" for="qzform_review"><?php echo JText::_('COM_QAZAP_ADD_COMMENT') ?></label>
			    <div class="controls">
			      <textarea id="qzform_review" class="qazap-comment-box" rows="3" name="qzform[comment]" required="true"></textarea>
			    </div>
			  </div>			
				
				<button type="submit" id="qazap-review-submit-button" class="btn btn-success"><?php echo JText::_('COM_QAZAP_ADD_REVIEW') ?></button>
				<button type="button" id="qazap-review-submit-button" class="btn" onclick="jQ('#qazap-addreview-form').qazap('resetForm');"><?php echo JText::_('COM_QAZAP_RESET') ?></button>
				<input type="hidden" name="qzform[product_id]" value="<?php echo $this->item->product_id ?>" />
				<input type="hidden" name="option" value="com_qazap"/>
				<input type="hidden" name="task" value="product.addreview" />
				<input type="hidden" name="return" value="<?php echo base64_encode($this->product_url) ?> "/>				
				<?php echo JHtml::_('form.token'); ?>
			</form>
			<?php endif; ?>
		<?php else : ?>
			<div class="product-review-login-notice"><?php echo JText::_('COM_QAZAP_MSG_LOGIN_TO_ADD_REVIEW') ?></div>
		<?php endif; ?>
	</div>
</div>