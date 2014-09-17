<?php
/**
 * mod_qazap_categories.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Categories Module
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

$app = JFactory::getApplication();
$presentCat		= $app->input->getInt('category_id', 0);
$cacheid = md5(serialize(array($presentCat, $module->module)));

$cacheparams	= new stdClass;
$cacheparams->cachemode = 'static';
$cacheparams->class = 'ModQazapCategoriesHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
//$cacheparams->modeparams = $cacheid;

$list = JModuleHelper::moduleCache($module, $params, $cacheparams);

if (!empty($list))
{
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
	$startLevel = reset($list)->getParent()->level;
	require JModuleHelper::getLayoutPath('mod_qazap_categories', $params->get('layout', 'default'));
}
