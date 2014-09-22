<?php
/**
 * mail.php
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

jimport('joomla.application.component.modellegacy');

if(!class_exists('Mustache_Autoloader'))
{
	require QAZAP_LIBRARIES . DS . 'Mustache' . DS . 'Autoloader.php';
	Mustache_Autoloader::register();
}
if(!class_exists('Emogrifier'))
{
	require QAZAP_LIBRARIES . DS . 'emogrifier' . DS . 'emogrifier.php';
}
/**
 * Qazap mail model.
 *
 * @package     Qazap.Administrator
 * @subpackage  com_qazap
 * @since       1.0
 */
class QazapModelMail extends JModelLegacy
{
	
	protected $_templates = array();
	
	protected $_mail_type = null;
	
	protected $_data = null;
	
	protected $_to = array();
	
	protected $_to_method = null;
	
	protected $_subject = null;
	
	protected $_mode = 1;
	
	protected $_body = null;
	
	protected $_css = null;
	
	protected $_attachments = array();
	
	protected $_copyto_vendor = false;
	
	protected $_vendor_email = null;
	
	protected $_copyto_admin = false;
	
	protected $_multiReturn = array();
	
	protected $_display_message = null;
	
	protected $_language = null;
	
	
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->_display_message = isset($config['display_message']) ? $config['display_message'] : true;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Emailtemplate', $prefix = 'QazapTable', $config = array())
	{
		JTable::addIncludePath(QZPATH_TABLE_ADMIN);
		return JTable::getInstance($type, $prefix, $config);
	}
		
	/**
	* Send Email
	* 
	* @param string $type Required type of the email
	* @param array or object $data Required data of the email
	* @param integer $isNew Is new or the ID.
	* 
	*/	
	public function send($type = null, $data = null, $isNew = null, $language = null)
	{
		if(!$type)
		{
			$this->setError('COM_QAZAP_MAIL_EMAIL_TYPE_NOT_DEFINED');
			return false;
		}	
		
		if(!$data)
		{
			$this->setError('COM_QAZAP_MAIL_EMAIL_DATA_NOT_AVAILABLE');
			return false;
		}	
		
		$this->reset();
		
		if(!empty($language))
		{
			$this->_language = $language;
		}
		
		// Process and finalize the data before we can get email subject and body.
		if(!$this->processData($type, $data, $isNew))
		{
			$this->setError($this->getError());
			return false;
		}
		
		// Get email body and subject	
		if(!$this->setContent())
		{
			$this->setError($this->getError());
			return false;
		}

		// automatically removes html formatting
		if (!$this->_mode)
		{
			$this->_body = JFilterInput::getInstance()->clean($this->_body, 'string');
		}

		// Check for a message body and subject
		if (!$this->_body || !$this->_subject)
		{
			$this->setError(JText::_('COM_QAZAP_MAIL_DATA_PROCESSING_FAILED'));
			return false;
		}

		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();

		// Get the Mailer
		$mailer = JFactory::getMailer();
		$params = QZApp::getConfig();

		// Build email message format.
		$mailer->setSender(array($app->getCfg('mailfrom'), $app->getCfg('fromname')));
		$mailer->setSubject(stripslashes($this->_subject));
		$mailer->setBody($this->_body);
		
		$mailer->IsHTML($this->_mode);				
		
		$to_addresses = array();
		$cc_addresses = array();
		$bcc_addresses = array();
		
		// Add recipients
		if(!$this->_to_method || $this->_to_method == 'to')
		{
			$mailer->addRecipient($this->_to);
			$to_addresses[] = is_array($this->_to) ? ($to_addresses + $this->_to) : $this->_to;
		}
		elseif($this->_to_method == 'cc')
		{
			$mailer->addCC($this->_to);
			$cc_addresses[] = is_array($this->_to) ? ($cc_addresses + $this->_to) : $this->_to;
		}
		elseif($this->_to_method == 'bcc')
		{
			$mailer->addBCC($this->_to);
			$bcc_addresses[] = is_array($this->_to) ? ($bcc_addresses + $this->_to) : $this->_to;
		}
		
		// If copy to be provided to vendor
		if ($this->_copyto_vendor && $this->_vendor_email != null)
		{
			$mailer->addCC($this->_vendor_email);
			$cc_addresses[] = is_array($this->_vendor_email) ? ($cc_addresses + $this->_vendor_email) : $this->_vendor_email;	
		}
		
		// If copy to be provided to admin
		if($this->_copyto_admin)
		{
			$mailer->addCC($app->getCfg('mailfrom'));
			$cc_addresses[] = $app->getCfg('mailfrom');	
		}

		// Attach file if set
		if(!empty($this->_attachments))
		{
			foreach($this->_attachments as $attachment)
			{
				$mailer->addAttachment($attachment);
			}
		}		
		
		// Send the Mail
		$rs	= $mailer->Send();

		// Check for an error
		if ($rs instanceof Exception)
		{
			$this->setError($rs->getMessage());
			return false;
		} 
		elseif (empty($rs))
		{
			$this->setError(JText::sprintf('COM_QAZAP_MAIL_TYPE_MAIL_COULD_NOT_BE_SENT', $type));
			return false;
		}
		
		if($this->_display_message)
		{
			if(!empty($to_addresses))
			{
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_MAIL_EMAIL_SENT_TO_USER', implode(',', $to_addresses)), 'message');
			}			
			
			if(!empty($cc_addresses))
			{
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_MAIL_EMAIL_COPY_SENT_TO_USER', implode(', ', $cc_addresses)), 'message');
			}
			
			if(!empty($bcc_addresses))
			{
				$app->enqueueMessage(JText::sprintf('COM_QAZAP_MAIL_EMAIL_BCC_SENT_TO_USER', implode(', ', $bcc_addresses)), 'message');
			}			
		}
						
		return true;
	}	

