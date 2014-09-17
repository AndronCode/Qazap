<?php
/**
 * qzdate.php
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
defined('JPATH_BASE') or die;

/**
 * Utility class for categories
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.0.0
 */
abstract class JHtmlQZDate
{
	public static function diff(DateInterval $interval, $hideSeconds = false, $inDays = false)
	{
		if($inDays)
		{
			if($interval->days)
			{
				return $interval->days . ' ' . ($interval->days > 1 ? JText::_('COM_QAZAP_DATE_DAYS') : JText::_('COM_QAZAP_DATE_DAY'));
			}
			
			return null;
		}
		
		$result = array();
	
		if($interval->y)
		{
			$result[] = $interval->y . ' ' . ($interval->y > 1 ? JText::_('COM_QAZAP_DATE_YEARS') : JText::_('COM_QAZAP_DATE_YEAR')) ;
		}
		if($interval->m)
		{
			$result[] = $interval->m . ' ' . ($interval->m > 1 ? JText::_('COM_QAZAP_DATE_MONTHS') : JText::_('COM_QAZAP_DATE_MONTH')) ;
		}
		if($interval->d)
		{
			$result[] = $interval->d . ' ' . ($interval->d > 1 ? JText::_('COM_QAZAP_DATE_DAYS') : JText::_('COM_QAZAP_DATE_DAY')) ;
		}
		if($interval->h)
		{
			$result[] = $interval->h . ' ' . ($interval->h > 1 ? JText::_('COM_QAZAP_DATE_HOURS') : JText::_('COM_QAZAP_DATE_HOUR')) ;
		}
		if($interval->i)
		{
			$result[] = $interval->i . ' ' . ($interval->i > 1 ? JText::_('COM_QAZAP_DATE_MINUTES') : JText::_('COM_QAZAP_DATE_MINUTE')) ;
		}	
		if(!$hideSeconds && $interval->s)
		{
			$result[] = $interval->s . ' ' . ($interval->s > 1 ? JText::_('COM_QAZAP_DATE_SECONDS') : JText::_('COM_QAZAP_DATE_SECOND')) ;
		}
		
		if(empty($result))
		{
			return null;
		}	
		
		if(count($result) > 1)
		{
			end($result);
			$key = (int) key($result);
			$last = $result[$key];
			$result[$key] = JText::_('COM_QAZAP_DATE_AND');
			$result[$key+1] = $last;
			reset($result);
		}	
		
		return implode(' ', $result);	
	}

}
