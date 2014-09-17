<?php
/**
 * mod_qazap_search.php
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

// Include the helper functions only once
require_once dirname(__FILE__) . '/helper.php';

$lang = JFactory::getLanguage();
$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$cacheparams								= new stdClass;
$cacheparams->cachemode			= 'static';
$cacheparams->class					= 'ModQazapSearchHelper';
$cacheparams->method				= 'getCategoryOptions';
$cacheparams->methodparams	= $params;
$options										= JModuleHelper::moduleCache($module, $params, $cacheparams);

$attr							= '';
$presentCat				= $app->input->getInt('category_id', 0);
$categoryLabel		= JText::_('MOD_QAZAP_SEARCH_CATEGORIES');
$categorySelect		= JHtml::_('select.genericlist', $options, 'searchcategory', trim($attr), 'value', 'text', $presentCat, 'mod-qazap-search-category');

$upper_limit 			= $lang->getUpperLimitSearchWord();
$button						= $params->get('button', 0);
$imagebutton			= $params->get('imagebutton', 0);
$button_pos				= $params->get('button_pos', 'left');
$button_text			= htmlspecialchars($params->get('button_text', JText::_('MOD_QAZAP_SEARCH_SEARCHBUTTON_TEXT')));
$width						= (int) $params->get('width', 20);
$maxlength				= $upper_limit;
$text							= htmlspecialchars($params->get('text', JText::_('MOD_QAZAP_SEARCH_SEARCHBOX_TEXT')));
$label						= htmlspecialchars($params->get('label', JText::_('MOD_QAZAP_SEARCH_LABEL_TEXT')));
$search_word			= $app->input->getString('filter_search', $text);
$params->set('text', $search_word);
$moduleclass_sfx	= htmlspecialchars($params->get('moduleclass_sfx'));

if($imagebutton)
{
	$img = ModQazapSearchHelper::getSearchImage($button_text);
}

require JModuleHelper::getLayoutPath('mod_qazap_search', $params->get('layout', 'default'));