	public function sendMultiple($type = null, $dataArray = array(), $isNew = null, $language = null)
	{
		if(count($dataArray))
		{
			foreach($dataArray as $data)
			{
				$this->_multiReturn[] = $this->send($type, $data, $isNew, $language);
			}
			
			return $this->_multiReturn;
		}
		
		return false;		
	}
	/**
	* Prepare and set email subject, body and mode.
	* 
	* @return Boolean.
	*/
	protected function setContent()
	{
		$app = JFactory::getApplication();
		
		if(!$this->_mail_type || !$this->_data)
		{
			$app->enqueueMessage('COM_QAZAP_MAIL_ERROR_CREATING_MESSAGE_BODY', 'error');
			return false;
		}
		
		if(!$template = $this->getTemplate($this->_mail_type, $this->_language))
		{
			$this->setError($this->getError());
			return false;
		}
		
		$compiler = new Mustache_Engine(array(
								'escape' => function($value) {
															return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
   													},
								'currencyDisplay' => function($value) {
															return QazapHelper::currencyDisplay($value);
   													},   													
								'charset' => 'UTF-8'
		));
		
		$this->_subject = $compiler->render($template->subject, $this->_data);
		$this->_body = $compiler->render($template->body, $this->_data);
		$this->_mode = (int) $template->mode;
		
		if($this->_mode)
		{
			$this->_body = '<!DOCTYPE html>' . "\n" . '<html>' . $this->_body . '</html>';
			
			if($template->css)
			{			
				$content = new Emogrifier($this->_body, $template->css);
				$this->_body = $content->emogrify();
			}				
		}

		return true;
	}


	protected function setVendorEmail($vendorID)
	{		
		$vendor = $this->getTable('Vendor', 'QazapTable');

		if(!$vendor->load($vendorID))
		{
			JFactory::getApplication()->enqueueMessage($vendor->getError(), 'warning');
			return false;						
		}
			
		if(!isset($vendor->vendor_admin))
		{
			JFactory::getApplication()->enqueueMessage('COM_QAZAP_MAIL_ERROR_VENDOR_NOT_FOUND', 'warning');
			return false;				
		}
		
		if(!isset($vendor->email) || !$vendor->email)
		{
			$user = JFactory::getUser($vendor->vendor_admin);
			return $user->email;
		}
			
		return $vendor->email;
	}

