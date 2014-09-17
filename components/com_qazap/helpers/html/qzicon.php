<?php
/**
 * qzicon.php
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

/**
 * Content Component HTML Helper
 *
 * @package     Joomla.Site
 * @subpackage  com_qazap
 * @since       1.0.0
 */
abstract class JHtmlQzicon
{
	/**
	* Method to generate a link to the create item page for the given category
	*
	* @param   object     $category  The category information
	* @param   JRegistry  $params    The item parameters
	* @param   array      $attribs   Optional attributes for the link
	* @param   boolean    $legacy    True to use legacy images, false to use icomoon based graphic
	*
	* @return  string  The HTML markup for the create item link
	*/
	public static function create($category, $params, $attribs = array(), $legacy = false)
	{
		JHtml::_('bootstrap.tooltip');

		$uri = JUri::getInstance();

		$url = 'index.php?option=com_qazap&task=product.add&return=' . base64_encode($uri) . '&product_id=0&category_id=' . $category->category_id;

		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/new.png', JText::_('JNEW'), null, true);
			}
			else
			{
				$text = '<span class="icon-plus"></span>&#160;' . JText::_('JNEW') . '&#160;';
			}
		}
		else
		{
			$text = JText::_('JNEW') . '&#160;';
		}

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] = $attribs['class'] . ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = JHtml::_('link', JRoute::_($url), $text, $attribs);

		$output = '<span class="hasTooltip" title="' . JHtml::tooltipText('COM_QAZAP_CREATE_PRODUCT') . '">' . $button . '</span>';

		return $output;
	}

	/**
	* Method to generate a link to the email item page for the given product
	*
	* @param   object     $product  The product information
	* @param   JRegistry  $params   The item parameters
	* @param   array      $attribs  Optional attributes for the link
	* @param   boolean    $legacy   True to use legacy images, false to use icomoon based graphic
	*
	* @return  string  The HTML markup for the email item link
	*/
	public static function email($product, $params, $attribs = array(), $legacy = false)
	{
		require_once JPATH_SITE . '/components/com_mailto/helpers/mailto.php';

		$uri      = JUri::getInstance();
		$base     = $uri->toString(array('scheme', 'host', 'port'));
		$template = JFactory::getApplication()->getTemplate();
		$link     = $base . JRoute::_(QazapHelperRoute::getProductRoute($product->slug, $product->category_id), false);
		$url      = 'index.php?option=com_mailto&tmpl=component&template=' . $template . '&link=' . MailToHelper::addLink($link);

		$status = 'width=400,height=350,menubar=yes,resizable=yes';

		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/emailButton.png', JText::_('JGLOBAL_EMAIL'), null, true);
			}
			else
			{
				$text = '<span class="icon-envelope"></span> ' . JText::_('JGLOBAL_EMAIL');
			}
		}
		else
		{
			$text = JText::_('JGLOBAL_EMAIL');
		}

		$attribs['title']   = JText::_('JGLOBAL_EMAIL');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";

		$output = JHtml::_('link', JRoute::_($url), $text, $attribs);

		return $output;
	}

	/**
	* Display an edit icon for the product.
	*
	* This icon will not display in a popup window, nor if the product is trashed.
	* Edit access checks must be performed in the calling code.
	*
	* @param   object     $product  The product information
	* @param   JRegistry  $params   The item parameters
	* @param   array      $attribs  Optional attributes for the link
	* @param   boolean    $legacy   True to use legacy images, false to use icomoon based graphic
	*
	* @return  string	The HTML for the product edit icon.
	* @since   1.0.0
	*/
	public static function edit($product, $params, $attribs = array(), $legacy = false)
	{
		$user = JFactory::getUser();
		$uri  = JUri::getInstance();

		// Ignore if the state is negative (trashed).
		if ($product->state < 0)
		{
			return;
		}

		JHtml::_('bootstrap.tooltip');

		// Show checked_out icon if the product is checked out by a different user
		if (property_exists($product, 'checked_out') && property_exists($product, 'checked_out_time') && $product->checked_out > 0 && $product->checked_out != $user->get('id'))
		{
			$checkoutUser = JFactory::getUser($product->checked_out);
			$button       = JHtml::_('image', 'system/checked_out.png', null, null, true);
			$date         = JHtml::_('date', $product->checked_out_time);
			$tooltip      = JText::_('JLIB_HTML_CHECKED_OUT') . ' :: ' . JText::sprintf('COM_QAZAP_CHECKED_OUT_BY', $checkoutUser->name) . ' <br /> ' . $date;

			return '<span class="hasTooltip" title="' . JHtml::tooltipText($tooltip. '', 0) . '">' . $button . '</span>';
		}

		$url = 'index.php?option=com_qazap&task=product.edit&product_id=' . $product->product_id . '&return=' . base64_encode($uri);

		if ($product->state == 0)
		{
			$overlib = JText::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = JText::_('JPUBLISHED');
		}

		$date   = JHtml::_('date', $product->created_time);

		$overlib .= '&lt;br /&gt;';
		$overlib .= $date;
		$overlib .= '&lt;br /&gt;';
		$overlib .= JText::sprintf('COM_QAZAP_SELLER', htmlspecialchars($product->shop_name, ENT_COMPAT, 'UTF-8'));

		if ($legacy)
		{
			$icon = $product->state ? 'edit.png' : 'edit_unpublished.png';
			if ($product->block)
			{
				$icon = 'edit_unpublished.png';
			}
			$text = JHtml::_('image', 'system/' . $icon, JText::_('JGLOBAL_EDIT'), null, true);
		}
		else
		{
			$icon = $product->state ? 'edit' : 'eye-close';
			if ($product->block)
			{
				$icon = 'eye-close';
			}
			$text = '<span class="hasTooltip icon-' . $icon . ' tip" title="' . JHtml::tooltipText(JText::_('COM_QAZAP_EDIT_PRODUCT'), $overlib, 0) . '"></span>&#160;' . JText::_('JGLOBAL_EDIT') . '&#160;';
		}

		$output = JHtml::_('link', JRoute::_($url), $text, $attribs);

		return $output;
	}

	/**
	* Method to generate a popup link to print an product
	*
	* @param   object     $product  The product information
	* @param   JRegistry  $params   The item parameters
	* @param   array      $attribs  Optional attributes for the link
	* @param   boolean    $legacy   True to use legacy images, false to use icomoon based graphic
	*
	* @return  string  The HTML markup for the popup link
	*/
	public static function print_popup($product, $params, $attribs = array(), $legacy = false)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$request = $input->request;

		$url  = QazapHelperRoute::getProductRoute($product->slug, $product->category_id);
		$url .= '&tmpl=component&print=1&layout=default&page=' . @ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);
			}
			else
			{
				$text = '<span class="icon-print"></span>&#160;' . JText::_('JGLOBAL_PRINT') . '&#160;';
			}
		}
		else
		{
			$text = JText::_('JGLOBAL_PRINT');
		}

		$attribs['title']   = JText::_('JGLOBAL_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

	/**
	* Method to generate a link to print an product
	*
	* @param   object     $product  Not used, @deprecated for 4.0
	* @param   JRegistry  $params   The item parameters
	* @param   array      $attribs  Not used, @deprecated for 4.0
	* @param   boolean    $legacy   True to use legacy images, false to use icomoon based graphic
	*
	* @return  string  The HTML markup for the popup link
	*/
	public static function print_screen($product, $params, $attribs = array(), $legacy = false)
	{
		// Checks template image directory for image, if none found default are loaded
		if ($params->get('show_icons'))
		{
			if ($legacy)
			{
				$text = JHtml::_('image', 'system/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);
			}
			else
			{
				$text = '<span class="icon-print"></span>&#160;' . JText::_('JGLOBAL_PRINT') . '&#160;';
			}
		}
		else
		{
			$text = JText::_('JGLOBAL_PRINT');
		}

		return '<a href="#" onclick="window.print();return false;">' . $text . '</a>';
	}
}
