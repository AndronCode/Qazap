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

defined('JPATH_PLATFORM') or die;

/**
 * QZCategories Class.
 *
 * @package     Qazap.Admin
 * @subpackage  Helpers
 * @since       1.0
 */
class QZCategories
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  1.0
	 */
	public static $instances = array();

	/**
	 * Array of category nodes
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	protected $_nodes;

	/**
	 * Array of checked categories -- used to save values when _nodes are null
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $_checkedCategories;

	/**
	 * Name of the extension the categories belong to
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_extension = null;

	/**
	 * Name of the linked content table to get category content count
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_table = null;

	/**
	 * Name of the category field
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_field = null;

	/**
	 * Name of the key field
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_key = null;

	/**
	 * Name of the items state field
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $_statefield = null;

	/**
	 * Array of options
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $_options = null;
	
	protected $_vendorcats = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.0
	 */
	public function __construct($options)
	{
		$this->_table = '#__qazap_products';
		$this->_field = (isset($options['field']) && $options['field']) ? $options['field'] : 'category_id';
		$this->_key = (isset($options['key']) && $options['key']) ? $options['key'] : 'product_id';
		$this->_statefield = (isset($options['statefield'])) ? $options['statefield'] : 'state';
		$options['access'] = (isset($options['access'])) ? $options['access'] : 'true';
		$options['published'] = (isset($options['published'])) ? $options['published'] : 1;
		$options['countItems'] = (isset($options['countItems'])) ? $options['countItems'] : false;
		$options['countSubcat'] = (isset($options['countSubcat'])) ? $options['countSubcat'] : true;
		$options['vendors'] = (isset($options['vendors'])) ? $options['vendors'] : null;
		$this->_options = $options;

		return true;
	}

	/**
	 * Returns a reference to a QZCategories object
	 *
	 * @param   array   $options    An array of options
	 *
	 * @return  QZCategories         QZCategories object
	 *
	 * @since   1.0
	 */
	public static function getInstance($options = array())
	{
		$hash = md5(serialize($options));

		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}

		self::$instances[$hash] = new self($options);
		
		return self::$instances[$hash];
	}

	/**
	 * Loads a specific category and all its children in a QZCategoryNode object
	 *
	 * @param   mixed    $category_id					an optional id integer or equal to 'root'
	 * @param   boolean  $forceload  					True to force  the _load method to execute
	 *
	 * @return  mixed    QZCategoryNode object or null if $category_id is not valid
	 *
	 * @since   1.0
	 */
	public function get($category_id = 'root', $forceload = false)
	{
		if ($category_id !== 'root')
		{
			$category_id = (int) $category_id;

			if ($category_id == 0)
			{
				$category_id = 'root';
			}
		}

		
		// If this $category_id has not been processed yet, execute the _load method
		if ((!isset($this->_nodes[$category_id]) && !isset($this->_checkedCategories[$category_id])) || $forceload)
		{
			$this->_load($category_id);
		}
		
		// If we already have a value in _nodes for this $category_id, then use it.
		if (isset($this->_nodes[$category_id]))
		{
			return $this->_nodes[$category_id];
		}
		// If we processed this $category_id already and it was not valid, then return null.
		elseif (isset($this->_checkedCategories[$category_id]))
		{
			return null;
		}

		return false;
	}

	/**
	 * Load method
	 *
	 * @param   integer  $category_id  Id of category to load
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _load($category_id)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$multiple_language = JLanguageMultilang::isEnabled();
		$present_language = $lang->getTag();
		$default_language = $lang->getDefault();
		$config = QZApp::getConfig();			

		// Record that has this $category_id has been checked
		$this->_checkedCategories[$category_id] = true;

		$query = $db->getQuery(true);

		// Select c for categories table and left join d for category_details page.
		$query->select('c.category_id, c.asset_id, d.category_details_id, c.access, d.alias, c.checked_out, 
			c.checked_out_time, c.created_time, c.created_user_id, d.description, c.images, c.hits, d.language, 
			c.level, c.lft, d.metadata, d.metadesc, d.metakey, c.modified_time, c.note, c.params, c.parent_id,
			d.path, c.published, c.rgt, d.title, c.modified_user_id, c.version');
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('d.alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('d.category_id');
		$case_when .= $query->concatenate(array($c_id, 'd.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when);
		
		$query->from('#__qazap_categories as c');
		
		if($multiple_language)
		{
			$query->join('LEFT', '#__qazap_category_details AS d ON d.category_id = c.category_id AND d.language = '.$db->quote($present_language));				
		}
		else
		{
			$query->join('LEFT', '#__qazap_category_details AS d ON d.category_id = c.category_id AND d.language = '.$db->quote($default_language));
		}


		if ($this->_options['access'])
		{
			$query->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}

		if ($this->_options['published'] == 1)
		{
			$query->where('c.published = 1');
		}

		$query->order('c.lft');

		// Note: s for selected id
		if ($category_id != 'root')
		{
			// Get the selected category
			$query->join('LEFT', '#__qazap_categories AS s ON (s.lft <= c.lft AND s.rgt >= c.rgt) OR (s.lft > c.lft AND s.rgt < c.rgt)')
				->where('s.category_id=' . (int) $category_id);
		}

		$subQuery = ' (SELECT cat.category_id as category_id FROM #__qazap_categories AS cat JOIN #__qazap_categories AS parent ' .
			'ON cat.lft BETWEEN parent.lft AND parent.rgt WHERE parent.published != 1 GROUP BY cat.category_id) ';
		$query->join('LEFT', $subQuery . 'AS badcats ON badcats.category_id = c.category_id')
			->where('badcats.category_id is null');
			
		$vendor_cats = $this->getVendorCategories();
		
		if(is_array($vendor_cats) && !empty($vendor_cats))
		{
			$subQuery = '(SELECT vparent.category_id FROM #__qazap_categories AS vparent '.
										'JOIN #__qazap_categories AS vcat ON vcat.lft BETWEEN vparent.lft AND vparent.rgt '.
										'WHERE vparent.published = 1 AND vcat.category_id IN ('. implode(',', $vendor_cats) .') '.
										'GROUP BY vparent.category_id) ';
			$query->where('c.category_id IN ' . $subQuery);													
		}

		// Note: i for item
		if ($this->_options['countItems'])
		{
			if($config->get('show_inactive_vendor_products', 0))
			{
				$activeVendorSubquery = '';
			}
			else
			{
				$activeVendorSubquery = 'AND i.vendor IN (SELECT av.id FROM #__qazap_vendor AS av WHERE av.state = 1 GROUP BY av.id) ';	
			}
			
			$vendors = (array) $this->_options['vendors'];
			
			if(empty($vendors))
			{
				$input = JFactory::getApplication()->input;
				$vendors = $input->get('vendor_id', array(), 'array');
			}
			$vendors = array_filter(array_unique($vendors));
			
			if(!empty($vendors))
			{
				$vendors = array_map('intval', $vendors);
				if(count($vendors) == 1)
				{
					$vendorQuery = ' AND i.vendor = ' . $vendors[0];
				}
				else
				{
					$vendorQuery = ' AND i.vendor IN (' . implode(',', $vendors) . ')';
				}				
			}
			else
			{
				$vendorQuery = '';
			}
	
			if ($this->_options['published'] == 1)
			{			
				if($this->_options['countSubcat'])
				{
					$countSubquery = ' (SELECT subcat.category_id as category_id FROM #__qazap_categories AS subcat WHERE subcat.lft BETWEEN c.lft AND c.rgt AND subcat.published = 1 GROUP BY subcat.category_id) ';
					$query->join(
						'LEFT',
						$db->quoteName($this->_table) . ' AS i ON i.' . $db->quoteName($this->_field) . ' IN '.$countSubquery.' AND i.' . $this->_statefield . ' = 1 AND i.parent_id = 0 AND i.block = 0 ' . $activeVendorSubquery . $vendorQuery
					);
				}
				else
				{
					$query->join(
						'LEFT',
						$db->quoteName($this->_table) . ' AS i ON i.' . $db->quoteName($this->_field) . ' = c.category_id AND i.' . $this->_statefield . ' = 1  AND i.parent_id = 0 AND i.block = 0 ' . $activeVendorSubquery . $vendorQuery
					);					
				}

			}
			else
			{
				if($this->_options['countSubcat'])
				{
					$countSubquery = ' (SELECT subcat.category_id as category_id FROM #__qazap_categories AS subcat WHERE subcat.lft BETWEEN c.lft AND c.rgt GROUP BY subcat.category_id) ';
					$query->join('LEFT', $db->quoteName($this->_table) . ' AS i ON i.' . $db->quoteName($this->_field) . ' IN '.$countSubquery .' AND i.parent_id = 0 AND i.block = 0 ' . $activeVendorSubquery . $vendorQuery);
				}
				else
				{
					$query->join('LEFT', $db->quoteName($this->_table) . ' AS i ON i.' . $db->quoteName($this->_field) . ' = c.category_id  AND i.parent_id = 0 AND i.block = 0 ' . $activeVendorSubquery . $vendorQuery);
				}

			}

			$query->select('COUNT(i.' . $db->quoteName($this->_key) . ') AS numitems');
		}
		
		// Group by
		$query->group('c.category_id, c.asset_id, c.access, c.checked_out, c.checked_out_time, c.created_time, '.
									'c.created_user_id, c.images, c.hits, c.level, c.lft, c.modified_time, c.note, c.params, '.
									'c.parent_id, c.published, c.rgt, c.modified_user_id, c.version');

		// Get the results
		$db->setQuery($query);
		$results = $db->loadObjectList('category_id');
		$childrenLoaded = false;		
		
		if (count($results))
		{
			// Foreach categories
			foreach ($results as $result)
			{
				// Deal with root category
				if ($result->category_id == 1)
				{
					$result->category_id = 'root';
				}

				// Deal with parent_id
				if ($result->parent_id == 1)
				{
					$result->parent_id = 'root';
				}
				
				// Create the node
				if (!isset($this->_nodes[$result->category_id]))
				{
					// Create the QZCategoryNode and add to _nodes
					$this->_nodes[$result->category_id] = new QZCategoryNode($result, $this);

					// If this is not root and if the current node's parent is in the list or the current node parent is 0
					if ($result->category_id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id == 1))
					{
						// Compute relationship between node and its parent - set the parent in the _nodes field
						$this->_nodes[$result->category_id]->setParent($this->_nodes[$result->parent_id]);
					}

					// If the node's parent category_id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
					// then remove the node from the list
					if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						unset($this->_nodes[$result->category_id]);
						continue;
					}

					if ($result->category_id == $category_id || $childrenLoaded)
					{
						$this->_nodes[$result->category_id]->setAllLoaded();
						$childrenLoaded = true;
					}
				}
				elseif ($result->category_id == $category_id || $childrenLoaded)
				{
					// Create the QZCategoryNode
					$this->_nodes[$result->category_id] = new QZCategoryNode($result, $this);

					if ($result->category_id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id))
					{
						// Compute relationship between node and its parent
						$this->_nodes[$result->category_id]->setParent($this->_nodes[$result->parent_id]);
					}

					if (!isset($this->_nodes[$result->parent_id]))
					{
						unset($this->_nodes[$result->category_id]);
						continue;
					}

					if ($result->category_id == $category_id || $childrenLoaded)
					{
						$this->_nodes[$result->category_id]->setAllLoaded();
						$childrenLoaded = true;
					}
				}
			}
		}
		else
		{
			$this->_nodes[$category_id] = null;
		}
	}
	
	public function getVendorCategories()
	{
		// Filter by selected vendors
		$vendors = (array) $this->_options['vendors'];
		
		if(empty($vendors))
		{
			$input = JFactory::getApplication()->input;
			$vendors = $input->get('vendor_id', array(), 'array');
		}
		
		$return = array();
		
		if(!empty($vendors))
		{
			foreach($vendors as $key => $vendor)
			{
				if(isset($this->_vendorcats[$vendor]))
				{
					$return += (array) $this->_vendorcats[$vendor];
					unset($vendors[$key]);
				}
			}
			
			$newCount = count($vendors);
			
			if($newCount > 0)
			{
				$vendors = array_map('intval', array_values($vendors));
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
								->select('id, category_list')
								->from('#__qazap_vendor');
								
				if($newCount == 1)
				{
					$query->where('id = ' . (int) $vendors[0]);
				}
				else
				{
					$query->where('id IN (' . implode(',', $vendors) . ')');
				}
				
				$db->setQuery($query);
				$results = $db->loadObjectList();

				if(!empty($results))
				{
					foreach($results as $result)
					{
						if(!empty($result->category_list) && is_string($result->category_list))
						{
							$this->_vendorcats[$result->id] = json_decode($result->category_list, true);
						}
						else
						{
							$this->_vendorcats[$result->id] = array();
						}
						
						$return += (array) $this->_vendorcats[$result->id];
					}
				}
			}
			
			if(in_array('0', $return))
			{
				return null;
			}
			
			return array_unique($return);							
		}	
		
		return null;
	}
}

/**
 * Helper class to load QZCategorytree
 *
 * @package     Qazap.Admin
 * @subpackage  Helpers
 * @since       1.0
 */
