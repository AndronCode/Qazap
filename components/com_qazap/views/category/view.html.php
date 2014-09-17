<?php
/**
 * view.html.php
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
 * Base HTML View class for the a Category list
 *
 * @package     Joomla.Legacy
 * @subpackage  View
 * @since       3.2
 */
class QazapViewCategory extends JViewLegacy
{
	/**
	 * State data
	 *
	 * @var    JRegistry
	 * @since  3.2
	 */
	protected $state;

	/**
	 * Category items data
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $items;

	/**
	 * The category model object for this category
	 *
	 * @var    JModelCategory
	 * @since  3.2
	 */
	protected $category;

	/**
	 * The list of other categories for this extension.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $categories;

	/**
	 * Pagination object
	 *
	 * @var    JPagination
	 * @since  3.2
	 */
	protected $pagination;

	/**
	 * Child objects
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $children;
	
	/**
	 * The name of the view to link individual items to
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $viewName;

	protected $sorter;
	
	protected $url;
  
	protected $isFiltered;
	
	protected $filters;
	
	protected $isSearch;
	
	protected $searchWord;


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$params	= $app->getParams();

		// Get some data from the models
		$state			= $this->get('State');
		$items			= $this->get('Items');
		$category		= $this->get('Category');
		$children		= $this->get('Children');
		$parent			= $this->get('Parent');
		$pagination	= $this->get('Pagination');
		$filters		= $this->getFilters();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($category == false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		if ($category->category_id != 'root' && $parent == false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		// Check whether category access level allows access.
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($category->access, $groups))
		{
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Setup the category parameters.
		$cparams					= $category->getParams();
		$category->params	= clone($params);
		$category->params->merge($cparams);

		$children = array($category->category_id => $children);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->state			= &$state;
		$this->items			= &$items;
		$this->category		= &$category;
		$this->children		= &$children;
		$this->params			= &$params;
		$this->parent			= &$parent;
		$this->pagination	= &$pagination;
		$this->user				= &$user;
		$this->url				= QazapHelperRoute::getCategoryRoute($category);
		$this->filters		= &$filters;		

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active = $app->getMenu()->getActive();

		if ((!$active) || ((strpos($active->link, 'view=category') === false) || (strpos($active->link, '&category_id=' . (string) $this->category->category_id) === false)))
		{
			if ($layout = $category->params->get('category_layout'))
			{
				$this->setLayout($layout);
			}
		}
		elseif (isset($active->query['layout']))
		{
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		// Product ordering or sorting element
		$this->sorter = $this->getSorter();
		$this->getSearchPhrase();
		$this->prepareSearch();
		$this->prepareDocument();		

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function prepareDocument()
	{
		$app				= JFactory::getApplication();
		$menus			= $app->getMenu();
		$menu				= $menus->getActive();
		$pathway		= $app->getPathway();
		$id					= (int) @$menu->query['category_id'];
		$meta				= $this->category->getMetadata();
		$title			= $this->params->get('page_title', $meta->get('page_title', $this->category->title));

		if ($menu && $menu->query['option'] == 'com_qazap' && $menu->query['view'] == 'category' && $id == $this->category->category_id)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_QAZAP_PRODUCT_CATEGORY_NAME'));
		}
		
		$path = array();
		
		if ($menu && ($menu->query['option'] != 'com_qazap' || $menu->query['view'] == 'category' || $id != $this->category->category_id))
		{
			// If this is not a single product menu item, set the page title to the product title
			if ($meta->get('page_title', $this->category->title))
			{
				$title = $meta->get('page_title', $this->category->title);
				$path = array(array('title' => $this->category->title, 'link' => ''));
			}	
						
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_qazap' || $menu->query['view'] == 'category' || $id != $category->category_id) && $category->category_id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => QazapHelperRoute::getCategoryRoute($category));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}		
		
		if($this->isSearch)
		{
			$title = JText::sprintf('COM_QAZAP_SEARCH_RESULTS_FOR', $this->escape($this->searchWord));
		}
		
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description') && ($id == $this->category->category_id))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->category->metadesc);
		}

		if ($this->params->get('menu-meta_keywords') && ($id == $this->category->category_id))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->category->metakey);
		}
		
		if ($this->params->get('robots') && ($id == $this->category->category_id))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		else
		{
			$this->document->setMetadata('robots', $meta->get('robots'));
		}	
		
		if ($app->getCfg('MetaAuthor') == '1' && $meta->get('author'))
		{
			$this->document->setMetaData('author', $meta->get('author'));
		}			
		
		
		
	
	}

	/**
	 * Method to add an alternative feed link to a category layout.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function addFeed()
	{
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
	
	// Method to get the ordering and direction url list
	protected function getSorter()
	{
		$model = $this->getModel();
		$params = $this->category->getParams();
		$category_id = $this->category->category_id;
		$ordering_hash = md5(serialize(array_map('intval', (array) $category_id)));
		$default_ordering =  QZProducts::getDefaultOrder($params);
		$active_order = $this->state->get('filter.ordering', $default_ordering);
		$default_direction = $params->get('product_order_dir', 'ASC');		
		$active_direction = $this->state->get('filter.direction', $default_direction); 
		
		$options = $params->get('product_sorting_options', array());
		$fields = QZProducts::getOrderingFields(false);
		$enabled = array_intersect_key($fields, array_flip($options));
		$values = array_values($enabled);		
		$fields = QZProducts::getOrderingFields();
		$fields = array_intersect_key($fields, $enabled);
		$names = array_values($fields);
		$sorters = array_combine($values, $names);
		
		$return = new stdClass;
				
		if(count($sorters))
		{
			$return->orders = array();
			$i = 0;
			foreach($sorters as $value => $name)
			{
				$return->orders[$i] = new stdClass;
				$return->orders[$i]->url = JRoute::_($model->getURL($category_id, 'orderby', $value));
				
				if($value == $default_ordering)
				{
					$return->orders[$i]->title = JText::_('COM_QAZAP_ORDERING_DEFAULT');
				}
				else
				{
					$return->orders[$i]->title = JText::_(strtoupper($name));
				}
				
				if($value == $active_order)
				{
					$return->orders[$i]->active = true;
				}
				else
				{
					$return->orders[$i]->active = false;
				}
				$i++;
			}
		}
		else
		{
			$return->orders = null;
		}
		
		$return->direction = new stdClass;
		
		if(strtolower($active_direction) == 'asc')
		{
			$return->direction->url = JRoute::_($model->getURL($category_id, 'order_dir', 'desc'));
			$return->direction->title = JText::_('COM_QAZAP_DESCENDING');
			$return->direction->action = 'desc';
		}
		else
		{
			$return->direction->url = JRoute::_($model->getURL($category_id, 'order_dir', 'asc'));
			$return->direction->title = JText::_('COM_QAZAP_ASCENDING');
			$return->direction->action = 'asc';
		}		
		
		return $return;
	}
	
	public function getCategoryList($attr = '')
	{
		$app					= JFactory::getApplication();
		$presentCat		= $app->input->getInt('category_id', 0);		
		$options			= JHtml::_('qazapcategory.options', array(1));
		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_QAZAP_ALL_CATEGORIES')));
		
		return JHtml::_('select.genericlist', $options, 'searchcategory', trim($attr), 'value', 'text', $presentCat, 'mod-qazap-search-category');
	}
	
	public function prepareSearch()
	{
		$app							= JFactory::getApplication();
		$presentCat				= $app->input->getInt('category_id', 0);
		$model						= $this->getModel();
		$context					= $model->getContext($presentCat);
		$this->searchWord	= $app->input->getString('filter_search', JText::_('COM_QAZAP_SEARCH_SEARCHBOX_TEXT'));
		$this->isSearch		= ($this->searchWord != JText::_('COM_QAZAP_SEARCH_SEARCHBOX_TEXT'));
	}
	
	public function getSearchPhrase()
	{
		$app								= JFactory::getApplication();
		$searchphrases			= array();
		$searchphrases[]		= JHtml::_('select.option', 'all', JText::_('COM_QAZAP_SEARCH_ALL_WORDS'));
		$searchphrases[]		= JHtml::_('select.option', 'any', JText::_('COM_QAZAP_SEARCH_ANY_WORDS'));
		$searchphrases[]		= JHtml::_('select.option', 'exact', JText::_('COM_QAZAP_SEARCH_EXACT_PHRASE'));
		$this->searchPhrase	= $app->input->getString('searchphrase', 'any');
		$this->searchphrase	= JHtml::_('select.radiolist', $searchphrases, 'searchphrase', '', 'value', 'text', $this->searchPhrase);
	}
	
	protected function getFilters()
	{
		$this->isFiltered	= $this->get('FilterState');
		
		if($this->isFiltered)
		{
			$filters = QZFilters::getInstance();
			
			// Get Attributes
			$attributes = $filters->getAttributes();
			$error = $filters->getError();
			
			if($attributes === false && !empty($error))
			{
				$this->setError($error);
				return null;
			}
			
			// Get Brands
			$brands = $filters->getBrands();
			$error = $filters->getError();
			
			if($brands === false && !empty($error))
			{
				$this->setError($error);
				return null;
			}
			
			if(is_object($brands) && !empty($brands->data))
			{
				$brands->selected = array();
				foreach($brands->data as $brand)
				{
					if($brand->checked)
					{
						$brands->selected[] = $brand;
					}
				}
			}
			
			// Get Prices
			$prices = $filters->getPrices();	
			$error = $filters->getError();
			
			if($prices === false && !empty($error))
			{
				$this->setError($error);
				return null;
			}
			
			if(empty($prices->min_price) && empty($prices->max_price))
			{
				$prices = null;
			}
			elseif($prices->min_price == $prices->max_price)
			{
				$prices = null;
			}
			
			$result = new stdClass;
			$result->attributes = $attributes;
			$result->brands = $brands;
			$result->prices = $prices;
			
			return $result;			
		}
		
		return null;
	}
}
