<?php
/**
 * tos.php
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

jimport('joomla.application.component.modellist');
/**
 * Methods supporting a list of Qazap records.
 */
class QazapModelTos extends JModelItem 
{
	
	protected $returnError = true;
	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @since	1.0.0
	*/
	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication('site');		
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

	}

	public function dontReturnError()
	{
		$this->returnError = false;
	}	
	/**
	* Method to get article data.
	*
	* @param   integer    The id of the article.
	*
	* @return  mixed  Menu item data object on success, false on failure.
	*/
	public function getItem($language = null)
	{
		$lang = JFactory::getLanguage();
		$language = !empty($language) ? $language : $lang->getTag();
		$default_language = $lang->getDefault();
				
		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if (!isset($this->_item[$language]))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select('shop_id, lang, tos')
						->from('#__qazap_shop')
						->where('lang IN (' . $db->quote($language). ','. $db->quote('*'). ','.  $db->quote($default_language) . ')')
						->group('shop_id');
			
			try
			{
				$db->setQuery($query);
				$datas = $db->loadObjectList('lang');			
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
				return false;
			}
				
			if (empty($datas))
			{
				if($this->returnError === true)
				{
					return JError::raiseError(404, JText::_('COM_QAZAP_ERROR_TOS_NOT_FOUND'));
				}
				else
				{
					$this->setError(JText::_('COM_QAZAP_ERROR_TOS_NOT_FOUND'));
					return false;
				}
			}
			
			if(isset($datas[$language]))
			{
				$tos = $datas[$language]->tos;
			}
			elseif(isset($datas['*']))
			{
				$tos = $datas['*']->tos;
			}
			else
			{
				$tos = $datas[$default_language]->tos;
			}						
			
			$this->_item[$language] = $tos;
		}
		
		return $this->_item[$language];
	}
	
}