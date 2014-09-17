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
 * @subpackage Qazap Search Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;
$doc->addStyleSheet(JURI::base(true) . '/modules/mod_qazap_search/assets/css/module.css');
?>
<div class="qazap-search<?php echo $moduleclass_sfx ?>">
	<form action="<?php echo JRoute::_('index.php');?>" method="post" class="form-vertical">
		<?php
			$output = '';
			if($params->get('show_categories', 1)) 
			{
				$output .= '<div class="control-group">';
				$output .= '<label for="mod-qazap-search-category" class="element-invisible">' . $categoryLabel . '</label> ';
				$output .= '<div class="controls">';
				$output .= $categorySelect;			
				$output .= '</div>';
				$output .= '</div>';	
			}
			$output .= '<div class="control-group">';
			$output .= '<label for="mod-qazap-search-searchword" class="element-invisible">' . $label . '</label> ';
			$output .= '<div class="controls">';
			$output .= '<input name="searchword" id="mod-qazap-search-searchword" maxlength="' . $maxlength . '"  class="inputbox" type="text" size="' . $width . '" value="' . $search_word . '"  onblur="if (this.value==\'\') this.value=\'' . $text . '\';" onfocus="if (this.value==\'' . $text . '\') this.value=\'\';" onchange="this.form.submit();" placeholder="' . $text .'"/>';
			$output .= '</div>';
			$output .= '</div>';

			if ($button) :
				if ($imagebutton) :
					$btn_output = ' <input type="image" value="' . $button_text . '" class="button" src="' . $img . '" onclick="this.form.searchword.focus();"/>';
				else :
					$btn_output = ' <button class="button btn btn-primary" onclick="this.form.searchword.focus();">' . $button_text . '</button>';
				endif;

				switch ($button_pos) :
					case 'top' :
						$output = $btn_output . '<br />' . $output;
						break;

					case 'bottom' :
						$output .= '<br />' . $btn_output;
						break;

					case 'right' :
						$output .= $btn_output;
						break;

					case 'left' :
					default :
						$output = $btn_output . $output;
						break;
				endswitch;

			endif;

			echo $output;
		?>
		<input type="hidden" name="task" value="category.search" />
		<input type="hidden" name="option" value="com_qazap" />
	</form>
</div>