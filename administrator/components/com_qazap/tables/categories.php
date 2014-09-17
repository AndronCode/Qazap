<?php
/**
 * categories.php
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

/**
* productcategorie Table class
*/
class QazapTableCategories extends JTableNested 
{

	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_categories', 'category_id', $db);
		$this->access = (int) JFactory::getConfig()->get('access');
	}
	

	/**
	* Overloaded bind function to pre-process the params.
	*
	* @param	array		Named array
	* @return	null|string	null is operation was satisfactory, otherwise returns an error
	* @see		JTable:bind
	* @since	1.0.0
	*/
	public function bind($array, $ignore = '') 
	{
		$input = JFactory::getApplication()->input;
		$task = $input->getString('task', '');
		$user = JFactory::getUser();
		
		if(($task == 'save' || $task == 'apply') && (!JFactory::getUser()->authorise('core.edit.state','com_qazap') && $array['state'] == 1))
		{
			$array['state'] = 0;
		}

		//Support for multiple field: images
		if(isset($array['images']) && is_array($array['images']))
		{
			$array['images'] = json_encode($array['images']);
		}			

		if ($array['access'] == "")
		{
			$this->setError('COM_QAZAP_ACCESS_BLANK');
			return false;	
		}
		
		if (isset($array['params']) && is_array($array['params']))
		{
			$intgerFields = array('products_per_row', 'categories_per_row');
			
			foreach($array['params'] as $k=>$v)
			{
				if(in_array($k, $intgerFields) && !$v)
				{
					unset($array['params'][$k]);
				}
				elseif(trim($v) == '')
				{
					unset($array['params'][$k]);
				}
			}
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}
		
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}		


		if(!JFactory::getUser()->authorise('core.admin', 'com_qazap.category.'.$array['category_id']))
		{
			$actions = JFactory::getACL()->getActions('com_qazap','category');
	    	$default_actions = JFactory::getACL()->getAssetRules('com_qazap.category.'.$array['category_id'])->getData();
	    	$array_jaccess = array();
	    	foreach($actions as $action)
			{
				$array_jaccess[$action->name] = $default_actions[$action->name];
			}
	    	$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}
		
		return parent::bind($array, $ignore);
	}	

	/**
	* Method to compute the default name of the asset.
	* The default name is in the form table_name.id
	* where id is the value of the primary key of the table.
	*
	* @return  string
	*
	* @since   1.0.0
	*/
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_qazap.category.' . (int) $this->$k;
	}

	/**
	* Method to return the title to use for the asset table.
	*
	* @return  string
	*
	* @since  1.0.0
	*/
	protected function _getAssetTitle()
	{
		$lang = JFactory::getLanguage();
		$defaultLang = $lang->getDefault();
				
		$query = $this->_db->getQuery(true)
						->select($this->_db->quoteName('title'))
						->from($this->_db->quoteName('#__qazap_category_details'))
						->where($this->_db->quoteName('category_id') . ' = ' . $this->category_id)
						->where($this->_db->quoteName('language') . ' = ' . $this->_db->quote($defaultLang));

		// Get the asset id from the database.
		$this->_db->setQuery($query);

		if ($title = $this->_db->loadResult())
		{
			return $title;
		}		
		
		return $this->_getAssetName();
	}

	/**
	* Get the parent asset id for the record
	*
	* @param   JTable   $table  A JTable object for the asset parent.
	* @param   integer  $id     The id for the asset
	*
	* @return  integer  The id of the asset's parent
	*
	* @since  1.0.0
	*/
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$assetId = null;

		// This is a category under a category.
		if ($this->parent_id > 1)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__qazap_categories'))
				->where($this->_db->quoteName('category_id') . ' = ' . $this->parent_id);

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}
		// This is a category that needs to parent with the extension.
		elseif ($assetId === null)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('id'))
				->from($this->_db->quoteName('#__assets'))
				->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote('com_qazap'));

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}


	/**
	* Method to store a node in the database table.
	*
	* @param   boolean  $updateNulls  True to update null values as well.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTableNested/store
	* @since  1.0.0
	*/
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		if ($this->category_id)
		{
			// Existing item
			$this->modified_time		= $date->toSql();
			$this->modified_user_id	= $user->get('id');
		}
		else
		{
			// New contact. A contact created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}
			if (empty($this->created_user_id))
			{
				$this->created_user_id = $user->get('id');
			}
		}		
		
		return parent::store($updateNulls);
	}
		
	/**
	* Method to recursively rebuild the whole nested set tree.
	*
	* @param   integer  $parentId  The root of the tree to rebuild.
	* @param   integer  $leftId    The left id to start with in building the tree.
	* @param   integer  $level     The level to assign to the current nodes.
	* @param   string   $path      The path to the current nodes.
	*
	* @return  integer  1 + value of root rgt on success, false on failure
	*
	* @link    http://docs.joomla.org/JTableNested/rebuild
	* @since  1.0.0
	* @throws  RuntimeException on database error.
	*/
	public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
	{
		// In our case path is sent as array.
		$path = (array) $path;
		
		// If no parent is provided, try to find it.
		if ($parentId === null)
		{
			// Get the root item.
			$parentId = $this->getRootId();

			if ($parentId === false)
			{
				return false;
			}
		}

		$query = $this->_db->getQuery(true);

		// Build the structure of the recursive query.
		if (!isset($this->_cache['rebuild.sql']))
		{
			$query->clear()
				->select(array('a.'.$this->_tbl_key, 'd.alias', 'd.language'))
				->from($this->_tbl .' AS a')
				->join('LEFT', '#__qazap_category_details AS d ON d.category_id = a.category_id')
				->where('a.parent_id = %d');

			// If the table has an ordering field, use that for ordering.
			if (property_exists($this, 'ordering'))
			{
				$query->order('a.parent_id, a.ordering, a.lft');
			}
			else
			{
				$query->order('a.parent_id, a.lft');
			}
			$this->_cache['rebuild.sql'] = (string) $query;
		}

		// Make a shortcut to database object.

		// Assemble the query to find all children of this node.
		$this->_db->setQuery(sprintf($this->_cache['rebuild.sql'], (int) $parentId));

		$result = $this->_db->loadObjectList();
		
		$children = array();
		$checked = array();
		foreach($result as $key=>$child)
		{
			if(!isset($checked[$child->category_id]))
			{
				$checked[$child->category_id] = array();
			}
			$checked[$child->category_id][$child->language] = $child->alias;
		}
		unset($result);
		
		$i = 0;
		foreach($checked as $key=>$child)
		{
			$children[$i] = new stdClass;
			$children[$i]->category_id = $key;
			$children[$i]->alias = $child;
			$i++;
		}
		unset($checked);
		
		// The right value of this node is the left value + 1
		$rightId = $leftId + 1;

		// Execute this function recursively over all children
		foreach ($children as $node)
		{
			/*
			* $rightId is the current right value, which is incremented on recursion return.
			* Increment the level for the children.
			* Add this item's alias to the path (but avoid a leading /)
			*/
			$newPath = $this->buildPathRecursive($node->alias, $path);
			
			$rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1, $newPath);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false)
			{
				return false;
			}
		}
		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.

		$when = '';	
		if(!empty($path))
		{
			foreach($path as $language => $value) 
			{
				$when .= sprintf('WHEN %s THEN %s ', $this->_db->quote($language), $this->_db->quote($value));
			}			

			$query->clear()
					->update($this->_tbl .' AS a, #__qazap_category_details AS b')
					->set('a.lft = ' . (int) $leftId)
					->set('a.rgt = ' . (int) $rightId)
					->set('a.level = ' . (int) $level)
					->set('b.path = CASE b.'.$this->_db->quoteName('language').' '.$when.' END')			
					->where('a.'.$this->_tbl_key . ' = ' . (int) $parentId)
					->where('b.'.$this->_tbl_key . ' = ' . (int) $parentId);
			$this->_db->setQuery($query)->execute();
						
		}	
		else
		{
			$query->clear()
					->update($this->_tbl .' AS a')
					->set('a.lft = ' . (int) $leftId)
					->set('a.rgt = ' . (int) $rightId)
					->set('a.level = ' . (int) $level)	
					->where('a.'.$this->_tbl_key . ' = ' . (int) $parentId);
			$this->_db->setQuery($query)->execute();			
		}
		
		// Return the right value of this node + 1.
		return $rightId + 1;
	}		
	

	protected function buildPathRecursive($alias, $path)
	{
		$result = array();

		foreach($alias as $key => $value)
		{
			if(isset($path[$key]))
			{
				if(is_array($value))
				{
					$result[$key] = $this->buildPathRecursive($value, $path[$key]);
				}
				else
				{
					$result[$key] = $path[$key] . (empty($path[$key]) ? '' : '/') . $value;
				}
			}
		}

		return $result;
	}

	/**
	* Method to rebuild the node's path field from the alias values of the
	* nodes from the current node to the root node of the tree.
	*
	* @param   integer  $pk  Primary key of the node for which to get the path.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTableNested/rebuildPath
	* @since  1.0.0
	*/
	public function rebuildPath($pk = null)
	{
		$fields = $this->getFields();

		// If there is no alias or path field, just return true.
		if (!array_key_exists('alias', $fields) || !array_key_exists('path', $fields))
		{
			return true;
		}

		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the aliases for the path from the node to the root node.
		$query = $this->_db->getQuery(true)
					->select('p.alias')
					->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p')
					->where('n.lft BETWEEN p.lft AND p.rgt')
					->where('n.' . $this->_tbl_key . ' = ' . (int) $pk)
					->order('p.lft');
		$this->_db->setQuery($query);

		$segments = $this->_db->loadColumn();

		// Make sure to remove the root path if it exists in the list.
		if ($segments[0] == 'root')
		{
			array_shift($segments);
		}

		// Build the path.
		$path = trim(implode('/', $segments), ' /\\');

		// Update the path field for the node.
		$query->clear()
			->update($this->_tbl)
			->set('path = ' . $this->_db->quote($path))
			->where($this->_tbl_key . ' = ' . (int) $pk);

		$this->_db->setQuery($query)->execute();

		// Update the current record's path to the new one:
		$this->path = $path;

		return true;
	}
}
