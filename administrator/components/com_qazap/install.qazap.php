<?php
/**
 * install.qazap.php
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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of Ola component
 */
class com_qazapInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		// $parent is the class calling this method
		//$parent->getParent()->setRedirectURL('index.php?option=com_qazap');
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		if($this->_InstlTableExists())
		{
			$extensions = array();
			
			$modules = $this->_disableModules();
			
			if($modules === false)
			{
				$extensions[] = 'Modules';
			}
			
			$plugins = $this->_disablePlugins();
			
			if($plugins === false)
			{
				$extensions[] = 'Plugins';
			}
			
			if(!empty($extensions))
			{
				$extensions = implode('and ', $extensions);
				
				ob_start();
				?>
				<div class="alert alert-block alert-error" style="margin-bottom: 40px;line-height: 2em;">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<p style="font-size:20px;margin-bottom: 20px;">All Qazap <?php echo $extensions ?> could not be uninstalled properly from your site. Please uninstall them one by one from Joomla Extension Manager otherwise you may find some problem accessing your website.</p>
				</div>
				<?php
				$html = ob_get_contents();
				@ob_end_clean();			
				echo $html;				
			}
			elseif(!empty($modules) || !empty($plugins))
			{
				ob_start();
				?>
				<div class="alert alert-block alert-info" style="margin-bottom: 40px;line-height: 2em;">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<p style="font-size:20px;margin-bottom: 20px;">Also please uninstall the following extensions as they areno longer required in your site.</p>
					<?php if(!empty($modules)) : ?>
						<p><strong>Modules: </strong><?php echo implode(',', $modules) ?></p>
					<?php endif; ?>
					<?php if(!empty($plugins)) : ?>
						<p><strong>Plugins: </strong><?php echo implode(',', $plugins) ?></p>
					<?php endif; ?>					
				</div>
				<?php
				$html = ob_get_contents();
				@ob_end_clean();			
				echo $html;					
			}				
		}
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{
		// $parent is the class calling this method
		//echo '<p>' . JText::_('com_qazap update script') . '</p>';
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		//echo '<p>' . JText::_('com_qazap pre flight script') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		if(strtolower($type) != 'uninstall')
		{
			$qazapVersion	= $parent->get('manifest')->version;
			
			if(version_compare(JVERSION, '3.3.1', '<') && version_compare($qazapVersion, '1.0.0', '>='))
			{
				JError::raiseNotice(1, 'Qazap 1.0.x requires minimum Joomla! CMS 3.3.1');
				return false;
			}
			
			// Load installation language
			JFactory::getLanguage()->load('com_qazap.instl', JPATH_ADMINISTRATOR);

			$destination = JPath::clean(JPATH_ROOT . '/administrator/components/com_qazap/');
			$buffer      = JText::_('COM_QAZAP_INSTL_STARTING_WIZARD') . "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t" . JFactory::getDate();

			if(!$this->_createInstlTable())
			{
				ob_start();
				?>
				<div class="alert alert-block alert-error" style="margin-bottom: 40px;line-height: 2em;">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<p style="font-size:20px;margin-bottom: 20px;"><?php echo JText::_('COM_QAZAP_INSTL_PRECHECK_TABLE_CREATION_FAILED_MSG') ?></p>
				</div>
				<?php
				$html = ob_get_contents();
				@ob_end_clean();
			}
			elseif(!JFile::write($destination . 'installer.log.ini', $buffer))
			{
				ob_start();
				?>
				<div class="alert alert-block alert-error" style="margin-bottom: 40px;line-height: 2em;">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<p style="font-size:20px;margin-bottom: 20px;"><?php echo JText::sprintf('COM_QAZAP_INSTL_PRECHECK_LOGFILE_CREATION_FAILED_MSG', $destination) ?></p>
				</div>					
				<?php
				$html = ob_get_contents();
				@ob_end_clean();
			}
			else
			{
				$link = rtrim(JURI::root(), '/') . '/administrator/index.php?option=com_qazap';

				ob_start();
				?>
				<div class="alert alert-block alert-info" style="margin-bottom: 40px;line-height: 2em;color: #333333;">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<p style="font-size:20px;margin-bottom: 20px;"><?php echo JText::_('COM_QAZAP_INSTL_COMPLETE_INSTALLATION_DESC') ?></p>
				  <input type="button" class="btn btn-large btn-success" onclick="window.location = '<?php echo $link; ?>'" value="<?php echo JText::_('COM_QAZAP_INSTL_COMPLETE_INSTALLATION') ?>"/>
				</div>				
				<?php
				$html = ob_get_contents();
				@ob_end_clean();
			}					
		} ?>
		
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('.alert-block').parents('table').width('100%');
				jQuery('.alert-block').parents('table').find('th').hide();
			}); 		
		</script>
		
		<?php
		// Print output html
		echo $html;	
	}
	
	/**
	* Method to check if installation table exists
	* 
	* @return boolean
	*/	
	protected function _InstlTableExists()
	{
		$app = JFactory::getApplication();
		$tableName = str_replace('#__', $app->getCfg('dbprefix'), '#__qazap_install'); 
		$db = JFactory::getDbo();
		$query = 'SHOW TABLES LIKE ' . $db->quote($tableName);
		$db->setQuery($query);
		
		try 
		{
			$result = $db->loadColumn();
		}
		catch(Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}		
		
		if(empty($result))
		{
			return false;
		}
		
		return true;
	}	
	
	protected function _createInstlTable()
	{
		$db = JFactory::getDbo();
		$query = 	'CREATE TABLE IF NOT EXISTS `#__qazap_install` ('.
							'`extension_type` varchar(255) NOT NULL,'.
  						'`names` text NOT NULL'.
							') ENGINE=MyISAM DEFAULT CHARSET=utf8;';
							
		$db->setQuery($query);
		
		if(!$db->execute())
		{
			JFactory::getApplication()->enqueueMessage($db->getErrorNum() . ':' . $db->getErrorMsg(), 'error');
			return false;			
		}
		
		return true;
	}
	
	protected function _uninstallModules()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
								->select('names')
								->from('#__qazap_install')
								->where('extension_type = ' . $db->quote('module'));
		$db->setQuery($query);

		try 
		{
			$modules = $db->loadResult();
		}
		catch(Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}		
		
		if(!empty($modules))
		{
			$modules = (array) json_decode($modules, true);
			
			if(!empty($modules))
			{
				$query->clear()
							->select('extension_id')
							->from('#__extensions')
							->where('element IN (' . implode(',', $db->quote($modules)) . ')')
							->where('type = ' . $db->quote('module'));				

				try 
				{
					$db->setQuery($query);
					$module_ids = $db->loadColumn();
				}
				catch(Exception $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');
					return false;
				}				

				if(!empty($module_ids))
				{	
					$installer = JInstaller::getInstance();
									
					foreach($module_ids as $id)
					{						
						if (!$installer->uninstall('module', $id))
						{
							return false;
						}							
					}				
				}				
			}						
		}
		
		return true;		
	}
	
	protected function _disableModules()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
								->select('names')
								->from('#__qazap_install')
								->where('extension_type = ' . $db->quote('module'));
		$db->setQuery($query);

		try 
		{
			$result = $db->loadResult();
		}
		catch(Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}		
		
		$modules = array();
		
		if(!empty($result))
		{
			$modules = (array) json_decode($result, true);
			
			if(!empty($modules))
			{
				$query->clear()
							->update('#__modules')
							->set('published = 0')
							->where('module IN (' . implode(',', $db->quote($modules)) . ')');
							
				$db->setQuery($query);
				
				if(!$db->execute())
				{
					$app->enqueueMessage($db->getErrorNum() . ':' . $db->getErrorMsg(), 'error');
					return false;
				}	
				
				$query->clear()
							->select('name')
							->from('#__extensions')
							->where('element IN (' . implode(',', $db->quote($modules)) . ')')
							->where('type = ' . $db->quote('module'));					
				
				try 
				{
					$db->setQuery($query);	
					$results = $db->loadColumn();
				}
				catch(Exception $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');
					return false;
				}					
				
				$modules = $results;								
			}			
		}
		
		return $modules;		
	}

	protected function _disablePlugins()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
								->select('names')
								->from('#__qazap_install')
								->where('extension_type = ' . $db->quote('plugin'));
								
		$db->setQuery($query);

		try 
		{
			$result = $db->loadResult();
		}
		catch(Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}		
		
		$plugins = array();
		
		if(!empty($result))
		{
			$plugins = (array) json_decode($result, true);
			
			if(!empty($plugins))
			{
				$where = array();
				
				foreach($plugins as $element => $folder)
				{
					$where[] = '(element = ' . $db->quote($element) . ' AND folder = ' . $db->quote($folder) . ')';
				}	
								
				$query->clear()
							->update('#__extensions')
							->set('enabled = 0')
							->where('(' . implode(' OR ', $where) . ')')
							->where('type = ' . $db->quote('plugin'));
							
				$db->setQuery($query);
				
				if(!$db->execute())
				{
					$app->enqueueMessage($db->getErrorNum() . ':' . $db->getErrorMsg(), 'error');
					return false;
				}

				$query->clear()
							->select('name')
							->from('#__extensions')
							->where('(' . implode(' OR ', $where) . ')')
							->where('type = ' . $db->quote('plugin'));
							
				$db->setQuery($query);	
							
				try 
				{
					$results = $db->loadColumn();
				}
				catch(Exception $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');
					return false;
				}					
				
				$plugins = $results;
			}			
		}
		
		return $plugins;		
	}
	
	protected function _uninstallPlugins()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
								->select('names')
								->from('#__qazap_install')
								->where('extension_type = ' . $db->quote('plugin'));
		$db->setQuery($query);

		try 
		{
			$plugins = $db->loadResult();
		}
		catch(Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}		
		
		if(!empty($plugins))
		{
			$plugins = (array) json_decode($plugins, true);
			
			if(!empty($plugins))
			{
				$where = array();
				foreach($plugins as $element => $folder)
				{
					$where[] = '(element = ' . $db->quote($element) . ' AND folder = ' . $db->quote($folder) . ')';
				}				
					
				$query->clear()
							->select('extension_id')
							->from('#__extensions')
							->where('(' . implode(' OR ', $where) . ')')
							->where('type = ' . $db->quote('plugin'));
							
				$db->setQuery($query);

				try 
				{
					$plugin_ids = $db->loadColumn();
				}
				catch(Exception $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');
					return false;
				}				
				
				if(!empty($plugin_ids))
				{
					$installer = JInstaller::getInstance();
					
					foreach($plugin_ids as $id)
					{						
						if (!$installer->uninstall('plugin', $id))
						{
							return false;
						}							
					}				
				}				
			}						
		}
		
		return true;		
	}	
	
}