	/**
	* Process Data 
	* 
	* Dynamically find the data processing function for available type 
	* @param $type. Type of email
	* @param $data. Data array
	* @param $isNew. If primary key of the object not available in $data then $isNew must be provided to fetch data
	* 
	*/
	protected function processData($type, $data, $isNew)
	{
		$fncName = 'process' . ucfirst(str_replace('_', '', $type)) . 'Data';
		
		if (!method_exists($this, $fncName))
		{
			$this->setError(__METHOD__ . ' -- Unknown method: ' . $fncName);
			return false;
		}		
		
		return $this->$fncName($data, $isNew);
	}
	

	/**
	* Process Order Data 
	* 
	* Porcess order data 
	* @param $data. Data array
	* @param $isNew. If primary key of the object not available in $data then $isNew must be provided to fetch data
	* 
	*/	
	protected function processOrdergroupData($data, $isNew)
	{
		if(!isset($data['variables']) || !isset($data['email']))
		{
			$this->setError('no valid order data availble to send email');
			return false;
		}		
		
		if($isNew)
		{
			$this->_mail_type = 'ordergroup.new';
		}
		elseif($data['invoice'])
		{
			$this->_mail_type = 'ordergroup.invoice';
		}
		else
		{
			$this->_mail_type = 'ordergroup.status';
		}

		$this->_to = $data['email'];
		$this->_data = $data['variables'];
		$this->_data->created_on = QazapHelper::displayDate($this->_data->created_on);
		$this->_data->modified_on = QazapHelper::displayDate($this->_data->modified_on);
		$layout = new JLayoutFile('orderitems', QZPATH_LAYOUT . DS . 'mail', array('client' => 'site'));
		$items_table = $layout->render($this->_data);
		$this->_data->setItemsTable($items_table);
		$this->_data->prepareMailData();

		return true;
	}
	
	/**
	* Process Order Data 
	* 
	* Porcess order data 
	* @param $data. Data array
	* @param $isNew. If primary key of the object not available in $data then $isNew must be provided to fetch data
	* 
	*/	
	protected function processOrderData($data, $isNew)
	{	
		if(!isset($data['variables']) || !isset($data['email']))
		{
			$this->setError('no valid order data availble to send email');
			return false;
		}		
		
		if($isNew)
		{
			$this->_mail_type = 'order.new';
		}
		elseif($data['invoice'])
		{
			$this->_mail_type = 'order.invoice';
		}
		else
		{
			$this->_mail_type = 'order.status';
		}
		
		$this->_to = $data['email'];
		$this->_data = $data['variables'];
		$layout = new JLayoutFile('singleorderitems', QZPATH_LAYOUT . DS . 'mail', array('client' => 'site'));
		$this->_data['ordergroup']->created_on = QazapHelper::displayDate($this->_data['ordergroup']->created_on);
		$this->_data['ordergroup']->modified_on = QazapHelper::displayDate($this->_data['ordergroup']->modified_on);		
		$items_table = $layout->render($this->_data);
		$this->_data['order']->setItemsTable($items_table);
		$this->_data['order']->order_status = QazapHelper::orderStatusNameByCode($this->_data['order']->order_status);
		$this->_data['ordergroup']->prepareMailData();

		return true;
	}
	
