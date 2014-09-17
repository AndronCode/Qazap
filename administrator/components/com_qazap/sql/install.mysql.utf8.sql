-- Generation Time: 8:08am on Wednesday 17th September 2014

--
-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_cartattributes`
--

CREATE TABLE IF NOT EXISTS `#__qazap_cartattributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeid` int(11) NOT NULL,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `price` float(50,10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `ordered` int(11) NOT NULL DEFAULT '0',
  `booked_order` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_type_id` (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='cart attributes for product';

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_cartattributestype`
--

CREATE TABLE IF NOT EXISTS `#__qazap_cartattributestype` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `type` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `show_title` tinyint(1) NOT NULL,
  `description` text NOT NULL,
  `tooltip` varchar(255) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `check_stock` tinyint(1) NOT NULL,
  `params` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_categories`
--

CREATE TABLE IF NOT EXISTS `#__qazap_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  `images` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_left` (`lft`),
  KEY `idx_right` (`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_category_details`
--

CREATE TABLE IF NOT EXISTS `#__qazap_category_details` (
  `category_details_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `language` char(7) NOT NULL,
  PRIMARY KEY (`category_details_id`),
  KEY `idx_path` (`path`),
  KEY `idx_alias` (`alias`),
  KEY `idx_category_details` (`category_id`,`language`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_config`
--

CREATE TABLE IF NOT EXISTS `#__qazap_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_offline` int(1) NOT NULL,
  `offline_message` text NOT NULL,
  `fallback_lang` varchar(255) NOT NULL,
  `catalogue_only` int(1) NOT NULL,
  `print_view_link` int(1) NOT NULL,
  `pdf_view_icon` int(1) NOT NULL,
  `stock_level_display` int(1) NOT NULL,
  `low_stock_notofication` int(1) NOT NULL,
  `show_manufacturer` int(1) NOT NULL,
  `pagination_listing` varchar(255) NOT NULL,
  `default_pagination` int(2) NOT NULL DEFAULT '10',
  `latest_products_number` int(1) NOT NULL,
  `featured_products_number` int(1) NOT NULL,
  `show_latest_product` int(1) NOT NULL,
  `minimum_purchase_quantity` int(11) NOT NULL,
  `purchase_quantity_steps` int(11) NOT NULL,
  `maximum_purchase_quantity` int(11) NOT NULL,
  `state` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_countries`
--

CREATE TABLE IF NOT EXISTS `#__qazap_countries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `country_name` varchar(255) NOT NULL,
  `country_3_code` varchar(255) NOT NULL,
  `country_2_code` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_coupon`
--

CREATE TABLE IF NOT EXISTS `#__qazap_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `coupon_code` varchar(255) NOT NULL,
  `math_operation` enum('v','p') NOT NULL,
  `coupon_value` float(50,6) NOT NULL,
  `coupon_usage_type` enum('nl','ul','ol') NOT NULL DEFAULT 'nl',
  `coupon_usage_limit` int(11) NOT NULL,
  `coupon_start_date` datetime NOT NULL,
  `coupon_expiry_date` datetime NOT NULL,
  `min_order_amount` float(50,6) NOT NULL,
  `categories` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_coupon_usage`
--

CREATE TABLE IF NOT EXISTS `#__qazap_coupon_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(21) NOT NULL,
  `ordergroup_number` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_currencies`
--

CREATE TABLE IF NOT EXISTS `#__qazap_currencies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `currency` varchar(255) NOT NULL,
  `exchange_rate` float(12,5) NOT NULL,
  `currency_symbol` varchar(255) NOT NULL,
  `code3letters` varchar(255) NOT NULL,
  `numeric_code` int(11) NOT NULL,
  `decimals` int(2) NOT NULL,
  `decimals_symbol` varchar(11) NOT NULL,
  `format` varchar(30) NOT NULL,
  `thousand_separator` varchar(2) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_customfield`
--

CREATE TABLE IF NOT EXISTS `#__qazap_customfield` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeid` int(11) NOT NULL COMMENT 'customfieldtype group id',
  `value` mediumtext NOT NULL,
  `product_id` int(11) NOT NULL,
  `ordering` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='table for product custom fields';

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_customfieldtype`
--

CREATE TABLE IF NOT EXISTS `#__qazap_customfieldtype` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `type` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `show_title` tinyint(1) NOT NULL,
  `description` text NOT NULL,
  `tooltip` varchar(255) NOT NULL,
  `layout_position` varchar(255) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `params` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_downloads`
--

CREATE TABLE IF NOT EXISTS `#__qazap_downloads` (
  `download_id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `download_passcode` char(128) NOT NULL,
  `order_items_id` int(21) unsigned NOT NULL DEFAULT '0',
  `file_id` int(21) unsigned NOT NULL DEFAULT '0',
  `download_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `download_count` int(11) NOT NULL DEFAULT '0',
  `last_download` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `download_block` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`download_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_emailtemplates`
--

CREATE TABLE IF NOT EXISTS `#__qazap_emailtemplates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `mode` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` mediumtext NOT NULL,
  `altbody` mediumtext NOT NULL,
  `css` text,
  `lang` varchar(255) NOT NULL,
  `default` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `modified_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_files`
--

CREATE TABLE IF NOT EXISTS `#__qazap_files` (
  `file_id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_install`
--

CREATE TABLE IF NOT EXISTS `#__qazap_install` (
  `extension_type` varchar(255) NOT NULL,
  `names` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_manufacturercategories`
--

CREATE TABLE IF NOT EXISTS `#__qazap_manufacturercategories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `manufacturer_category_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `manufacturer_category_description` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_manufacturers`
--

CREATE TABLE IF NOT EXISTS `#__qazap_manufacturers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `manufacturer_name` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `manufacturer_email` varchar(255) NOT NULL,
  `manufacturer_category` int(1) NOT NULL,
  `description` text NOT NULL,
  `manufacturer_url` varchar(255) NOT NULL,
  `images` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_members`
--

CREATE TABLE IF NOT EXISTS `#__qazap_members` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `user_id` varchar(255) NOT NULL,
  `membership_id` varchar(255) NOT NULL,
  `jusergroup_id` int(11) NOT NULL,
  `from_date` datetime NOT NULL,
  `to_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  `effected_items` text NOT NULL COMMENT 'JSON encoded effected Order Items IDS',
  `notified_1` tinyint(1) NOT NULL DEFAULT '0',
  `notified_2` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(10) NOT NULL,
  `modified_time` datetime NOT NULL,
  `last_notified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_membership_history`
--

CREATE TABLE IF NOT EXISTS `#__qazap_membership_history` (
  `id` int(10) NOT NULL,
  `status` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `created_by` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_membership_order_history`
--

CREATE TABLE IF NOT EXISTS `#__qazap_membership_order_history` (
  `membership_order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `member_id` int(11) NOT NULL COMMENT 'Foreign Key #__qazap_members',
  `order_id` int(11) NOT NULL,
  `order_items_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `duration` int(21) NOT NULL COMMENT 'Membership duration in Unix format',
  PRIMARY KEY (`membership_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_memberships`
--

CREATE TABLE IF NOT EXISTS `#__qazap_memberships` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL,
  `plan_duration` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` varchar(255) NOT NULL,
  `access_to_members` text,
  `jusergroup_id` int(11) NOT NULL,
  `jview_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_notify_product`
--

CREATE TABLE IF NOT EXISTS `#__qazap_notify_product` (
  `id` int(21) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) CHARACTER SET latin1 NOT NULL,
  `product_id` int(21) NOT NULL,
  `user_id` int(11) NOT NULL,
  `block` tinyint(1) NOT NULL DEFAULT '0',
  `activation_key` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_order`
--

CREATE TABLE IF NOT EXISTS `#__qazap_order` (
  `order_id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `ordergroup_id` int(21) unsigned NOT NULL COMMENT 'Foreign key #__qazap_ordergroups',
  `user_id` int(21) NOT NULL,
  `vendor` int(21) unsigned NOT NULL COMMENT 'Vendor ID',
  `order_number` varchar(255) NOT NULL,
  `productTotalTax` float(50,10) NOT NULL,
  `productTotalDiscount` float(50,10) NOT NULL,
  `totalProductPrice` float(50,10) NOT NULL,
  `shipmentTax` float(50,10) NOT NULL,
  `shipmentPrice` float(50,10) NOT NULL,
  `paymentTax` float(50,10) NOT NULL,
  `paymentPrice` float(50,10) NOT NULL,
  `CartDiscountBeforeTax` float(50,10) NOT NULL,
  `CartDiscountBeforeTaxInfo` text NOT NULL,
  `CartTax` float(50,10) NOT NULL,
  `CartTaxInfo` text NOT NULL,
  `CartDiscountAfterTax` float(50,10) NOT NULL,
  `CartDiscountAfterTaxInfo` text NOT NULL,
  `coupon_discount` float(50,10) NOT NULL,
  `coupon_code` varchar(255) NOT NULL,
  `coupon_data` varchar(5120) NOT NULL,
  `TotalTax` float(50,10) NOT NULL,
  `TotalDiscount` float(50,10) NOT NULL,
  `Total` float(50,10) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `shipment_method_id` int(11) NOT NULL,
  `order_status` varchar(5) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `idx_group_id` (`ordergroup_id`),
  KEY `idx_vendor_id` (`vendor`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_order_addresses`
--

CREATE TABLE IF NOT EXISTS `#__qazap_order_addresses` (
  `order_address_id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `ordergroup_id` int(21) unsigned NOT NULL COMMENT 'Foreign Key of #__qazap_ordergroups',
  `address_type` enum('bt','st') NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `title` varchar(21) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` int(11) NOT NULL,
  `states_territory` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`order_address_id`),
  KEY `address_type` (`address_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_order_history`
--

CREATE TABLE IF NOT EXISTS `#__qazap_order_history` (
  `order_history_id` int(51) unsigned NOT NULL AUTO_INCREMENT,
  `ordergroup_id` int(21) unsigned NOT NULL COMMENT 'Foreign Key #__qazap_ordergroups',
  `comments` varchar(255) NOT NULL,
  `mail_to_buyer` tinyint(1) NOT NULL DEFAULT '0',
  `mail_to_vendor` tinyint(1) NOT NULL DEFAULT '0',
  `order_status` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`order_history_id`),
  KEY `idx_ordergroup` (`ordergroup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_order_items`
--

CREATE TABLE IF NOT EXISTS `#__qazap_order_items` (
  `order_items_id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `order_id` varchar(255) NOT NULL,
  `product_id` int(21) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  `vendor` int(11) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `stock_affected` int(11) NOT NULL DEFAULT '0',
  `stock_booked` int(11) NOT NULL DEFAULT '0',
  `product_baseprice` float(50,10) NOT NULL,
  `product_basepricewithVariants` float(50,10) NOT NULL,
  `product_salesprice` float(50,10) NOT NULL,
  `product_tax` float(50,10) NOT NULL,
  `product_discount` float(50,10) NOT NULL,
  `product_totalprice` float(50,10) NOT NULL,
  `commission` float(50,10) NOT NULL,
  `total_tax` float(50,10) NOT NULL,
  `total_discount` float(50,10) NOT NULL,
  `order_status` varchar(5) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(255) NOT NULL,
  `product_attributes` mediumtext NOT NULL,
  `product_membership` text NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`order_items_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_order_status`
--

CREATE TABLE IF NOT EXISTS `#__qazap_order_status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `created_by` int(21) NOT NULL,
  `status_code` varchar(3) NOT NULL,
  `status_name` varchar(255) NOT NULL,
  `stock_handle` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_ordergroups`
--

CREATE TABLE IF NOT EXISTS `#__qazap_ordergroups` (
  `ordergroup_id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(21) unsigned NOT NULL COMMENT 'Foreign Key of #__users',
  `ordergroup_number` varchar(51) NOT NULL,
  `order_currency` int(11) NOT NULL,
  `user_currency` int(11) NOT NULL,
  `currency_exchange_rate` float(50,10) NOT NULL,
  `cart_payment_method_id` int(11) NOT NULL,
  `cart_shipment_method_id` int(11) NOT NULL,
  `cart_paymentTax` float(50,10) NOT NULL,
  `cart_paymentPrice` float(50,10) NOT NULL,
  `cart_shipmentTax` float(50,10) NOT NULL,
  `cart_shipmentPrice` float(50,10) NOT NULL,
  `coupon_discount` float(50,10) NOT NULL,
  `coupon_code` varchar(255) NOT NULL,
  `cart_total` float(50,10) NOT NULL,
  `payment_received` float(50,10) NOT NULL,
  `payment_refunded` float(50,10) NOT NULL,
  `order_status` varchar(5) NOT NULL,
  `customer_note` text NOT NULL,
  `ip_address` varchar(16) NOT NULL,
  `access_key` varchar(255) NOT NULL COMMENT 'Secret Access Key for the order group',
  `tos_accept` tinyint(1) NOT NULL,
  `language` char(7) NOT NULL COMMENT 'Language in which the order was placed',
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ordergroup_id`),
  KEY `idx_group_number` (`ordergroup_number`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Order Groups of Multiple Vendor Orders';

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_payment_methods`
--

CREATE TABLE IF NOT EXISTS `#__qazap_payment_methods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `payment_name` varchar(255) NOT NULL,
  `payment_description` text NOT NULL,
  `payment_method` int(21) NOT NULL,
  `countries` text NOT NULL,
  `logo` text NOT NULL,
  `price` float(12,6) NOT NULL,
  `tax` float(12,6) NOT NULL,
  `tax_calculation` enum('p','v') NOT NULL DEFAULT 'p',
  `user_group` varchar(255) NOT NULL,
  `params` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_payments`
--

CREATE TABLE IF NOT EXISTS `#__qazap_payments` (
  `payment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `vendor` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_order_value` float(50,6) NOT NULL,
  `total_confirmed_order` float(50,6) DEFAULT NULL,
  `total_commission_value` float(50,6) NOT NULL,
  `total_confirmed_commission` float(50,6) DEFAULT NULL,
  `last_payment_amount` float(50,6) NOT NULL,
  `last_payment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_paid_amount` float(50,6) NOT NULL,
  `total_balance` float(50,6) NOT NULL,
  `currency` int(11) NOT NULL,
  `payment_amount` float(50,6) NOT NULL,
  `balance` float(50,6) NOT NULL,
  `payment_method` int(11) NOT NULL,
  `note` text NOT NULL,
  `payment_status` tinyint(1) NOT NULL,
  `params` text NOT NULL,
  `mail_sent` tinyint(1) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `modified_time` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_product_details`
--

CREATE TABLE IF NOT EXISTS `#__qazap_product_details` (
  `product_details_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `language` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_alias` varchar(255) NOT NULL,
  `short_description` text NOT NULL,
  `product_description` mediumtext NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  PRIMARY KEY (`product_details_id`),
  KEY `idx_product_details` (`product_id`,`language`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_product_file_map`
--

CREATE TABLE IF NOT EXISTS `#__qazap_product_file_map` (
  `product_id` int(21) unsigned NOT NULL DEFAULT '0' COMMENT 'PK from product table',
  `file_id` int(21) unsigned NOT NULL DEFAULT '0' COMMENT 'PK from file table',
  UNIQUE KEY `idx_downloads` (`product_id`,`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_product_quantity_price`
--

CREATE TABLE IF NOT EXISTS `#__qazap_product_quantity_price` (
  `quantity_price_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `min_quantity` int(21) NOT NULL DEFAULT '0',
  `max_quantity` int(21) NOT NULL DEFAULT '0',
  `product_baseprice` float(50,10) NOT NULL,
  `product_customprice` varchar(60) NOT NULL,
  PRIMARY KEY (`quantity_price_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_product_uom`
--

CREATE TABLE IF NOT EXISTS `#__qazap_product_uom` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `product_attributes` varchar(255) NOT NULL,
  `product_measure_unit` varchar(255) NOT NULL,
  `exchange_rate` varchar(255) NOT NULL,
  `product_measure_unit_name` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_product_user_price`
--

CREATE TABLE IF NOT EXISTS `#__qazap_product_user_price` (
  `user_price_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `usergroup_id` int(11) NOT NULL,
  `product_baseprice` float(50,10) NOT NULL,
  `product_customprice` varchar(60) NOT NULL,
  PRIMARY KEY (`user_price_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_products`
--

CREATE TABLE IF NOT EXISTS `#__qazap_products` (
  `product_id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `block` int(11) NOT NULL,
  `parent_id` int(21) NOT NULL,
  `product_sku` varchar(255) NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `vendor` int(21) NOT NULL,
  `urls` text NOT NULL,
  `manufacturer_id` int(21) NOT NULL,
  `category_id` int(21) NOT NULL,
  `access` int(11) NOT NULL,
  `product_baseprice` float(50,10) NOT NULL,
  `product_customprice` varchar(60) NOT NULL,
  `multiple_pricing` int(1) NOT NULL,
  `dbt_rule_id` int(2) NOT NULL,
  `dat_rule_id` int(2) NOT NULL,
  `tax_rule_id` int(2) NOT NULL,
  `in_stock` int(21) NOT NULL DEFAULT '0',
  `ordered` int(21) NOT NULL DEFAULT '0',
  `booked_order` int(11) NOT NULL DEFAULT '0',
  `product_length` varchar(50) NOT NULL,
  `product_length_uom` varchar(11) NOT NULL,
  `product_width` varchar(50) NOT NULL,
  `product_height` varchar(50) NOT NULL,
  `product_weight` varchar(50) NOT NULL,
  `product_weight_uom` varchar(11) NOT NULL,
  `product_packaging` varchar(50) NOT NULL,
  `product_packaging_uom` varchar(11) NOT NULL,
  `units_in_box` varchar(50) NOT NULL,
  `images` text NOT NULL,
  `related_categories` text NOT NULL,
  `related_products` text NOT NULL,
  `membership` text NOT NULL,
  `params` varchar(5120) NOT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_vendor_id` (`vendor`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_manufacturer_id` (`manufacturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_reviews`
--

CREATE TABLE IF NOT EXISTS `#__qazap_reviews` (
  `id` int(21) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(21) unsigned NOT NULL,
  `product_id` int(21) NOT NULL,
  `review_summary` text NOT NULL,
  `comment` text NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_by_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_by_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_savecart`
--

CREATE TABLE IF NOT EXISTS `#__qazap_savecart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_savecartproduct`
--

CREATE TABLE IF NOT EXISTS `#__qazap_savecartproduct` (
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cartattributes` text NOT NULL,
  `memberships` text NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_shipment_methods`
--

CREATE TABLE IF NOT EXISTS `#__qazap_shipment_methods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `shipment_name` varchar(255) NOT NULL,
  `shipment_description` text NOT NULL,
  `shipment_method` int(21) NOT NULL,
  `countries` text NOT NULL,
  `logo` text NOT NULL,
  `price` float(12,6) NOT NULL,
  `tax` float(12,6) NOT NULL,
  `tax_calculation` enum('p','v') NOT NULL DEFAULT 'p',
  `user_group` varchar(255) NOT NULL,
  `params` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_shop`
--

CREATE TABLE IF NOT EXISTS `#__qazap_shop` (
  `shop_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang` char(7) NOT NULL,
  `name` varchar(510) NOT NULL,
  `company` varchar(510) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `address_1` varchar(510) NOT NULL,
  `address_2` varchar(510) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` int(11) NOT NULL,
  `country` int(11) NOT NULL,
  `zip` varchar(51) NOT NULL,
  `phone_1` varchar(64) NOT NULL,
  `phone_2` varchar(64) NOT NULL,
  `fax` varchar(64) NOT NULL,
  `mobile` varchar(64) NOT NULL,
  `vat` varchar(255) NOT NULL,
  `additional_info` text NOT NULL,
  `tos` mediumtext NOT NULL,
  `description` text NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(21) NOT NULL,
  PRIMARY KEY (`shop_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_states`
--

CREATE TABLE IF NOT EXISTS `#__qazap_states` (
  `id` smallint(1) unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `ordering` int(2) DEFAULT NULL,
  `country_id` smallint(1) unsigned NOT NULL DEFAULT '1',
  `state_name` char(64) DEFAULT NULL,
  `state_3_code` char(3) DEFAULT NULL,
  `state_2_code` char(2) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_state_3_code` (`country_id`,`state_3_code`),
  UNIQUE KEY `idx_state_2_code` (`country_id`,`state_2_code`),
  KEY `i_qazap_country_id` (`country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='States that are assigned to a country';

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_taxes`
--

CREATE TABLE IF NOT EXISTS `#__qazap_taxes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `calculation_rule_name` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `type_of_arithmatic_operation` tinyint(1) NOT NULL DEFAULT '1',
  `math_operation` enum('value','percent') NOT NULL DEFAULT 'value',
  `value` float(10,5) NOT NULL,
  `countries` mediumtext NOT NULL,
  `zipcodes` mediumtext NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) unsigned NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_userfields`
--

CREATE TABLE IF NOT EXISTS `#__qazap_userfields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `field_name` varchar(255) NOT NULL,
  `max_length` int(11) NOT NULL,
  `field_title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `field_type` varchar(255) NOT NULL,
  `values` text NOT NULL,
  `required` tinyint(1) NOT NULL,
  `show_in_userbilling_form` tinyint(1) NOT NULL,
  `show_in_shipment_form` tinyint(1) NOT NULL,
  `read_only` tinyint(1) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_userinfos`
--

CREATE TABLE IF NOT EXISTS `#__qazap_userinfos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(21) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(21) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(21) NOT NULL,
  `modified_time` datetime NOT NULL,
  `user_id` int(21) NOT NULL,
  `address_type` enum('bt','st') NOT NULL,
  `address_name` varchar(50) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `title` varchar(21) DEFAULT NULL,
  `first_name` varchar(55) DEFAULT NULL,
  `middle_name` varchar(55) DEFAULT NULL,
  `last_name` varchar(55) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `country` int(11) NOT NULL,
  `states_territory` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `joomlauser_id` (`user_id`),
  KEY `address_type` (`address_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_vendor`
--

CREATE TABLE IF NOT EXISTS `#__qazap_vendor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` int(10) NOT NULL,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `modified_time` datetime DEFAULT NULL,
  `vendor_admin` int(11) NOT NULL,
  `vendor_group_id` int(11) DEFAULT NULL,
  `shop_name` varchar(50) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `category_list` text NOT NULL,
  `shipment_methods` varchar(255) DEFAULT NULL,
  `title` varchar(10) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `address1` varchar(100) DEFAULT NULL,
  `address2` varchar(100) DEFAULT NULL,
  `country` varchar(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `fax` varchar(15) DEFAULT NULL,
  `image` text,
  `states` varchar(11) DEFAULT NULL,
  `shop_description` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `joomlauser_id` (`vendor_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_vendor_groups`
--

CREATE TABLE IF NOT EXISTS `#__qazap_vendor_groups` (
  `vendor_group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `commission` float(6,3) NOT NULL,
  `jusergroup_id` int(11) NOT NULL,
  `jview_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  PRIMARY KEY (`vendor_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_vendorfields`
--

CREATE TABLE IF NOT EXISTS `#__qazap_vendorfields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `field_name` varchar(255) NOT NULL,
  `max_length` varchar(255) NOT NULL,
  `field_title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `field_type` varchar(255) NOT NULL,
  `values` text NOT NULL,
  `required` varchar(255) NOT NULL,
  `read_only` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `modified_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__qazap_wishlist`
--

CREATE TABLE IF NOT EXISTS `#__qazap_wishlist` (
  `id` int(21) NOT NULL AUTO_INCREMENT,
  `user_id` int(21) NOT NULL,
  `product_id` int(21) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

