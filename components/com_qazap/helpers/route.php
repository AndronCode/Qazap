<?php
/**
 * @package      Qazap
 * @subpackage	 Site
 *
 * @author			Qazap Team
 * @link        http://www.qazap.com
 * @copyright		Copyright (C) 2014 VirtuePlanet Services LLP. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 * @since			  1.0.0
 */


defined('_JEXEC') or die;

/**
 * Qazap Component Route Helper
 *
 * @static
 * @since       1.0
 * @author			Abhishek Das
 */
abstract class QazapHelperRoute
{
	protected static $lookup = array();
	protected static $vendor_lookup = array();
	protected static $lang_lookup = array();
	protected static $vendor_alias = array();
	protected static $vendor_id = array();
	protected static $product_alias = array();
	protected static $attr_names = array();
	protected static $brand_alias = array();
	protected static $orderdetails_alias = array();
	protected static $product_category = array();
	protected static $_router = null;


	public static function mail($url)
	{
		if (!self::$_router)
		{
			// Get the router.
			$app    = JApplication::getInstance('site');
			self::$_router = $app->getRouter();

			// Make sure that we have our router
			if (!self::$_router)
			{
				return null;
			}
		}

		if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		$url = self::$_router->build($url);
		$url = $url->toString();
		$uri = JUri::getInstance();
		$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$url = $base . $url;

		$url = str_replace(JUri::root() . 'administrator/', JUri::root(), $url);
		
		return $url;
	}
	/**
	 * @param   integer  The route of the content item
	 */
	public static function getProductRoute($id, $catid = 0, $language = 0)
	{
		
		$needles = array(
			'product'  => array((int) $id)
		);
		//Create the link
		$link = 'index.php?option=com_qazap&view=product&product_id=' . (int) $id;
		
		if(empty($catid))
		{
			$catid = self::_findProductCategory($id);
		}
		
		if ((int) $catid > 1)
		{
			$categories = QZCategories::getInstance();
			$category = $categories->get((int) $catid); 
			
			if($category)
			{
				$path = array_reverse($category->getPath());
				$needles['category'] = array_map('intval', $path);				
				$needles['categories'] = $needles['category'];
				$needles['categories'][] = 0;
				$link .= '&category_id='.$catid;
			}
		}
		
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function getCategoryRoute($catid, $order = 0, $dir = 0, $start = '', $limit = '', $filters = array(), $language = 0)
	{
		if ($catid instanceof QZCategoryNode)
		{
			$category_id = (int) $catid->category_id;
			$category = $catid;
		}
		else
		{
			$category_id = (int) $catid;
			$categories = QZCategories::getInstance();
			$category = $categories->get($category_id);			
		}

		if ($category_id != 0 && ($category_id < 1 || !($category instanceof QZCategoryNode)))
		{
			$link = '';
		}
		else
		{
			$needles = array();

			if($category_id == 0)
			{
				$link = 'index.php?option=com_qazap&view=category&category_id=0';
			}
			else
			{
				$link = 'index.php?option=com_qazap&view=category&category_id='.$category_id;
			}			
			
			if($order)
			{
				$link .= '&orderby='.$order;
			}
			
			if($dir)
			{
				$link .= '&order_dir='.$dir;
			}
			
			if($start != '')
			{
				$link .= '&limitstart='.$start;
			}	
			
			if($limit != '')
			{
				$link .= '&listlimit='.$limit;
			}
			
			if(count($filters))
			{
				foreach($filters as $name=>$filter)
				{
					if(is_array($filter))
					{
						foreach($filter as $k=>$v)
						{
							if($name == 'vendor_id')
							{
								if(count($filter) == 1)
								{
									$link .= '&vendor_id='.$v;
								}
								else
								{
									$link .= '&vendor_id['.(int) $k.']='. (int) $v;
								}
							}
							elseif($name == 'attribute')
							{
								$link .= '&attribute['.(int) $k.']='.urlencode($v);
							}
							else
							{
								$link .= '&'.$name.'[]='.$v;
							}
						}
					}
					elseif($filter)
					{
						$link .= '&'.$name.'='.$filter;
					}	
				}
			}				
			
			if(!$category)
			{
				$needles['categories'] = array(0);
			}
			else
			{
				$catids = array_reverse($category->getPath());
				$needles['category'] = array_merge($catids, array(0));
				$needles['categories'] = array_merge($catids, array(0));				
			}

			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();

				if(isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];
					$needles['language'] = $language;
				}
			}

			if($vendor_id = self::_findVendor($needles))
			{
				$link .= '&vendor_id=' . $vendor_id;
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}
	
	public static function getCartRoute($options = array(), $language = 0)
	{
		$options = (array) $options;
		$layout = isset($options['layout']) ? $options['layout'] : null;
		$task = isset($options['task']) ? $options['task'] : null;
		$id = isset($options['id']) ? $options['id'] : null;
		
		$needles = array();

		$link = 'index.php?option=com_qazap&view=cart';
		
		if(!empty($layout))
		{
			$link .= '&layout=' . $layout;
		}
		
		if(!empty($id))
		{
			$link .= '&id=' . $id;
		}
		
		if(!empty($task))
		{
			$link .= '&task=' . $task;
		}		

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if(isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
		
		$needles['cart'] = array('');
		$idName = null;	
		
		if ($item = self::_findItem($needles, $idName))
		{
			$link .= '&Itemid=' . $item;
		}	
		
		return $link;	
	}
	
	public static function getBrandRoute($brandID, $productsPage = false, $language = 0)
	{
		if ($brandID instanceof stdClass)
		{
			$brand_id = (int) $brandID->id;
			$brand = $brandID;
		}
		else
		{
			$brand_id = (int) $brandID;
			JTable::addIncludePath(QZPATH_TABLE_ADMIN);
			$brand = JTable::getInstance('Manufacturer', 'QazapTable', array());
			$brand->load($brand_id);
		}

		if ($brand_id < 1 || !isset($brand->id) || $brand->id < 1)
		{
			$link = '';
		}
		else
		{
			$needles = array();
			$config = QZApp::getConfig();
			
			if($config->get('brands_link_page', 1) == 1 && !$productsPage)
			{
				$link = 'index.php?option=com_qazap&view=brand&brand_id=' . (int) $brand_id;
				$needles['brands'] = array($brand->manufacturer_category, 0);
				$type = 'brand_category_id';
			}
			else
			{
				$link = 'index.php?option=com_qazap&view=category&brand_id=' . (int) $brand_id;				
				$needles['category'] = array(0);
				$needles['categories'] = array(0);
				$type = 'category_id';
			}

			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();

				if(isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];
					$needles['language'] = $language;
				}
			}

			if ($item = self::_findItem($needles, $type))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}
	
	public static function getVendorRoute($vendorID, $productsPage = false, $language = 0)
	{
		if ($vendorID instanceof stdClass)
		{
			$vendor_id = (int) $vendorID->id;
			$vendor = $vendorID;
		}
		else
		{
			$vendor_id = (int) $vendorID;
			JTable::addIncludePath(QZPATH_TABLE_ADMIN);
			$vendor = JTable::getInstance('Vendor', 'QazapTable', array());
			$vendor->load($vendor_id);
		}

		if ($vendor_id < 1 || !isset($vendor->id) || $vendor->id < 1)
		{
			$link = '';
		}
		else
		{
			$needles = array();
			$config = QZApp::getConfig();
			
			if($config->get('shop_link_page', 1) == 1 && !$productsPage)
			{
				$link = 'index.php?option=com_qazap&view=shop&vendor_id=' . (int) $vendor_id;
				$needles['shops'] = array(0);
				$needles['categories'] = array(0);
				$type = null;
			}
			else
			{
				$link = 'index.php?option=com_qazap&view=category&vendor_id=' . (int) $vendor_id;				
				$needles['category'] = array(0);
				$needles['categories'] = array(0);
				$type = 'category_id';
			}

			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();

				if(isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];
					$needles['language'] = $language;
				}
			}

			if ($item = self::_findItem($needles, $type))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;		
	}
	