	/**
	* Process Vendor Data 
	* 
	* Porcess Vendor data 
	* @param $data. Data array
	* @param $isNew. If primary key of the object not available in $data then $isNew must be provided to fetch data
	* 
	*/	
	protected function processVendorData($data, $new_id)
	{
		$params = JComponentHelper::getParams('com_qazap');	
		
		$isNew = false;
		$mail_type = 'vendor.';
		
		if(!$data['id'])
		{
			if(!$new_id)
			{
				$this->setError('COM_QAZAP_MAIL_ERROR_VENDOR_NOT_FOUND');
				return false;
			}
			$mail_type .= 'new';
			$data['id'] = $new_id;
			$isNew = true;
		}
		else
		{
			$mail_type .= 'statuschange';

		}
		
		$data['state'] = ($data['state'] == 1) ? JText::_('COM_QAZAP_ACTIVE') : JText::_('COM_QAZAP_INACTIVE');
		$this->_mail_type = $mail_type;
		
		$data['category_list'] = $this->toCategoryString($data['category_list']);

		$this->_data = $data;		
	
		if(isset($data['email']))
		{
			$this->_to = $data['email'];		
		}
		else
		{
			$user = JFactory::getUser($data['vendor_admin']);
			$this->_to = $user->email;			
		}
		
		if($isNew && $params->get('new_vendor_email_to_admin', 1))
		{
			$this->_copyto_admin = true;									
		}
		elseif($params->get('update_vendor_email_to_admin', 1))
		{
			$this->_copyto_admin = true;
		}

		return true;
	}

	/**
	* Process Payment Data 
	* 
	* Porcess Payment data 
	* @param $data. Data array
	* 
	*/	
	protected function processPaymentData($data)
	{
		
		$params = JComponentHelper::getParams('com_qazap');	
		$VendorInfo = JArrayHelper::fromObject(QZVendor::get($data['vendor'])) ;
		$data = array_merge($data,$VendorInfo);

		$mail_type = 'payment.';
		
		if(!$data['vendor'])
		{
				$this->setError('COM_QAZAP_MAIL_ERROR_VENDOR_NOT_FOUND');
				return false;
		}
		else
		{
			$mail_type .= 'vendor';
		}
		
		$this->_mail_type = $mail_type;

		$this->_data = $data;		
	
		if(isset($data['vendor']))
		{
			//$this->_to = $data['email'];		
			
			$this->_to = $data['email'];
		}
		
		if($params->get('payment_mail_to_admin', 1))
		{
			$this->_copyto_admin = true;
		}

		return true;
	}	
	
	protected function toString($data)
	{
		foreach($data as $key=>$item)
		{			
			if((is_array($item) || is_object($item)) && !empty($item))
			{
				foreach($item as $k=>$v)
				{
					$name = $key.':'.$k;
					$data->$name = $v;
				}
				unset($data->$key);
			}
		}
		
		return $data;
	}


	protected function toCategoryString($array)
	{
		// Run the query to built concat list of categories$isNew
		
		return 'String';
	}
	
	protected function processQuestionData($data, $isNew)
	{
		$options = array('custom_fields'=>false, 'attributes'=>false);
		$helper = QZProducts::getInstance($options);
		$product = $helper->get($data['product_id']);
		
		$params = JComponentHelper::getParams('com_qazap');	
		$VendorInfo = JArrayHelper::fromObject(QZVendor::get($data['vendor_id'])) ;
		$data = array_merge($data,$VendorInfo);
		$data['product_name'] = $product->product_name;
		

		$mail_type = 'question.';
		
		if(!$isNew)
		{
			$mail_type .= 'product';
		}
		$this->_mail_type = $mail_type;

		$this->_data = $data;		
	
		if(isset($data['vendor_id']))
		{		
			
			$this->_to = $data['email'];
		}

		if($params->get('ask_a_question', 1))
		{
			$this->_copyto_admin = true;
		}
		
		
		return true;		
	}
	
	protected function processMemberData($data, $isNew)
	{
		if(!isset($data['email']) || empty($data['email']))
		{
			$this->setError('QazapModelMail::processMemberData(). No email address available to send mail.');
			return false;
		}

		$this->_mail_type = 'member.' . $data['type'];		
		$this->_to = $data['email'];
		
		$mailData = $data['data'];
		$mailData->to_date = QazapHelper::displayDate($mailData->to_date);
		$mailData->from_date = QazapHelper::displayDate($mailData->from_date);		
		
		$this->_data = $mailData;
		
		return true;
	}
	