class QZCategoryNode extends JObject
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $category_id = null;

	/**
	 * The id of the category in the asset table
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $asset_id = null;

	/**
	 * The id of the parent of category in the asset table, 0 for category root
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $parent_id = null;

	/**
	 * The lft value for this category in the category tree
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $lft = null;

	/**
	 * The rgt value for this category in the category tree
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $rgt = null;

	/**
	 * The depth of this category's position in the category tree
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $level = null;

	/**
	 * The menu title for the category (a short name)
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $title = null;

	/**
	 * The the alias for the category
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $alias = null;

	/**
	 * Description of the category.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $description = null;
	
	/**
	 * Images of the category.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $images = null;	

	/**
	 * The publication status of the category
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $published = null;

	/**
	 * Whether the category is or is not checked out
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $checked_out = 0;

	/**
	 * The time at which the category was checked out
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $checked_out_time = 0;

	/**
	 * Access level for the category
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $access = null;

	/**
	 * JSON string of parameters
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $params = null;

	/**
	 * Metadata description
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $metadesc = null;

	/**
	 * Key words for meta data
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $metakey = null;

	/**
	 * JSON string of other meta data
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $metadata = null;

	/**
	 * The ID of the user who created the category
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $created_user_id = null;

	/**
	 * The time at which the category was created
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $created_time = null;

	/**
	 * The ID of the user who last modified the category
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $modified_user_id = null;

	/**
	 * The time at which the category was modified
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $modified_time = null;

	/**
	 * Nmber of times the category has been viewed
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $hits = null;

	/**
	 * The language for the category in xx-XX format
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $language = null;

	/**
	 * Number of items in this category or descendants of this category
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $numitems = null;

	/**
	 * Number of children items
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $childrennumitems = null;

	/**
	 * Slug fo the category (used in URL)
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $slug = null;

	/**
	 * Array of  assets
	 *
	 * @var    array
	 * @since  1.0
	 */
	public $assets = null;

	/**
	 * Parent Category object
	 *
	 * @var    object
	 * @since  1.0
	 */
	protected $_parent = null;

	/**
	 * @var Array of Children
	 * @since  1.0
	 */
	protected $_children = array();

	/**
	 * Path from root to this category
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $_path = array();

	/**
	 * Category left of this one
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $_leftSibling = null;

	/**
	 * Category right of this one
	 *
	 * @var
	 * @since  1.0
	 */
	protected $_rightSibling = null;

	/**
	 * true if all children have been loaded
	 *
	 * @var boolean
	 * @since  1.0
	 */
	protected $_allChildrenloaded = false;

	/**
	 * Constructor of this tree
	 *
	 * @var
	 * @since  1.0
	 */
	protected $_constructor = null;

	/**
	 * Class constructor
	 *
	 * @param   array          $category     The category data.
	 * @param   QZCategoryNode  $constructor  The tree constructor.
	 *
	 * @since   1.0
	 */
	public function __construct($category = null, $constructor = null)
	{
		if ($category)
		{
			$this->setProperties($category);
			if ($constructor)
			{
				$this->_constructor = $constructor;
			}

			return true;
		}

		return false;
	}

	/**
	 * Set the parent of this category
	 *
	 * If the category already has a parent, the link is unset
	 *
	 * @param   mixed  $parent  QZCategoryNode for the parent to be set or null
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setParent($parent)
	{
		if ($parent instanceof QZCategoryNode || is_null($parent))
		{
			if (!is_null($this->_parent))
			{
				$key = array_search($this, $this->_parent->_children);
				unset($this->_parent->_children[$key]);
			}

			if (!is_null($parent))
			{
				$parent->_children[] = & $this;
			}

			$this->_parent = $parent;

			if ($this->category_id != 'root')
			{
				if ($this->parent_id != 1)
				{
					$this->_path = $parent->getPath();
				}
				$this->_path[] = $this->category_id . ':' . $this->alias;
			}

			if (count($parent->_children) > 1)
			{
				end($parent->_children);
				$this->_leftSibling = prev($parent->_children);
				$this->_leftSibling->_rightsibling = & $this;
			}
		}
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   QZCategoryNode  $child  The child to be added.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addChild($child)
	{
		if ($child instanceof QZCategoryNode)
		{
			$child->setParent($this);
		}
	}

	/**
	 * Remove a specific child
	 *
	 * @param   integer  $category_id  ID of a category
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function removeChild($category_id)
	{
		$key = array_search($this, $this->_parent->_children);
		unset($this->_parent->_children[$key]);
	}

	/**
	 * Get the children of this node
	 *
	 * @param   boolean  $recursive  False by default
	 *
	 * @return  array  The children
	 *
	 * @since   1.0
	 */
	public function &getChildren($recursive = false)
	{
		if (!$this->_allChildrenloaded)
		{
			$temp = $this->_constructor->get($this->category_id, true);
			if ($temp)
			{
				$this->_children = $temp->getChildren();
				$this->_leftSibling = $temp->getSibling(false);
				$this->_rightSibling = $temp->getSibling(true);
				$this->setAllLoaded();
			}
		}

		if ($recursive)
		{
			$items = array();
			foreach ($this->_children as $child)
			{
				$items[] = $child;
				$items = array_merge($items, $child->getChildren(true));
			}
			return $items;
		}

		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed  QZCategoryNode or null
	 *
	 * @since   1.0
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there is a child
	 *
	 * @since   1.0
	 */
	public function hasChildren()
	{
		return count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean    True if there is a parent
	 *
	 * @since   1.0
	 */
	public function hasParent()
	{
		return $this->getParent() != null;
	}

	/**
	 * Function to set the left or right sibling of a category
	 *
	 * @param   QZCategoryNode  $sibling  QZCategoryNode object for the sibling
	 * @param   boolean        $right    If set to false, the sibling is the left one
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setSibling($sibling, $right = true)
	{
		if ($right)
		{
			$this->_rightSibling = $sibling;
		}
		else
		{
			$this->_leftSibling = $sibling;
		}
	}

	/**
	 * Returns the right or left sibling of a category
	 *
	 * @param   boolean  $right  If set to false, returns the left sibling
	 *
	 * @return  mixed  QZCategoryNode object with the sibling information or
	 *                 NULL if there is no sibling on that side.
	 *
	 * @since          1.0
	 */
	public function getSibling($right = true)
	{
		if (!$this->_allChildrenloaded)
		{
			$temp = $this->_constructor->get($this->category_id, true);
			$this->_children = $temp->getChildren();
			$this->_leftSibling = $temp->getSibling(false);
			$this->_rightSibling = $temp->getSibling(true);
			$this->setAllLoaded();
		}

		if ($right)
		{
			return $this->_rightSibling;
		}
		else
		{
			return $this->_leftSibling;
		}
	}

	/**
	 * Returns the category parameters
	 *
	 * @return  JRegistry
	 *
	 * @since   1.0
	 */
	public function getParams()
	{
		if (!($this->params instanceof JRegistry))
		{
			$temp = new JRegistry;
			$temp->loadString($this->params);
			$this->params = $temp;
		}

		return $this->params;
	}
	
	/**
	 * Returns the category images
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getImages()
	{
		if (!empty($this->images))
		{
			if(is_string($this->images))
			{
				$this->images = json_decode($this->images);
			}			
		}
		else
		{
			$this->images = false;
		}

		return $this->images;
	}	
	

	/**
	 * Returns the category metadata
	 *
	 * @return  JRegistry  A JRegistry object containing the metadata
	 *
	 * @since   1.0
	 */
	public function getMetadata()
	{
		if (!($this->metadata instanceof JRegistry))
		{
			$temp = new JRegistry;
			$temp->loadString($this->metadata);
			$this->metadata = $temp;
		}

		return $this->metadata;
	}

	/**
	 * Returns the category path to the root category
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Returns the user that created the category
	 *
	 * @param   boolean  $modified_user  Returns the modified_user when set to true
	 *
	 * @return  JUser  A JUser object containing a userid
	 *
	 * @since   1.0
	 */
	public function getAuthor($modified_user = false)
	{
		if ($modified_user)
		{
			return JFactory::getUser($this->modified_user_id);
		}

		return JFactory::getUser($this->created_user_id);
	}

	/**
	 * Set to load all children
	 *
	 * @return  void
	 *
	 * @since 1.0
	 */
	public function setAllLoaded()
	{
		$this->_allChildrenloaded = true;
		foreach ($this->_children as $child)
		{
			$child->setAllLoaded();
		}
	}

	/**
	 * Returns the number of items.
	 *
	 * @param   boolean  $recursive  If false number of children, if true number of descendants
	 *
	 * @return  integer  Number of children or descendants
	 *
	 * @since 1.0
	 */
	public function getNumItems($recursive = false)
	{
		if ($recursive)
		{
			$count = $this->numitems;

			foreach ($this->getChildren() as $child)
			{
				$count = $count + $child->getNumItems(true);
			}

			return $count;
		}

		return $this->numitems;
	}
}
