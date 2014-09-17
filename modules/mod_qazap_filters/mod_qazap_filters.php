<?php
/**
 * mod_qazap_filters.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Qazap Filters Module
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
if(!class_exists('ModQazapFiltersHelper'))
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$headerText	= JString::trim($params->get('header_text'));
$footerText	= JString::trim($params->get('footer_text'));

$attribute_groups	= ModQazapFiltersHelper::getAttributes();
$brands						= ModQazapFiltersHelper::getBrands();
$prices						= ModQazapFiltersHelper::getPrices();

$category_id 								= $app->input->getInt('category_id', 0);
$urlvars 										= array();
$urlvars['search']					= $app->input->getString('filter_search');	
$urlvars['searchphrase']		= $app->input->getString('searchphrase');
$urlvars['vendor_id']				= $app->input->get('vendor_id', null, 'array');
$action_url									= QazapHelperRoute::getCategoryRoute($category_id, 0, 0, '', '', $urlvars);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

if(!empty($brands) || !empty($attribute_groups) || !empty($prices))
{
	require JModuleHelper::getLayoutPath('mod_qazap_filters', $params->get('layout', 'default'));
}