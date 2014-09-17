<?php
/**
 * coupon.php
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

jimport('joomla.application.component.modeladmin');

/**
* Qazap model.
*/
class QazapModelCoupon extends JModelAdmin
{
	/**
	* @var		string	The prefix to use with controller messages.
	* @since	1.0.0
	*/
	protected $text_prefix = 'COM_QAZAP';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0.0
	 */
	public function getTable($type = 'Coupon', $prefix = 'QazapTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_qazap.coupon', 'coupon', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_qazap.edit.coupon.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.0.0
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
					->select('a.*')
					->from('#__qazap_coupon AS a')
					->select('COUNT(b.id) AS countUsage')
					->leftjoin('#__qazap_coupon_usage AS b ON a.id = b.coupon_id')
					->where('a.id = '.$pk);
		try
		{
			$db->setQuery($sql);
			$item = $db->loadObject();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		if(!empty($item) && !empty($item->categories))
		{
			$item->categories = json_decode($item->categories, true);
		}
		
		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) 
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '') 
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__qazap_coupon');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}
	/*
	* 
	* @data means the data of the coupon
	* 
	* Save the used coupon data
	*/
	public function saveCouponUsage($couponCode, $ordergroup_number)
	{
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapcoupon');
				
		$result = $dispatcher->trigger('onSaveCouponUsage', array($couponCode));
		
		if(in_array(true, $result, true))
		{
			return true;
		}
		
		$couponTable = $this->getTable();
		
		if(!$coupon = $couponTable->load(array('coupon_code'=>$couponCode)))
		{
			$this->setError(JText::_('COM_QAZAP_COUPON_CODE_ERROR'));
			return false;
		}
		
		if(!isset($couponTable->id) || !$couponTable->id)
		{
			$this->setError(JText::_('COM_QAZAP_COUPON_CODE_ERROR'));
			return false;
		}
		
		$user = JFactory::getUser();
		
		$data = array();
		
		$data['id'] = 0;
		$data['coupon_id'] = $couponTable->id;		
		$data['ordergroup_number'] = $ordergroup_number;		
		$table = $this->getTable('coupon_usage');
		
		try
		{		
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}

			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}			 					
			
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}	
					
			$this->cleanCache();	
		}
		
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}
	
	/*
	* 
	* 
	* @param QZCart object $cart
	* 
	*/
	public function getCoupon($couponCode = null, QZCart $cart)
	{
		$couponCode = (string) trim($couponCode);
		$user = JFactory::getUser();
		$date = JFactory::getDate();
		
		if(!$couponCode || empty($couponCode))
		{
			$this->setError(JText::_('COM_QAZAP_COUPON_CODE_ERROR'));
			return false;
		}
			
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapcoupon');
		
		$return = null;
		$result = $dispatcher->trigger('onGetCoupon', array($couponCode, $cart, &$return));
		
		if($return !== null)
		{
			return $return;
		}		
		
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
					->select('a.id, a.coupon_code, a.coupon_usage_type, a.math_operation, a.coupon_value, '.
										'a.coupon_usage_limit, a.coupon_start_date, a.coupon_expiry_date, '.
										'a.min_order_amount, a.categories')
					->from('#__qazap_coupon AS a');
			 		
		$case  = '(CASE a.coupon_usage_type WHEN ';
		$case .= $db->quote('ul');
		$case .= ' THEN';
		$case .= ' (SELECT COUNT(b.id) FROM #__qazap_coupon_usage AS b';
		$case .= ' WHERE b.coupon_id = a.id AND b.user_id = ' . (int) $user->get('id'). ')';
		$case .= ' ELSE';
		$case .= ' (SELECT COUNT(b.id) FROM #__qazap_coupon_usage AS b WHERE b.coupon_id = a.id)';
		$case .= ' END) AS coupon_usage';
		
		$sql->select($case);
		$sql->where('a.state = 1');
		$sql->where('a.coupon_code = ' . $db->quote($couponCode));

		try
		{
			$db->setQuery($sql);
			$return = $db->loadObject();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}		
		
		if(empty($return))
		{
			$this->setError(JText::_('COM_QAZAP_COUPON_INVALID_CODE'));
			return false;			
		}
		// Checking Coupon Expiry Date//
		if(strtotime($return->coupon_expiry_date) < strtotime($date->toSql()))
		{
			$this->setError(JText::_("COM_QAZAP_COUPON_EXPIRED"));
			return false;
		}
		
		//Checking min order amount//
		if($cart->cart_total < $return->min_order_amount)
		{
			$this->setError(JText::_("COM_QAZAP_MIN_ORDER_VALUE_NOT_REACHED"));
			return false;
		}
		
		//Check coupon usage limit//
		if($return->coupon_usage_type == "ul" || $return->coupon_usage_type == "ol")
		{
			if($return->coupon_usage_limit <= $return->coupon_usage)
			{
				$this->setError(JText::_('COM_QAZAP_LIMIT_REACHED'));
				return false;
			}		
		}
		
		$cartCategories = array();
		$products = $cart->getProducts();
		if(!empty($products))
		{
			foreach($products as $product)
			{
				$cartCategories[$product->category_id] = JText::sprintf('COM_QAZAP_PRODUCT_IN_CATEGORY', $product->product_name, $product->category_name);
			}
		}
		
		if(!empty($return->categories) && is_string($return->categories))
		{
			$categories = json_decode($return->categories, true);
		}
		else
		{
			$categories = array('0');
		}
		
		// Get subcategories from parent categories
		if(!in_array('0', $categories))
		{
			$sql->clear()
				->select('subcat.category_id')
				->from('`#__qazap_categories` AS c')
				->leftjoin('`#__qazap_categories` AS subcat ON subcat.lft BETWEEN c.lft AND c.rgt')
				->where('c.category_id IN ('.implode(',', $categories).')');
			try
			{
				//print($sql);exit;
				$db->setQuery($sql);
				$subcategories = $db->loadColumn();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage);
				return false;
			}
			
			$mismatch = array();
			
			if(!empty($subcategories))
			{
				$mismatch = array_diff_key($cartCategories, array_flip($subcategories));
			}
			
			if(!empty($mismatch))
			{
				$unmatched_products = implode(',', $mismatch);
				$this->setError(JText::sprintf('COM_QAZAP_COUPON_CANNOT_BE_APPLIED', $unmatched_products));
				return false;
			}						
		}		
				
		return $return;
	}


	
	public function getCouponUsage($pk=null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			 ->select('a.id, b.username, a.ordergroup_number, a.date')
			 ->from('#__qazap_coupon_usage AS a')
			 ->join('LEFT', '#__users AS b ON a.user_id = b.id')
			 ->where('a.coupon_id = ' . (int) $pk);
			 
		try
		{
			$db->setQuery($sql);
			$results = $db->loadObjectList();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return $results;
	}
	
	public function deleteCouponUsage($couponCode = null, $ordergroup_id = null)
	{		
		if(!$couponCode || !$ordergroup_id)
		{
			$this->setError('COM_QAZAP_PLEASE_PROVIDE_A_VALID_INPUT');
			return false;
		}
		
		$db = $this->getDbo();
		$sql = $db->getQuery(true)
			 ->delete('a.id')
			 ->from('`#__qazap_coupon_usage` AS a');
			 
		$subQuery = 'SELECT b.id FROM `#__qazap_coupon` AS b WHERE b.coupon_code = ' . $db->quote($couponCode);
		
		$sql->where('a.coupon_id = (' . $subQuery . ')');
		$sql->where('a.ordergroup_id = '. (int) $ordergroup_id);
		
		try
		{
			$db->setQuery($sql);
			$db->query();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}
	
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		$db = $this->getDbo();
		
		if(false === parent::delete($pks))
		{
			$this->setError(Jtext::_('COM_QAZAP_DELETE_UNSUCCESSFULL'));
			return false;
		}
		
		$sql = $db->getQuery(true)
				 ->delete($db->quoteName('#__qazap_coupon_usage'))
				 ->where('coupon_id IN ('.implode(',',$pks).')');
		try
		{
			$db->setQuery($sql);
			$db->query();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		return true;
	}
	
	public function save($data)
	{		
		$data['categories'] = (array) isset($data['categories']) ? $data['categories'] : array(0);
		$data['categories'] = array_map('intval', $data['categories']);
		
		if(in_array(0, $data['categories']) || empty($data['categories']))
		{
			$data['categories'] = array(0);
		}
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('qazapsystem');

		// Trigger the onEventBeforeSave event.
		$result = $dispatcher->trigger('onBeforeSave', array('coupon', &$data, $isNew));
		
		return parent::save($data);
	}
	
}