	public static function getFormRoute($product_id)
	{
		//Create the link
		if ($product_id)
		{
			$link = 'index.php?option=com_qazap&task=product.edit&product_id=' . $product_id;
		}
		else
		{
			$link = 'index.php?option=com_qazap&task=product.edit&product_id=0';
		}

		return $link;
	}
	
	public static function getProfileRoute($layout = '', $language = 0)
	{	
		$needles = array();
		
		if(!empty($layout))
		{
			$needles['profile'] = array((string) $layout, '');
			$idName = 'layout';				
		}
		else
		{
			$needles['profile'] = array('');
			$idName = null;			
		}
		
		$needles['seller'] = array('');
		//Create the link
		$link = 'index.php?option=com_qazap&view=profile';
				
		if(!empty($layout))
		{
			$link .= '&layout=' . $layout;
		}
		
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}

		if ($item = self::_findItem($needles, $idName))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}	
	
	public static function getSellerRoute($layout = '', $ordergroup_id = 0, $language = 0)
	{		
		$needles = array();
		
		if(!empty($layout))
		{
			$needles['seller'] = array((string) $layout, '');
			$idName = 'layout';				
		}
		else
		{
			$needles['seller'] = array('');
			$idName = null;			
		}
		
		$needles['profile'] = array('');
		
		//Create the link
		$link = 'index.php?option=com_qazap&view=seller';
				
		if(!empty($layout))
		{
			$link .= '&layout=' . $layout;
		}
		
		if(!empty($ordergroup_id))
		{
			$link .= '&ordergroup_id=' . $ordergroup_id;
		}
		
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}

		if ($item = self::_findItem($needles, $idName))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}
	
	public static function getOrderdetailsRoute($ordergroup_id, $language = null)
	{
		$user = JFactory::getUser();
		$needles = array();
		$idName = null;	
		
		if($user->guest)
		{
			$needles['cart'] = array('');
		}
		else
		{
			$needles['profile'] = array('');
		}
		
		$link = 'index.php?option=com_qazap&view=orderdetails&ordergroup_id=' . (int) $ordergroup_id;
		
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}

		if ($item = self::_findItem($needles, $idName))
		{
			$link .= '&Itemid=' . $item;
		}	
		
		return $link;	
	}

	public static function getDownloadRoute($download_id = null, $passcode = null, $language = null)
	{		
		$needles = array();
		
		$needles['download'] = array('');
		$idName = null;		
		
		//Create the link
		$link = 'index.php?option=com_qazap&view=download';
				
		if(!empty($download_id))
		{
			$link .= '&download_id=' . (int) $download_id;
		}
		
		if(!empty($passcode))
		{
			$link .= '&passcode=' . $passcode;
		}
		
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}

		if ($item = self::_findItem($needles, $idName))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}	
	
	public static function getCompareRoute($product_ids = array(), $language = 0)
	{
		$product_ids = (array) $product_ids;	
		$needles = array();

		$link = 'index.php?option=com_qazap&view=compare';
				

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if(isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
		
		$needles['compare'] = array('');
		$idName = null;
		
		if(!empty($product_ids))
		{
			foreach($product_ids as $product_id)
			{
				$link .= '&p_id[]='. (int) $product_id;
			}
		}	
		
		if ($item = self::_findItem($needles, $idName))
		{
			$link .= '&Itemid=' . $item;
		}
				
		return $link;	
	}	
		

	protected static function buildLanguageLookup()
	{
		if (count(self::$lang_lookup) == 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');

			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}
	
	protected static function _findProductCategory($product_id)
	{
		if (!isset(self::$product_category[$product_id]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.category_id AS category_id')
				->from('#__qazap_products AS a')
				->where('a.product_id = ' . (int) $product_id);
				
			$db->setQuery($query);
			$category_id = $db->loadResult();
			
			self::$product_category[$product_id] = $category_id;
		}	
		
		return self::$product_category[$product_id];	
	}

	protected static function _findItem($needles = array(), $idName = 'category_id')
	{
		$app			= JFactory::getApplication('site');
		$menus		= $app->getMenu('site');
		$language	= isset($needles['language']) ? $needles['language'] : '*';
		$type 		= $language . $idName;
		 
		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$type]))
		{
			self::$lookup[$type] = array();

			$component	= JComponentHelper::getComponent('com_qazap');

			$attributes = array('component_id');
			$values = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[] = array($needles['language'], '*');
			}

			$items		= $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$type][$view]))
					{
						self::$lookup[$type][$view] = array();
					}
					if (!empty($idName) && isset($item->query[$idName])) 
					{
						// here it will become a bit tricky
						// language != * can override existing entries
						// language == * cannot override existing entries
						if (!isset(self::$lookup[$type][$view][$item->query[$idName]]) || $item->language != '*')
						{
							self::$lookup[$type][$view][$item->query[$idName]] = $item->id;
						}
					}
					else
					{
						if (!isset(self::$lookup[$type][$view][0]) || $item->language != '*')
						{
							self::$lookup[$type][$view][0] = $item->id;
						}						
					}
				}
			}
		}

		if (!empty($needles))
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$type][$view]))
				{
					foreach ($ids as $id)
					{	
						$id = empty($id) ? 0 : (string) $id;
						
						if (isset(self::$lookup[$type][$view][$id]))
						{
							return self::$lookup[$type][$view][$id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();
		if ($active && $active->component == 'com_qazap' && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}
		
		// If not found, return language specific categories page or category page or home page link
		return self::getDefaultPage();
	}
	
	public static function getDefaultPage($needles = array())
	{
		static $return = null;
		
		if($return === null)
		{
			$app 		= JFactory::getApplication(); 
			$menus		= $app->getMenu('site');
			$language	= isset($needles['language']) ? $needles['language'] : '*';
			$component	= JComponentHelper::getComponent('com_qazap');

			$attributes = array('component_id');
			$values = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[] = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);
			
			if(!empty($items))
			{			
				if(count($items) == 1)
				{
					return $items[0]->id;
				}				
				
				$lastCategoryID = null;
				
				foreach($items as $item)
				{
					$query = $item->query;
					if((isset($query['category_id']) && isset($query['view']))
						&& ($lastCategoryID === null || $query['category_id'] < $lastCategoryID)
						&& ($query['view'] == 'categories' || $query['view'] == 'category'))
					{
						$lastCategoryID = $query['category_id'];
						$return = $item->id;
					}
				}
			}
			
			if(empty($return))
			{
				$default = $menus->getDefault($language);
				$return = !empty($default->id) ? $default->id : 0;					
			}					
		}
		
		return $return ? $return : null;
	}
	
	
	protected static function _findVendor($needles = null)
	{
		$app			= JFactory::getApplication();
		$input		= $app->input;
		$menus		= $app->getMenu('site');
		$language	= isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$vendor_lookup[$language]))
		{
			self::$vendor_lookup[$language] = array();

			$component	= JComponentHelper::getComponent('com_qazap');

			$attributes = array('component_id');
			$values = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[] = array($needles['language'], '*');
			}

			$items		= $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$vendor_lookup[$language][$view]))
					{
						self::$vendor_lookup[$language][$view] = array();
					}
					if (isset($item->query['category_id']) && isset($item->query['vendor_id']) && $item->query['vendor_id'] > 0) {

						// here it will become a bit tricky
						// language != * can override existing entries
						// language == * cannot override existing entries
						if (!isset(self::$vendor_lookup[$language][$view][$item->query['category_id']]) || $item->language != '*')
						{
							self::$vendor_lookup[$language][$view][$item->query['category_id']] = $item->query['vendor_id'];
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$vendor_lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$vendor_lookup[$language][$view][(int) $id]))
						{
							return self::$vendor_lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();
		if ($active && $active->component == 'com_qazap' && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()) && isset($active->query['vendor_id']) && $item->query['vendor_id'] > 0)
		{
			return $active->query['vendor_id'];
		}
		
		$vendor_id = $input->getInt('vendor_id', 0);
		
		if($input->getCmd('option') == 'com_qazap' && $vendor_id > 0)
		{
			return $vendor_id;
		}

		return null;
	}	
	
	public static function getVendorAlias($vendor_id)
	{
		if(!isset(static::$vendor_alias[$vendor_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
							->select('alias')
							->from('#__qazap_vendor')
							->where('id = ' . (int) $vendor_id);
			$db->setQuery($query);
			$result = $db->loadResult();
			
			if(empty($result))
			{
				static::$vendor_alias[$vendor_id] = false;
			}
			else
			{
				static::$vendor_alias[$vendor_id] = $result;
			}
		}
		
		return static::$vendor_alias[$vendor_id];
	}	
	
	public static function getVendorID($alias)
	{
		if(!isset(static::$vendor_id[$alias]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
							->select('id')
							->from('#__qazap_vendor')
							->where('alias = ' . $db->quote($alias));
							
			$db->setQuery($query);
			$result = $db->loadResult();
			
			if(empty($result))
			{
				static::$vendor_id[$alias] = false;
			}
			else
			{
				static::$vendor_id[$alias] = $result;
			}
		}
		
		return static::$vendor_id[$alias];		
	}
  
	/**
	* Method to get Product Alias of a product
	* 
	* @param undefined $product_id
	* 
	* @return string
	*/
	
	public static function getProductAlias($product_id)
	{
		if(!isset(static::$product_alias[$product_id]))
		{
  		$lang = JFactory::getLanguage();
  		$multiple_language = JLanguageMultilang::isEnabled();
  		$present_language = $lang->getTag();
  		$default_language = $lang->getDefault();
      	      
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			if($multiple_language)
			{
				$query->select('CASE WHEN pd.product_alias IS NULL THEN pdd.product_alias ELSE pd.product_alias END AS product_alias');	
				$query->from('#__qazap_products AS p');
				$query->join('LEFT', '#__qazap_product_details AS pd ON pd.product_id = p.product_id AND pd.language = '.$db->quote($present_language));
				$query->join('LEFT', '#__qazap_product_details AS pdd ON pdd.product_id = p.product_id AND pdd.language = '.$db->quote($default_language));
				$query->where('p.product_id = ' . (int) $product_id);			
			}
			else
			{
				$query->select('product_alias');
				$query->from('#__qazap_product_details');
				$query->where('product_id = ' . (int) $product_id);
				$query->where('language = ' . $db->quote($default_language));
			}
			
			$db->setQuery($query);
			$result = $db->loadResult();
			
			if(empty($result))
			{
				static::$product_alias[$product_id] = (string) $product_id;
			}
			else
			{
				static::$product_alias[$product_id] = (string) $result;
			}			
		}
		
		return static::$product_alias[$product_id];
	} 
	
	public static function getAttributeAliases($attribute_ids)
	{
		if(empty($attribute_ids))
		{
			return null;
		}
		
		$return = array();
		$newIDS = array();
		
		foreach($attribute_ids as $id)
		{
			if(isset(static::$attr_names[$id]))
			{
				$return[] = $id . ':' . urlencode(static::$attr_names[$id]);
			}
			else
			{
				$newIDS[] = (int) $id;
			}
		}
		
		if(!empty($newIDS))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('id, value')
						->from('#__qazap_cartattributes')
						->where('id IN (' . implode(',', $newIDS) . ')');
			$db->setQuery($query);
			$newResults = $db->loadObjectList();
			
			if(!empty($newResults))
			{
				foreach($newResults as $new)
				{
					static::$attr_names[$new->id] = $new->value;
					$return[] = $new->id . ':' . urlencode($new->value);
				}
			}
		}
		
		return $return;
	}
	
	public static function getBrandAlias($brand_ids, $multiples = false)
	{
		$brand_ids = (array) $brand_ids;
		
		if(empty($brand_ids))
		{
			return null;
		}
		
		$return = array();
		$newIDS = array();
		
		foreach($brand_ids as $id)
		{
			if(isset(static::$brand_alias[$id]))
			{
				$return[] = static::$brand_alias[$id];
			}
			else
			{
				$newIDS[] = (int) $id;
			}
		}
		
		if(!empty($newIDS))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('id, alias')
						->from('#__qazap_manufacturers');
						
			if($multiples)
			{
				$query->where('id IN (' . implode(',', $newIDS) . ')');
				$db->setQuery($query);
				$newResults = $db->loadObjectList();				
			}
			else
			{
				$query->where('id = ' . $newIDS[0]);
				$db->setQuery($query);
				$newResult = $db->loadObject();
				if(!empty($newResult))
				{
					$newResults	= array($newResult);
				}					
			}
			
			if(!empty($newResults))
			{
				foreach($newResults as $new)
				{
					static::$brand_alias[$new->id] = $new->alias;
					$return[] = $new->alias;
				}
			}
		}
		
		if(!$multiples)
		{
			if(!empty($return))
			{
				return $return[0];
			}
			else
			{
				return null;
			}			
		}
		
		return $return;
	}	
	
	public static function getBrandByAlias($aliases, $multiple = false)
	{
		$aliases = (array) $aliases;
		
		if(empty($aliases))
		{
			return null;
		}
		
		static $nulls = array();
		$return = array();
		$newAlias = array();
		
		foreach($aliases as $alias)
		{
			if(in_array($alias, static::$brand_alias))
			{
				$return[] = array_search($alias, static::$brand_alias);
			}
			elseif(in_array($alias, $nulls))
			{
				$return[] = null;
			}
			else
			{
				$newAlias[] = $alias;
			}		
		}
		
		$newCount = count($newAlias);
		
		if($newCount > 0)
		{
			$results = array();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('id, alias')
						->from('#__qazap_manufacturers');					
			
			if($newCount == 1)
			{
				$query->where('alias = ' . $db->quote($newAlias[0]));
				$db->setQuery($query);
				$result = $db->loadObject();
				if(!empty($result))
				{
					$results = array($result);
				}			
			}
			else
			{
				$query->where('alias IN (' . implode(',', $db->quote($newAlias)) . ')');
				$db->setQuery($query);
				$results = $db->loadObjectList();				
			}
			
			if(!empty($results))
			{
				foreach($results as $result)
				{
					static::$brand_alias[$result->id] = $result->alias;
					$return[] = $result->id;
				}
			}		
		}
		
		$nulls = array_diff($aliases, static::$brand_alias);
		
		if(!$multiple)
		{
			if(!empty($return))
			{
				return $return[0];
			}
			else
			{
				return null;
			}
		}		
		
		return $return;
	}	

	public static function getOrderDetailsAlias($ordergroup_id)
	{
		$ordergroup_id = (int) $ordergroup_id;
		
		if(empty($ordergroup_id))
		{
			return null;
		}		
		
		if(!isset(static::$orderdetails_alias[$ordergroup_id]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('ordergroup_number')
						->from('#__qazap_ordergroups');

			$query->where('ordergroup_id = ' . (int) $ordergroup_id);
			$db->setQuery($query);
			$result = $db->loadResult();
			
			if(!empty($result))
			{
				static::$orderdetails_alias[$ordergroup_id] = urlencode($result);
			}
			else
			{
				static::$orderdetails_alias[$ordergroup_id] = null;
			}
		}
		
		return static::$orderdetails_alias[$ordergroup_id];
	}	
	
	public static function getOrdergroupIDByAlias($aliases)
	{
		$aliases = (string) $aliases;
		
		if(empty($aliases))
		{
			return null;
		}		
		
		if(!in_array($aliases, static::$orderdetails_alias))
		{
			$ordergroup_number = urldecode($aliases);
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('ordergroup_id')
						->from('#__qazap_ordergroups')
						->where('ordergroup_number = ' . $db->quote($ordergroup_number));
			$db->setQuery($query);
			$result = $db->loadResult();
			
			if(!empty($result))
			{
				static::$orderdetails_alias[$result] = $aliases;
			}
		}
		
		$key = array_search($aliases, static::$orderdetails_alias);
		
		if(!empty($key))
		{
			return $key;
		}
		
		return null;
	}		
}
