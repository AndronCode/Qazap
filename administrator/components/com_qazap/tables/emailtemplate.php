<?php
/**
 * emailtemplate.php
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
* mailing Table class
*/
class QazapTableEmailtemplate extends JTable 
{
	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_emailtemplates', 'id', $db);
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
		if(($task == 'save' || $task == 'apply') && (!JFactory::getUser()->authorise('core.edit.state','com_qazap') && $array['state'] == 1))
		{
			$array['state'] = 0;
		}

		//Support for checkbox field: default
		if (!isset($array['default']))
		{
			$array['default'] = 0;
		}


		return parent::bind($array, $ignore);
	}
    

	/**
	* Overloaded check function
	*/
	public function check() 
	{
		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0) 
		{
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
	}

	/**
	* Method to set the publishing state for a row or list of rows in the database
	* table.  The method respects checked out rows by other users and will attempt
	* to checkin rows that it can after adjustments are made.
	*
	* @param    mixed    An optional array of primary key values to update.  If not
	*                    set the instance property value is used.
	* @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
	* @param    integer The user id of the user performing the operation.
	* @return    boolean    True on success.
	* @since    1.0.0
	*/
	public function publish($pks = null, $state = 1, $userId = 0) 
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) 
		{
            if ($this->$k) 
			{
                $pks = array($this->$k);
            }
            // Nothing to set publishing state on, return false.
            else 
			{
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }		
	
        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) 
		{
            $checkin = '';
        } 
		else 
		{
            $checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
        }

        // Update the publishing state for rows with the given primary keys.
        $this->_db->setQuery(
                'UPDATE `' . $this->_tbl . '`' .
                ' SET `state` = ' . (int) $state .
                ' WHERE (' . $where . ')' .
                $checkin
        );
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) 
		{
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows())) 
		{
            // Checkin each row.
            foreach ($pks as $pk) 
			{
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) 
		{
            $this->state = $state;
        }

        $this->setError('');
        return true;
    }


	public function checkDefault(&$pks)
	{
		$pks = (array) $pks;
		
		$query = $this->_db->getQuery(true);
		$query->select('id')
			  ->from($this->_tbl)
			  ->where($this->_db->quoteName('default') . ' = 1')
			  ->where('id IN (' . implode(',', $pks) . ')');
			  
		$this->_db->setQuery($query);
		
		try 
		{
		    $defaults = $this->_db->loadColumn();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
		    return false;
		}
					
		if(!empty($defaults))
		{
			$pks = array_diff($pks, $defaults);
			return $defaults;
		}
		
		return null;		
	}
	
	public function checkSetDefault()
	{
		if($this->state == 0 && $this->default == 1)
		{
			$this->setError(JText::_('COM_QAZAP_ERROR_UNPUBLISHED_ITEM_CAN_NOT_BE_SET_DEFAULT'));
			return false;
		}	
		elseif($this->state == 1 && $this->default == 1)
		{
			$query = $this->_db->getQuery(true);
			$query->select('COUNT(id)')
					->from($this->_tbl)
					->where($this->_db->quoteName('purpose').' = '.$this->_db->quote($this->purpose))
					->where($this->_db->quoteName('lang').' = '.$this->_db->quote($this->lang))
					->where($this->_db->quoteName('default').' = 1')
					->where($this->_db->quoteName('state').' = 1')
					->where($this->_db->quoteName('id') . ' <> ' . (int) $this->id);
			
			try 
			{
				$this->_db->setQuery($query);
		    	$count = $this->_db->loadResult();
			} 
			catch (Exception $e) 
			{
				$this->setError($e->getMessage());
		    	return false;
			}
				
			if($count > 0)
			{
				$this->setError(JText::_('COM_QAZAP_THERE_IS_ALREADY_A_DEFAULT'));
				return false;
			}
			else
			{
				$this->default = 1;
				return true;
			}
		}
		else
		{
			return true;
		}		
	}
	
	public function setDefault($pk = null, $default = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		$pk = (int) $pk;
		$userId = (int) $userId;
		$default = (int) $default;
		$needUpdate = false;
		
		if($default == 1 && $this->state <= 0)
		{
			$this->setError(JText::_('COM_QAZAP_ERROR_UNPUBLISHED_ITEM_CAN_NOT_BE_SET_DEFAULT'));
			return false;
		}		
		
		$query = $this->_db->getQuery(true);
		$query->select('COUNT(id)')
			  ->from($this->_tbl)
			  ->where('purpose = ' . $this->_db->quote($this->purpose))
			  ->where('lang = ' . $this->_db->quote($this->lang))
			  ->where($this->_db->quoteName('default') . ' = 1')
			  ->where($k . ' <> ' . (int) $this->id)
			  ->where('state = 1');
			  
		$this->_db->setQuery($query);
		
		try 
		{
		    $count = $this->_db->loadResult();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
		    return false;
		}
			
		if($default == 0 && $count < 1)
		{
			$purpose = str_replace('.', ' ', ucfirst($this->purpose));
			$this->setError($purpose . ' does not have any other default template');
			return false;
		}
		elseif($default == 1 && $count > 0)
		{			
			$needUpdate	= true;		
		}	
		
		if($default == 1)
		{
			$case = '(CASE WHEN ' . $k . ' = ' . (int) $this->id . ' THEN 1 ELSE 0 END)'; 
		}
		else
		{
			$case = 0;
		}
		
		$query->clear()->update($this->_tbl)
			  ->set($this->_db->quoteName('default') . ' = ' . $case);
		
		if($default == 1)
		{
			  $query->where('purpose = ' . $this->_db->quote($this->purpose));
			  $query->where('lang = ' . $this->_db->quote($this->lang));			
		}
		else
		{
			$query->where($k . ' = ' . (int) $this->id);
		}
			  
		$this->_db->setQuery($query);
		
		try 
		{
		    $result = $this->_db->execute();
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
		    return false;
		}		
		
		$this->checkin($pk);
		$this->default = (int) $default;

		$this->setError('');

		return true;
	}
	/**
	* Method to store a node in the database table.
	*
	* @param   boolean  $updateNulls  True to update null values as well.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTableNested/store
	* @since   1.0.0
	*/
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		if ($this->id)
		{
			// Existing item
			$this->modified_time		= $date->toSql();
			$this->modified_by			= $user->get('id');
		}
		else
		{
			// New contact. A contact created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}		
		
		return parent::store($updateNulls);
	}

}