	/**
	* Method for Process Notify data
	* 
	* @return Boolean true if success, false if failed 
	*
	* @param array data Array for sending verification mail ,Array for sending product in stock mail
	* 
	* @param boolean verification  True for verification mail sending, False for sending product in stock notification
	*  
	* @since 1.0
	*/
	protected function processNotifyData($data, $verification)
	{
		$uri = JUri::getInstance();
		$config = JComponentHelper::getParams('com_qazap');
		$app = JFactory::getApplication();
		
		$mail_type = 'notify.';
		
		if($verification)
		{
			$mail_type .= 'verification';
			$data['verification_link'] = JRoute::_('index.php?option=com_qazap&task=notify.activate&key=' . trim($data['activation_key']));
		}
		else
		{
			$mail_type .= 'mail';
		}
		
		if(!isset($data['user_email']) || empty($data['user_email']))
		{
			$this->setError('QazapModelMail::processNotifyData() says no user email address found');
			return false;			
		}
		
		if(is_string($data['user_email']) && strpos($data['user_email'], ','))
		{
			$data['user_email'] = explode(',', $data['user_email']);
		}
		
		if($verification)
		{
			$this->_to = $data['user_email'];
			$this->_data = $data;
		}		
		else
		{
			if($config->get('notify_multiple_users_mail') == 'to')
			{				
				$this->_to = $data['user_email'];
			}
			elseif($config->get('notify_multiple_users_mail') == 'cc')
			{
				$this->_to_method = 'cc';
				$this->_to = $data['user_email'];
			}
			elseif($config->get('notify_multiple_users_mail') == 'bcc')
			{
				$this->_to_method = 'bcc';
				$this->_to = $data['user_email'];
			}
			
			$this->_data = $data['products'];
		}
		
		$this->_mail_type = $mail_type;	
		return true;		
	}	

	public function reset() 
	{
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) 
		{
			$blankInstance = new static;
		}	
		//requires PHP 5.3+  for older versions you could do $blankInstance = new get_class($this);
		else
		{
			$blankInstance = new get_class($this);
		}
		
		$reflBlankInstance = new \ReflectionClass($blankInstance);
		
		foreach ($reflBlankInstance->getProperties() as $prop) 
		{
			if($prop->name != '_display_message')
			{
				$prop->setAccessible(true);
				$this->{$prop->name} = $prop->getValue($blankInstance);				
			}
		}
	}
	
	public function getTemplate($purpose = null, $language = null)
	{
		$purpose = trim($purpose);
		
		if(empty($purpose))
		{
			$this->setError(JText::_('COM_QAZAP_ERROR_INVALID_MAIL_TEMPLATE'));
			return false;
		}
		
		$hash = md5('Purpose:' . $purpose . '.Language:' . $language);
		
		if(!isset($this->_templates[$hash]))
		{
			$lang = JFactory::getLanguage();
			$language = !empty($language) ? $language : $lang->getTag();
			$default_language = $lang->getDefault();
			$all_langs = array($language, '*', $default_language);
				
			$db = $this->getDbo();
			$query = $db->getQuery(true)
									->select('*')
									->from($db->quoteName('#__qazap_emailtemplates'))
									->where($db->quoteName('state') . ' = 1')
									->where($db->quoteName('default') . ' = 1')
									->where($db->quoteName('lang') . ' IN (' . implode(',', $db->quote($all_langs)) . ')')
									->where($db->quoteName('purpose') . ' = ' . $db->quote($purpose));
			
			try
			{
				$db->setQuery($query);
				$results = $db->loadObjectList('lang');
			}
			catch(Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
			
			if(empty($results))
			{
				$this->setError(JText::sprintf('COM_QAZAP_ERROR_MAIL_TEMPLATE_NOT_FOUND', $purpose, $language));
				return false;
			}
			
			if(isset($results[$language]))
			{
				$this->_templates[$hash] = $results[$language];
			}
			elseif(isset($results['*']))
			{
				$this->_templates[$hash] = $results['*'];
			}
			else
			{
				$this->_templates[$hash] = $results[$default_language];
			}
		}		
		
		return $this->_templates[$hash];
	}
	
}