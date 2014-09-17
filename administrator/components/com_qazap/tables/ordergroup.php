<?php
/**
 * ordergroup.php
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

if(!class_exists('QZCart'))
{
	require(QZPATH_HELPER_ADMIN . DS . 'cart.php');
}
if(!class_exists('QZOrder'))
{
	require(QZPATH_HELPER_ADMIN . DS . 'order.php');
}
if(!class_exists('QZOrderItemNode'))
{
	require(QZPATH_HELPER_ADMIN . DS . 'orderitem.php');
}
/**
* Qazap Ordergroup Table class
*/
class QazapTableOrdergroup extends JTable 
{
	protected $_updateData = array();
	protected $_insertData = array();
	protected $_orderNumber = null;
	
	// The following variabled required for stock update
	protected $_product_ids = array();
	protected $_productStockUpdate = array();
	protected $_productBookUpdate = array();
	protected $_attribute_ids = array();
	protected $_attrStockUpdate = array();
	protected $_attrBookUpdate = array();	
	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_ordergroups', 'ordergroup_id', $db);
	}

	/**
	* Method to load a row from the database by primary key and bind the fields
	* to the JTable instance properties.
	*
	* @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	*                           set the instance property value is used.
	* @param   boolean  $reset  True to reset the default values before loading the new row.
	*
	* @return  boolean  True if successful. False if row not found.
	*
	* @link    http://docs.joomla.org/JTable/load
	* @since   1.0.0
	* @throws  InvalidArgumentException
	* @throws  RuntimeException
	* @throws  UnexpectedValueException
	*/
	public function load($keys = null, $reset = true)
	{
		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeLoad', array($keys, $reset));
		$config = QZApp::getConfig();

		if (empty($keys))
		{
			$empty = true;
			$keys  = array();

			// If empty, use the value of the current key
			foreach ($this->_tbl_keys as $key)
			{
				$empty      = $empty && empty($this->$key);
				$keys[$key] = $this->$key;
			}

			// If empty primary key there's is no need to load anything
			if ($empty)
			{
				return true;
			}
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keyCount = count($this->_tbl_keys);

			if ($keyCount)
			{
				if ($keyCount > 1)
				{
					throw new InvalidArgumentException('Table has multiple primary keys specified, only one primary key value provided.');
				}
				$keys = array($this->getKeyName() => $keys);
			}
			else
			{
				throw new RuntimeException('No table keys defined.');
			}
		}

		if ($reset)
		{
			$this->reset();
		}

		// Initialise the query.
		$query = $this->_db->getQuery(true)
			->select('*')
			->from($this->_tbl);
		$fields = array_keys($this->getProperties());

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields))
			{
				throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
			}
			// Add the search tuple to the query.
			$query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
		}

		$this->_db->setQuery($query);

		$row = $this->_db->loadAssoc();
		
		
		// Check that we have a result.
		if (empty($row))
		{
			$result = false;
		}
		else
		{

			$vendor_carts = array();
			
			if($row[$this->_tbl_key])
			{
				$orderTable = JTable::getInstance('Order', 'QazapTable', array());
				$orderTableName = $orderTable->getTableName();
				$orderTableKey = $orderTable->getKeyName();
				$orderTableFields = array_keys($orderTable->getProperties());
				$orderTableFields = array_map(function($val) { return 'o.'.$val;}, $orderTableFields);
				
				if(is_array($orderTableKey))
				{
					$orderTableKey = $orderTableKey[0];
				}
				
				// Lets load the orders of this ordergroup
				$query->clear()
					->select($orderTableFields)
					->from($orderTableName . ' AS o')
					->select('v.shop_name')
					->join('LEFT', '#__qazap_vendor AS v ON v.id = o.vendor')
					->where('o.ordergroup_id = '. (int) $row[$this->_tbl_key])
					->group($orderTableFields);

				$this->_db->setQuery($query);
				$orders = $this->_db->loadAssocList($orderTableKey);
				
				if(count($orders))
				{
					$orderIDs = array_keys($orders);
					
					// Lets load the order items of these orders
					$query->clear()
						->select('a.*')
						->from('#__qazap_order_items AS a');
					
					if($config->get('downloadable'))
					{
						$query->select('d.download_id, d.download_passcode, d.file_id, d.download_start_date, '.
													'd.download_count, d.last_download, d.download_block')
									->join('LEFT', '#__qazap_downloads AS d ON d.order_items_id = a.order_items_id')
									->select('f.name AS downloadable_file, f.mime_type AS download_mime_type')
									->join('LEFT', '#__qazap_files AS f ON f.file_id = d.file_id');
					}					
					
					$query->where('a.order_id IN ('. implode(',', $orderIDs) .')');
						
					$this->_db->setQuery($query);
					$orderItems = $this->_db->loadObjectList();
					
					$products = array();
					
					if(count($orderItems))
					{
						foreach($orderItems as $orderItem)
						{
							if(!isset($products[$orderItem->order_id]))
							{
								$products[$orderItem->order_id] = array();
							}
							
							$products[$orderItem->order_id][$orderItem->group_id] = new QZOrderItemNode($orderItem, $orderItem->product_quantity);
						}
					}					
					
					foreach($orders as $key => $order)
					{
						$order_id = $order['order_id'];
						$order['products'] = isset($products[$order_id]) ? $products[$order_id] : array();
						$orderTable->bind($order);
						$orderTable->setProducts($order['products']);
						$orderTable->setShopname($order['shop_name']);	
						$vendor_id = $order['vendor'];
						unset($orders[$key]);
						$orders[$vendor_id]	= new QZOrder($orderTable->getProperties());
					}					
					
					$vendor_carts = $orders;
				}
				
				$row['vendor_carts'] = $vendor_carts;
				
				// Now load the addresses for ordergroup
				$row['billing_address'] = array();
				$row['shipping_address'] = array();
				
				$query->clear()
					->select('*')
					->from('#__qazap_order_addresses')
					->where('ordergroup_id = '. (int) $row[$this->_tbl_key]);
					
				$this->_db->setQuery($query);
				$addresses = $this->_db->loadAssocList();
				
				if(!empty($addresses))
				{
					foreach($addresses as $address)
					{
						if($address['address_type'] == 'bt')
						{
							$row['billing_address'] = $address;
						}
						else
						{
							$row['shipping_address'] = $address;
						}
					}
				}		
				
				// Bind the object with the row and return.
				$result = $this->bind($row);									
			}			
			
			$this->setVendorCarts($vendor_carts);
		}
		
		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterLoad', array(&$result, $row));

		return $result;
	}
	
	/**
	* Method to bind an associative array or object to the JTable instance.This
	* method only binds properties that are publicly accessible and optionally
	* takes an array of properties to ignore when binding.
	*
	* @param   mixed  $src     An associative array or object to bind to the JTable instance.
	* @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTable/bind
	* @since   1.0.0
	* @throws  InvalidArgumentException
	*/
	public function bind($src, $ignore = array())
	{
		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			throw new InvalidArgumentException(sprintf('%s::bind(*%s*)', get_class($this), gettype($src)));
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}
		
		// Bind the source value, excluding the ignored fields.
		foreach ($this->getProperties() as $k => $v)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->$k = $src[$k];
				}
			}
		}
		
		if(isset($src['billing_address']))
		{
			$src['billing_address'] = (array) $src['billing_address'];
			$src['billing_address']['order_address_id'] = isset($src['billing_address']['order_address_id']) ? $src['billing_address']['order_address_id'] : 0;
			$this->billing_address = $src['billing_address'];
		}
		else
		{
			$this->billing_address = array();
		}
		
		if(isset($src['shipping_address']))
		{
			$src['shipping_address'] = (array) $src['shipping_address'];
			$src['shipping_address']['order_address_id'] = isset($src['shipping_address']['order_address_id']) ? $src['shipping_address']['order_address_id'] : 0;
			$this->shipping_address = $src['shipping_address'];
		}
		else
		{
			$this->shipping_address = array();
		}		
		
		if (isset($src['vendor_carts']) && count($src['vendor_carts']))
		{
			foreach($src['vendor_carts'] as &$order)
			{
				if(!$order instanceof QZOrder)
				{
					$orderTable = JTable::getInstance('Order', 'QazapTable', array());
					$orderTable->bind($order);
					
					if(count($order['products']))
					{
						$products = array();				
						foreach($order['products'] as $key => $product)
						{
							$group_id = $product['group_id'];
							$products[$group_id] = new QZOrderItemNode($product, $product['product_quantity']);
						}
					}
					$orderTable->setProducts($products);
					$orderTable->setShopname($order['shop_name']);						
					$order	= new QZOrder($orderTable->getProperties());
				}			
			}
					
			$this->vendor_carts = $src['vendor_carts'];
		}

		return true;
	}	


	protected function setVendorCarts($orders)
	{
		$this->vendor_carts = $orders;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/store
	 * @since   1.0.0
	 */
	public function store($updateNulls = false)
	{
		$k = $this->_tbl_keys;

		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeStore', array($updateNulls, $k));

		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if ($this->ordergroup_id)
		{
			// Existing item
			$this->modified_on = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			// New article. An article created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_on)
			{
				$this->created_on = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}		

		// If a primary key exists update the object, otherwise insert it.
		if ($this->hasPrimaryKey())
		{
			$result = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_keys, $updateNulls);
		}
		else
		{
			$result = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys[0]);
		}

		if($result)
		{
			$result = $this->storeAddresses();
		}	

		if($result)
		{
			$result = $this->storeOrders();
		}		
		
		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterStore', array(&$result));

		return $result;
	}
	
	public function storeAddresses()
	{
		$this->billing_address = (array) $this->billing_address;
		$this->shipping_address = (array) $this->shipping_address;

		if(!empty($this->billing_address))
		{			
			$this->billing_address['address_type'] = 'bt';
			$this->billing_address['ordergroup_id'] = $this->ordergroup_id;
			$this->prepareOrderSaveData($this->billing_address, '#__qazap_order_addresses', 'order_address_id');			
		}
		
		if(!empty($this->shipping_address))
		{			
			$this->shipping_address['address_type'] = 'st';
			$this->shipping_address['ordergroup_id'] = $this->ordergroup_id;
			$this->prepareOrderSaveData($this->shipping_address, '#__qazap_order_addresses', 'order_address_id');
		}
		
		$this->insertObject('#__qazap_order_addresses');
		$this->updateObject('#__qazap_order_addresses', 'order_address_id');
		
		$savedAddressIDs = $this->getAddressIDs();
		
		if(empty($savedAddressIDs))
		{
			$this->setError('QazapTableOrdergroup::storeAddresses() says - No saved order addresses found.');
			return false;
		}
		
		if(isset($savedAddressIDs['bt']))
		{
			$this->billing_address['order_address_id'] = $savedAddressIDs['bt']->order_address_id;
		}
		else
		{
			$this->setError('QazapTableOrdergroup::storeAddresses() says - No billing address for order group id '.$this->ordergroup_id.' found.');
			return false;			
		}
		
		if(isset($savedAddressIDs['st']))
		{
			$this->shipping_address['order_address_id'] = $savedAddressIDs['st']->order_address_id;
		}		
		
		return true;
	}
    
	public function storeOrders()
	{
		$orderItems = array();
		$isNew = true;
		
		if(count($this->vendor_carts))
		{
			foreach($this->vendor_carts as &$order)
			{
				$order->ordergroup_id = $this->ordergroup_id;
				
				$this->setNewOrderNumber($order);
				
				if($order->order_id > 0)
				{
					$this->prepareOrderSaveData($order, '#__qazap_order', 'order_id');
					
					if(count($order->products))
					{
						foreach($order->products as &$orderItem)
						{
							$orderItem->order_id = $order->order_id;
							
							if(!empty($orderItem->order_status))
							{
								$this->bindStocks($orderItem);	
							}		
																		
							$this->prepareOrderSaveData($orderItem, '#__qazap_order_items', 'order_items_id');
						}
					}	
					
					$isNew = false;					
				}
				else
				{
					$this->prepareOrderSaveData($order, '#__qazap_order', 'order_id');
				}			
			}
		}
		
		if($isNew && count($this->_insertData['#__qazap_order']))
		{
			$this->insertObject('#__qazap_order');			
			$savedOrders = $this->getOrders();
			$order_ids = array();
			
			foreach($this->vendor_carts as &$order)
			{
				if(isset($savedOrders[$order->vendor]))
				{
					$order->order_id = $savedOrders[$order->vendor]->order_id;
					$order_ids[] = $order->order_id;
					
					if(count($order->products))
					{
						foreach($order->products as &$orderItem)
						{
							$orderItem->order_id = $order->order_id;
							
							if(!empty($orderItem->order_status))
							{
								$this->bindStocks($orderItem);	
							}
							$this->prepareOrderSaveData($orderItem, '#__qazap_order_items', 'order_items_id');
						}
					}					
				}
			}
		}
		
		if($isNew && count($this->_insertData['#__qazap_order_items']))
		{
			$this->insertObject('#__qazap_order_items');
			$savedOrderItems = $this->getOrderItems($order_ids);
			
			foreach($this->vendor_carts as &$order)
			{					
				if(count($order->products))
				{
					foreach($order->products as &$orderItem)
					{
						if(isset($savedOrderItems[$order->order_id]))
						{
							$orderItem->order_items_id = $savedOrderItems[$order->order_id]->order_items_id;
						}
					}
				}					
			}			
		}	
				
		if(count($this->_updateData))
		{
			$this->updateObject('#__qazap_order', 'order_id');
			$this->updateObject('#__qazap_order_items', 'order_items_id');
		}

		return true;		
	}
	
	protected function getOrders()
	{
		$query = $this->_db->getQuery(true)
						->select('order_id, vendor')
						->from('#__qazap_order')
						->where('ordergroup_id = '. (int) $this->ordergroup_id);
		$this->_db->setQuery($query);
		
		return $this->_db->loadObjectList('vendor');
	}
	
	protected function getAddressIDs()
	{
		$query = $this->_db->getQuery(true)
						->select('order_address_id, address_type')
						->from('#__qazap_order_addresses')
						->where('ordergroup_id = '. (int) $this->ordergroup_id);
		$this->_db->setQuery($query);
		
		return $this->_db->loadObjectList('address_type');
	}	
	
	protected function getOrderItems($order_ids)
	{
		$order_ids = array_map('intval', $order_ids);
		
		$query = $this->_db->getQuery(true)
						->select('order_items_id, order_id')
						->from('#__qazap_order_items')
						->where('order_id IN ('. implode(',', $order_ids) .')');
		$this->_db->setQuery($query);
		
		return $this->_db->loadObjectList('order_id');		
	}



	protected function prepareOrderSaveData($data, $tableName, $primaryKey)
	{
		$fields = $this->getTableFields($tableName);
		
		$user = JFactory::getUser();
		$date = JFactory::getDate();
		
		if(!isset($this->_updateData[$tableName]))
		{
			$this->_updateData[$tableName] = array();
		}
		
		if(!isset($this->_insertData[$tableName]))
		{
			$this->_insertData[$tableName] = array();
		}
		
		if(is_object($data))
		{
			$tmp = array();
			foreach($data as $k=>$v)
			{
				$tmp[$k] = $v; 
			}
			$data = $tmp;
		}
		
		if(empty($data))
		{
			return false;
		}	

		if(isset($data[$primaryKey]) &&  $data[$primaryKey] > 0)
		{
			$id = $data[$primaryKey];
			
			foreach($fields as $column)
			{
				if(!isset($this->_updateData[$tableName][$column->Field]))
				{
					$this->_updateData[$tableName][$column->Field] = array();
				}
				
				if(isset($data[$column->Field]) && (is_object($data[$column->Field]) || is_array($data[$column->Field])))
				{
					$this->_updateData[$tableName][$column->Field][$id] = json_encode($data[$column->Field]);
				}
				elseif($column->Field == 'modified_on')
				{
					$this->_updateData[$tableName][$column->Field][$id] = $date->toSQL();
				}
				elseif($column->Field == 'modified_by')	
				{
					$this->_updateData[$tableName][$column->Field][$id] = $user->id;
				}				
				else
				{
					$this->_updateData[$tableName][$column->Field][$id] = isset($data[$column->Field]) ? $data[$column->Field] : $column->Default;					
				}					
			}	
		}
		
		else 
		{
			$tmp = array();
			
			foreach($fields as $column)
			{
				if(isset($data[$column->Field]) && (is_object($data[$column->Field]) || is_array($data[$column->Field])))
				{
					$tmp[$column->Field] = json_encode($data[$column->Field]);
				}
				
				elseif($column->Field == 'created_on')
				{
					$tmp[$column->Field] = $date->toSQL();
				}
				elseif($column->Field == 'created_by')	
				{
					$tmp[$column->Field] = $user->id;
				}			
				else
				{
					$tmp[$column->Field] = isset($data[$column->Field]) ? $data[$column->Field] : '';
				}					
			}
			
			$this->_insertData[$tableName][] = implode(',', $this->_db->quote($tmp));
			unset($tmp);
		}
	}

	public function updateObject($tableName, $primaryKey)
	{		
		if(isset($this->_updateData[$tableName]) && count($this->_updateData[$tableName]))
		{
			$query = $this->_db->getQuery(true)
								->update($this->_db->quoteName($tableName));
								
			$ids = array();
			
			foreach($this->_updateData[$tableName] as $field_name => $values)
			{	
				$when = '';			 
				foreach($values as $id => $value) 
				{
					if(!in_array($id, $ids))
					{
						$ids[] = $id;
					}
					
					$when .= sprintf('WHEN %d THEN %s ', $id, $this->_db->quote($value));
				}
				
				$query->set($this->_db->quoteName($field_name) .' = CASE '.$this->_db->quoteName($primaryKey).' '.$when.' END');
			}
			
			$query->where($this->_db->quoteName($primaryKey).' IN ('.implode(',', $ids).')');
			
			$this->_db->setQuery($query)->execute();
		}	
	}
	
	public function insertObject($tableName)
	{
		if(isset($this->_insertData[$tableName]) && count($this->_insertData[$tableName])) 
		{
			$fields = $this->getTableFields($tableName);
			
			$columns = array();
			foreach($fields as $field)
			{
				$columns[] = $field->Field;
			}
			
			$query = $this->_db->getQuery(true)
							->insert($this->_db->quoteName($tableName))
							->columns($this->_db->quoteName($columns));	
			$query->values(implode('),(', $this->_insertData[$tableName]));
			
			$this->_db->setQuery($query)->execute();			
		}					
	}
	
	/**
	* Method to get all fields of Order Items Table
	* 
	* @return	array 
	* @since	1.0
	*/	
	public function getTableFields($tableName)
	{
		static $cache = array();
		
		if (!isset($cache[$tableName]))
		{
			// Lookup the fields for this table only once.
			$fields = $this->_db->getTableColumns($tableName, false);

			if (empty($fields))
			{
				throw new UnexpectedValueException(sprintf('No columns found for %s table', $name));
			}

			$cache[$tableName] = $fields;
		}

		return $cache[$tableName];
	}

	protected function getNewOrderNumber()
	{
		static $cache = null;
		
		if($cache === null)
		{
			$config = QZApp::getConfig();
			
			$prefix = $config->get('order_prefix', '');
			$suffix = $config->get('order_sufix', '');
			$start = $config->get('order_start', '');
			
			$query = $this->_db->getQuery(true)
					 			->select('order_number')
					 			->from('#__qazap_order')
					 			->order('order_id DESC');

			$this->_db->setQuery($query, 0, 1);
			$last_order_number = $this->_db->loadResult();	 			

			$isNew = false;			
			$suffixLength = !empty($suffix) ? strlen($suffix) : 999999999;
			$fullLength = !empty($last_order_number) ? strlen($last_order_number) : 0;
			$check_suffixPos = ($fullLength - $suffixLength);		
			
			if(empty($last_order_number)) 
			{
				$isNew = true;
			}		
			elseif(strpos($last_order_number, $prefix) !== 0 && strpos($last_order_number, $suffix) !== $check_suffixPos)
			{
				$isNew = true;
			}
			
			if($isNew) 
			{
				$cache = array('prefix' => $prefix, 'number' => (int) $start, 'suffix' => $suffix);	
			} 
			else 
			{
				$prev_start = str_replace($prefix,'',str_replace($suffix, '', $last_order_number));
				$new_start = (int) $prev_start + 1 ;
				$cache = array('prefix' => $prefix, 'number' => (int) $new_start, 'suffix' => $suffix);	
			}			
		}

		return $cache;	
	}
	
	protected function setNewOrderNumber(&$order)
	{		
		$orderNumber = $this->getNewOrderNumber();
		
		if($this->_orderNumber === null)
		{
			$this->_orderNumber = $orderNumber['number'];
		}
		
		if(empty($order->order_number))
		{
			$order->order_number = (string) ($orderNumber['prefix'] . $this->_orderNumber . $orderNumber['suffix']);
			$this->_orderNumber++;
		}
	}
	
	
	
	protected function bindStocks($order_item)
	{	
		$stock_handle = (int) $this->getStockHandle($order_item->order_status);
		
		// Stock Handle can have only three scenario (2, 1 and -1)
		if($stock_handle == 2)
		{
			$stockEffect = ($order_item->stock_affected * -1);
			$bookEffect = $order_item->stock_booked ? ($order_item->stock_booked - $order_item->product_quantity) : $order_item->product_quantity;
			$order_item->stock_affected = 0;
			$order_item->stock_booked = $order_item->product_quantity;	
		}
		elseif($stock_handle == 1)
		{
			$stockEffect = ($order_item->product_quantity - $order_item->stock_affected);
			$bookEffect = ($order_item->stock_booked * -1);
			$order_item->stock_affected = $order_item->product_quantity;
			$order_item->stock_booked = 0;
		}
		else
		{
			$stockEffect = ($order_item->stock_affected * -1);
			$bookEffect = ($order_item->stock_booked * -1);
			$order_item->stock_affected = 0;
			$order_item->stock_booked = 0;
		}					

		if($stockEffect == 0 && $bookEffect == 0)
		{
			return;
		}
		
		if(!in_array($order_item->product_id, $this->_product_ids))
		{
			$this->_product_ids[] = $order_item->product_id;
		}		
		
		if($stockEffect != 0)
		{
			if(array_key_exists($order_item->product_id, $this->_productStockUpdate))
			{
				$this->_productStockUpdate[$order_item->product_id] += $stockEffect;
			}
			else
			{
				$this->_productStockUpdate[$order_item->product_id] = $stockEffect;
			}			
		}

		if($bookEffect != 0)
		{
			if(array_key_exists($order_item->product_id, $this->_productBookUpdate))
			{
				$this->_productBookUpdate[$order_item->product_id] += $bookEffect;
			}
			else
			{
				$this->_productBookUpdate[$order_item->product_id] = $bookEffect;
			}			
		}

		
		// Calcuation for cart attributes // 
		list($product_id, $attribute_ids, $membership_id) = $this->groupToArray($order_item->group_id);
		
		if(count($attribute_ids))
		{
			foreach($attribute_ids as $attribute_id)
			{
				if(!in_array($attribute_id, $this->_attribute_ids))
				{
					$this->_attribute_ids[] = $attribute_id;
				}
				
				if($stockEffect != 0)
				{
					if(array_key_exists($attribute_id, $this->_attrStockUpdate))
					{
						$this->_attrStockUpdate[$attribute_id] += $stockEffect;
					}
					else
					{
						$this->_attrStockUpdate[$attribute_id] = $stockEffect;
					}
				}
				
				if($bookEffect != 0)
				{				
					if(array_key_exists($attribute_id, $this->_attrBookUpdate))
					{
						$this->_attrBookUpdate[$attribute_id] += $bookEffect;
					}
					else
					{
						$this->_attrBookUpdate[$attribute_id] = $bookEffect;
					}	
				}			
			}				
		}
	}	
	
	
	public function updateStocks()
	{
		$query = $this->_db->getQuery(true);
		
		// Update product stocks
		if(count($this->_productStockUpdate) || count($this->_productBookUpdate))
		{
			$query->clear();
			$query->update($this->_db->quoteName('#__qazap_products'));
			
			if(count($this->_productStockUpdate))
			{
				$inStockWhen = '';
				$orderedWhen = '';
				
				foreach($this->_productStockUpdate as $id => $value) 
				{
					$inStockWhen .= ' WHEN '.$this->_db->quote($id).' THEN '.$this->_db->quoteName('in_stock'). ' - '. $value;
					$orderedWhen .= ' WHEN '.$this->_db->quote($id).' THEN '.$this->_db->quoteName('ordered'). ' + '. $value;
				}
				
				$query->set($this->_db->quoteName('in_stock').' = CASE '.$this->_db->quoteName('product_id').' ' . $inStockWhen . ' END');
				$query->set($this->_db->quoteName('ordered').' = CASE '.$this->_db->quoteName('product_id').' ' . $orderedWhen . ' END');	
			}
			
			if(count($this->_productBookUpdate))
			{
				$when = '';
					 
				foreach($this->_productBookUpdate as $id => $value) 
				{
					$when .= ' WHEN '.$this->_db->quote($id).' THEN '.$this->_db->quoteName('booked_order'). ' + '. $value;
				}
				
				$query->set($this->_db->quoteName('booked_order').' = CASE '.$this->_db->quoteName('product_id').' '.$when.' END');
			}
			
			$query->where($this->_db->quoteName('product_id').' IN ('.implode(',', $this->_product_ids).')');
			
			$this->_db->setQuery($query)->execute();
									
		}
		
		// Update attributes stocks
		
		if(count($this->_attrStockUpdate) || count($this->_attrBookUpdate))
		{
			$query->clear();
			$query->update($this->_db->quoteName('#__qazap_cartattributes'));

			if(count($this->_attrStockUpdate))
			{
				$stockWhen = '';
				$orderedWhen = '';
					 
				foreach($this->_attrStockUpdate as $id => $value) 
				{
					$stockWhen .= ' WHEN '.$this->_db->quote($id).' THEN '.$this->_db->quoteName('stock'). ' - '. $value;
					$orderedWhen .= ' WHEN '.$this->_db->quote($id).' THEN '.$this->_db->quoteName('ordered'). ' + '. $value;
				}
				
				$query->set($this->_db->quoteName('stock') .' = CASE ' . $this->_db->quoteName('id') . ' ' . $stockWhen . ' END');
				$query->set($this->_db->quoteName('ordered') . ' = CASE ' . $this->_db->quoteName('id') . ' ' . $orderedWhen . ' END');
			}
			
			if(count($this->_attrBookUpdate))
			{
				$when = '';	
						 
				foreach($this->_attrBookUpdate as $id => $value) 
				{
					$when .= ' WHEN '.$this->_db->quote($id).' THEN ' . $this->_db->quoteName('booked_order') . ' + ' . $value;
				}
				
				$query->set($this->_db->quoteName('booked_order').' = CASE ' . $this->_db->quoteName('id') . ' ' . $when . ' END');
			}			
			
			$query->where($this->_db->quoteName('id').' IN (' . implode(',', $this->_attribute_ids) . ')');
			
			$this->_db->setQuery($query)->execute();
		}
		
		return true;	
	}
	
	/**
	* Method to get stock handle method by status code
	* 
	* @param string $status	Status Code
	* 
	* @return integer	Stock Handle 
	* @since	1.0.0
	*/
	protected function getStockHandle($status)
	{
		static $cache = array();
		
		if(!isset($cache[$status]))
		{
			$query = $this->_db->getQuery(true) 
				 			->select('stock_handle')
				 			->from('#__qazap_order_status')
				 			->where('status_code = ' . $this->_db->quote($status));				 

			$this->_db->setQuery($query);			
			$cache[$status] = $this->_db->loadResult();			
		}

		return $cache[$status];
	}
	
	protected function groupToArray($group_id)
	{
		static $cache = array();
		
		if(!isset($cache[$group_id]))
		{
			$product_group_id = explode('::', $group_id);
			$product_id = $product_group_id[0];
			$product_attr_ids = isset($product_group_id[1]) ? $product_group_id[1] : 0;
			$product_attr_ids = (strpos($product_attr_ids, ':') === false) ? $product_attr_ids : explode(':', $product_attr_ids);
			$membership_id = isset($product_group_id[2]) ? $product_group_id[2] : 0;
			$cache[$group_id] = array($product_id, (array) $product_attr_ids, $membership_id);			
		}

		return $cache[$group_id];	
	}	

}
