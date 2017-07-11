<?php

class KomuKuJVC_Install
{
	public static function installCode()
	{
		$db = XenForo_Application::get('db');

		$db->query("
		CREATE TABLE IF NOT EXISTS `kmk_jobvacan_listingclaim` (
		  `thread_url` varchar(200) NOT NULL,
		  `origanal_owner` varchar(50) NOT NULL,
		  `claimant` varchar(50) NOT NULL,
		  `claimant_email` varchar(200) NOT NULL,
		  `claimant_id` int(10) NOT NULL,
		  `thread_id` int(10) NOT NULL,
		  `claimant_ip` varchar(20) NOT NULL,
		  `claimant_proof` text NOT NULL,
		  UNIQUE KEY `thread_id` (`thread_id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;		
		");
		
		
		
 		$db->query("
		CREATE TABLE IF NOT EXISTS `kmk_jobvacan_dir` (
		  `node_id` int(10) unsigned NOT NULL,
		  `discussion_count` int(10) unsigned NOT NULL DEFAULT '0',
		  `message_count` int(10) unsigned NOT NULL DEFAULT '0',
		  `last_post_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Most recent post_id',
		  `last_post_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Date of most recent post',
		  `last_post_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User_id of user posting most recently',
		  `last_post_username` varchar(50) NOT NULL DEFAULT '' COMMENT 'Username of most recently-posting user',
		  `last_thread_title` varchar(150) NOT NULL DEFAULT '' COMMENT 'Title of thread most recent post is in',
		  `moderate_messages` tinyint(3) unsigned NOT NULL DEFAULT '0',
		  `allow_posting` tinyint(3) unsigned NOT NULL DEFAULT '1',
		  PRIMARY KEY (`node_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	 	");

		
	 	
 		$db->query("
		CREATE TABLE IF NOT EXISTS `kmk_jobvacan_directory_node` (
		  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(50) NOT NULL,
		  `description` text NOT NULL,
		  `node_name` varchar(50) DEFAULT NULL COMMENT 'Unique column used as string ID by some node types',
		  `node_type_id` varbinary(25) NOT NULL,
		  `parent_node_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `display_order` int(10) unsigned NOT NULL DEFAULT '1',
		  `display_in_list` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'If 0, hidden from node list. Still counts for lft/rgt.',
		  `lft` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Nested set info ''left'' value',
		  `rgt` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Nested set info ''right'' value',
		  `depth` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Depth = 0: no parent',
		  `style_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Style override for specific node',
		  `effective_style_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Style override; pushed down tree',
		  PRIMARY KEY (`node_id`),
		  UNIQUE KEY `directory_name_unique` (`node_name`,`node_type_id`),
		  KEY `parent_directory_id` (`parent_node_id`),
		  KEY `display_order` (`display_order`),
		  KEY `lft` (`lft`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=222;
				");
				
		// if this table already has rows, assume this is an upgrade and dont insert the default data into it
	   $isUpgrade = self::tableHasRows($db, "kmk_jobvacan_directory_node");

	   
	   
 		$db->query("
		CREATE TABLE IF NOT EXISTS `kmk_jobvacan_directory_node_type` (
		  `node_type_id` varbinary(25) NOT NULL,
		  `handler_class` varchar(75) NOT NULL,
		  `controller_admin_class` varchar(75) NOT NULL COMMENT 'extends XenForo_ControllerAdmin_Abstract',
		  `datawriter_class` varchar(75) NOT NULL COMMENT 'extends XenForo_DataWriter_Node',
		  `permission_group_id` varchar(25) NOT NULL DEFAULT '',
		  `moderator_interface_group_id` varchar(50) NOT NULL DEFAULT '',
		  `public_route_prefix` varchar(25) NOT NULL,
		  PRIMARY KEY (`node_type_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		
 		$db->query("
		CREATE TABLE IF NOT EXISTS `kmk_jobvacan_thread_map` (
		  `directory_category` int(10) NOT NULL,
		  `thread_id` int(10) NOT NULL,
		  `telephone` varchar(100) NOT NULL,
		  `address_line_1` varchar(100) NOT NULL,
		  `address_line_2` varchar(100) NOT NULL,
		  `town_city` varchar(100) NOT NULL,
		  `postcode` varchar(100) NOT NULL,
		  `website_url` varchar(200) NOT NULL,
		  `website_anchor_text` varchar(100) NOT NULL,
		  `deeplink1_url` varchar(200) NOT NULL,
		  `deeplink1_anchor_text` varchar(100) NOT NULL,
		  `deeplink2_url` varchar(200) NOT NULL,
		  `deeplink2_anchor_text` varchar(100) NOT NULL,
		  `deeplink3_url` varchar(200) NOT NULL,
		  `deeplink3_anchor_text` varchar(100) NOT NULL,
		  PRIMARY KEY (`thread_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");		
		

		
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "logo_image_url", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "youtube_url", "varchar(200) NOT NULL");		
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfielda1", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfielda2", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfielda3", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfielda4", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfieldb1", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfieldb2", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfieldb3", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "customfieldb4", "varchar(200) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "is_claimable", "int(1) NOT NULL DEFAULT '0'");		
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat2", "int(10) NOT NULL DEFAULT '0'");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat3", "int(10) NOT NULL DEFAULT '0'");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat4", "int(10) NOT NULL DEFAULT '0'");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat5", "int(10) NOT NULL DEFAULT '0'");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat1title", "varchar(50) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat2title", "varchar(50) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat3title", "varchar(50) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat4title", "varchar(50) NOT NULL");
		self::addColumnIfNotExist($db, "kmk_jobvacan_thread_map", "cat5title", "varchar(50) NOT NULL");
	
		
		
		
		$db->query("INSERT IGNORE INTO `kmk_content_type` (`content_type`, `addon_id`, `fields`) VALUES ('listingclaim', 'KomuKuJVC', '')");
		$db->query("INSERT IGNORE INTO `kmk_content_type_field` (`content_type`, `field_name`, `field_value`) VALUES ('listingclaim', 'moderation_queue_handler_class', 'KomuKuJVC_ModerationQueueHandler_Listingclaim')");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
		
		
		
	 	$db->query("
		INSERT IGNORE INTO `kmk_jobvacan_directory_node_type` (`node_type_id`, `handler_class`, `controller_admin_class`, `datawriter_class`, `permission_group_id`, `moderator_interface_group_id`, `public_route_prefix`) VALUES
		('Directory', 'KomuKuJVC_NodeHandler_Dir', 'KomuKuJVC_ControllerAdmin_Dir', 'KomuKuJVC_DataWriter_Dir', 'directory', '', 'dirs');
		");		
		
		if(!$isUpgrade)
		{			
		 	$db->query("
			INSERT IGNORE INTO `kmk_jobvacan_directory_node` (`node_id`, `title`, `description`, `node_name`, `node_type_id`, `parent_node_id`, `display_order`, `display_in_list`, `lft`, `rgt`, `depth`, `style_id`, `effective_style_id`) VALUES
			(44, 'Art  & Entertainment', '', NULL, 'Directory', 0, 10, 0, 1, 16, 0, 0, 0),
			(45, 'Business Services (General Public)', '', NULL, 'Directory', 0, 20, 1, 41, 78, 0, 0, 0),
			(46, 'Computers & Internet', '', NULL, 'Directory', 0, 40, 1, 91, 114, 0, 0, 0),
			(47, 'Education', '', NULL, 'Directory', 0, 30, 1, 79, 90, 0, 0, 0),
			(49, 'Finance & Legal', '', NULL, 'Directory', 0, 50, 1, 115, 130, 0, 0, 0),
			(50, 'Health & Beauty', '', NULL, 'Directory', 0, 60, 1, 131, 164, 0, 0, 0),
			(51, 'Home & Garden', '', NULL, 'Directory', 0, 70, 1, 165, 214, 0, 0, 0),
			(53, 'Recreation & Sports', '', NULL, 'Directory', 0, 90, 1, 215, 226, 0, 0, 0),
			(56, 'Shopping', '', NULL, 'Directory', 0, 120, 1, 247, 310, 0, 0, 0),
			(57, 'Society & Reference', '', NULL, 'Directory', 0, 130, 1, 311, 334, 0, 0, 0),
			(58, 'Advice', '', NULL, 'Directory', 57, 1, 1, 312, 313, 1, 0, 0),
			(59, 'Associations & Organisations', '', NULL, 'Directory', 57, 1, 1, 314, 315, 1, 0, 0),
			(60, 'Disability', '', NULL, 'Directory', 57, 1, 1, 316, 317, 1, 0, 0),
			(61, 'Religion', '', NULL, 'Directory', 57, 1, 1, 318, 319, 1, 0, 0),
			(62, 'Support Groups', '', NULL, 'Directory', 57, 1, 1, 320, 321, 1, 0, 0),
			(63, 'Antiques & Collectibles', '', NULL, 'Directory', 56, 10, 1, 256, 257, 1, 0, 0),
			(64, 'Auctions', '', NULL, 'Directory', 56, 20, 1, 258, 259, 1, 0, 0),
			(65, 'Automotive', '', NULL, 'Directory', 56, 30, 1, 260, 261, 1, 0, 0),
			(66, 'Beauty Products', '', NULL, 'Directory', 56, 40, 1, 262, 263, 1, 0, 0),
			(67, 'Books', '', NULL, 'Directory', 56, 50, 1, 266, 267, 1, 0, 0),
			(68, 'Children', '', NULL, 'Directory', 56, 80, 1, 272, 273, 1, 0, 0),
			(69, 'Clothing', '', NULL, 'Directory', 56, 90, 1, 274, 275, 1, 0, 0),
			(71, 'Consumer Electronics', '', NULL, 'Directory', 56, 100, 1, 276, 277, 1, 0, 0),
			(72, 'Crafts', '', NULL, 'Directory', 56, 110, 1, 278, 279, 1, 0, 0),
			(74, 'Computer Consumables', '', NULL, 'Directory', 46, 20, 1, 92, 93, 1, 0, 0),
			(75, 'Computer Repair', '', NULL, 'Directory', 46, 50, 1, 98, 99, 1, 0, 0),
			(76, 'Computer Hardware,  Cables & Networking', '', NULL, 'Directory', 46, 40, 1, 96, 97, 1, 0, 0),
			(77, 'Computer Software', '', NULL, 'Directory', 46, 60, 1, 100, 101, 1, 0, 0),
			(79, 'Data Recovery', '', NULL, 'Directory', 46, 70, 1, 102, 103, 1, 0, 0),
			(80, 'Internet Services', '', NULL, 'Directory', 46, 80, 1, 104, 105, 1, 0, 0),
			(81, 'Web Design', '', NULL, 'Directory', 46, 110, 1, 110, 111, 1, 0, 0),
			(82, 'Computer Games', '', NULL, 'Directory', 46, 30, 1, 94, 95, 1, 0, 0),
			(83, 'Air Conditioning', '', NULL, 'Directory', 45, 20, 1, 44, 45, 1, 0, 0),
			(84, 'Audio & Video', '', NULL, 'Directory', 45, 20, 1, 46, 47, 1, 0, 0),
			(85, 'Catering', '', NULL, 'Directory', 45, 40, 1, 56, 57, 1, 0, 0),
			(86, 'Cleaning Services', '', NULL, 'Directory', 45, 50, 1, 58, 59, 1, 0, 0),
			(87, 'Conferences', '', NULL, 'Directory', 170, 60, 1, 18, 19, 1, 0, 0),
			(88, 'Consultants', '', NULL, 'Directory', 170, 70, 1, 20, 21, 1, 0, 0),
			(89, 'Conventions & Trade Shows', '', NULL, 'Directory', 45, 80, 1, 60, 61, 1, 0, 0),
			(90, 'Corporate', '', NULL, 'Directory', 170, 90, 1, 22, 23, 1, 0, 0),
			(92, 'Information Services', '', NULL, 'Directory', 170, 110, 1, 24, 25, 1, 0, 0),
			(93, 'Human Resources', '', NULL, 'Directory', 170, 120, 1, 26, 27, 1, 0, 0),
			(94, 'Lighting Services', '', NULL, 'Directory', 45, 130, 1, 62, 63, 1, 0, 0),
			(95, 'Management Training', '', NULL, 'Directory', 170, 140, 1, 28, 29, 1, 0, 0),
			(96, 'Marketing and Advertising', '', NULL, 'Directory', 170, 150, 1, 30, 31, 1, 0, 0),
			(97, 'Office Supplies', '', NULL, 'Directory', 170, 160, 1, 32, 33, 1, 0, 0),
			(98, 'Outsourcing', '', NULL, 'Directory', 170, 170, 1, 34, 35, 1, 0, 0),
			(99, 'Printing & Publishers', '', NULL, 'Directory', 45, 180, 1, 66, 67, 1, 0, 0),
			(101, 'Removals & Relocation', '', NULL, 'Directory', 45, 200, 1, 68, 69, 1, 0, 0),
			(102, 'Team Building', '', NULL, 'Directory', 170, 210, 1, 36, 37, 1, 0, 0),
			(103, 'Telecommunications', '', NULL, 'Directory', 170, 220, 1, 38, 39, 1, 0, 0),
			(104, 'Translation Services', '', NULL, 'Directory', 45, 230, 1, 72, 73, 1, 0, 0),
			(105, 'Remote Management', '', NULL, 'Directory', 46, 90, 1, 106, 107, 1, 0, 0),
			(106, 'Search Engine Optimisation', '', NULL, 'Directory', 46, 100, 1, 108, 109, 1, 0, 0),
			(107, 'Web Hosting', '', NULL, 'Directory', 46, 120, 1, 112, 113, 1, 0, 0),
			(108, 'Art Galleries', '', NULL, 'Directory', 44, 1, 1, 2, 3, 1, 0, 0),
			(109, 'Dance & Clubs', '', NULL, 'Directory', 44, 1, 1, 4, 5, 1, 0, 0),
			(110, 'Cinema & Theatre', '', NULL, 'Directory', 44, 1, 1, 6, 7, 1, 0, 0),
			(111, 'Architecture', '', NULL, 'Directory', 51, 10, 1, 166, 167, 1, 0, 0),
			(112, 'Bathrooms & Kitchens', '', NULL, 'Directory', 51, 30, 1, 168, 169, 1, 0, 0),
			(113, 'Bedroom', '', NULL, 'Directory', 51, 40, 1, 170, 171, 1, 0, 0),
			(114, 'Building & Construction', '', NULL, 'Directory', 51, 50, 1, 172, 173, 1, 0, 0),
			(115, 'Conservatories', '', NULL, 'Directory', 51, 60, 1, 174, 175, 1, 0, 0),
			(116, 'Design & Planning', '', NULL, 'Directory', 51, 80, 1, 176, 177, 1, 0, 0),
			(117, 'DIY do it yourself', '', NULL, 'Directory', 51, 90, 1, 178, 179, 1, 0, 0),
			(118, 'Electrical', '', NULL, 'Directory', 51, 100, 1, 180, 181, 1, 0, 0),
			(119, 'Estate Agents & Letting Agents', '', NULL, 'Directory', 51, 110, 1, 182, 183, 1, 0, 0),
			(120, 'Flooring', '', NULL, 'Directory', 51, 120, 1, 184, 185, 1, 0, 0),
			(121, 'Furnishing and Fixtures', '', NULL, 'Directory', 51, 130, 1, 186, 187, 1, 0, 0),
			(122, 'Garages & Workshops', '', NULL, 'Directory', 51, 140, 1, 188, 189, 1, 0, 0),
			(123, 'Gardens', '', NULL, 'Directory', 51, 150, 1, 190, 191, 1, 0, 0),
			(124, 'Tuition, Private & Home Tuition', '', NULL, 'Directory', 45, 240, 1, 74, 75, 1, 0, 0),
			(125, 'Banks & Building Societies', '', NULL, 'Directory', 49, 10, 1, 118, 119, 1, 0, 0),
			(126, 'Insurance', '', NULL, 'Directory', 49, 30, 1, 122, 123, 1, 0, 0),
			(127, 'Mortgages', '', NULL, 'Directory', 49, 50, 1, 126, 127, 1, 0, 0),
			(128, 'Legal Services', '', NULL, 'Directory', 49, 40, 1, 124, 125, 1, 0, 0),
			(129, 'Solicitors', '', NULL, 'Directory', 49, 60, 1, 128, 129, 1, 0, 0),
			(130, 'Barristers', '', NULL, 'Directory', 49, 20, 1, 120, 121, 1, 0, 0),
			(131, 'Birthday & Greeting Cards', '', NULL, 'Directory', 56, 70, 1, 270, 271, 1, 0, 0),
			(132, 'Florist & Flower Arranging', '', NULL, 'Directory', 56, 120, 1, 280, 281, 1, 0, 0),
			(133, 'Food & Groceries', '', NULL, 'Directory', 56, 130, 1, 282, 283, 1, 0, 0),
			(134, 'Furniture', '', NULL, 'Directory', 56, 140, 1, 284, 285, 1, 0, 0),
			(135, 'Gifts and Occasions', '', NULL, 'Directory', 56, 150, 1, 286, 287, 1, 0, 0),
			(136, 'Hair & Beauty', '', NULL, 'Directory', 56, 160, 1, 288, 289, 1, 0, 0),
			(137, 'Jewellery', '', NULL, 'Directory', 56, 170, 1, 290, 291, 1, 0, 0),
			(138, 'Lingerie', '', NULL, 'Directory', 56, 180, 1, 294, 295, 1, 0, 0),
			(139, 'Mobile Phones', '', NULL, 'Directory', 56, 190, 1, 298, 299, 1, 0, 0),
			(140, 'Novelties', '', NULL, 'Directory', 56, 200, 1, 300, 301, 1, 0, 0),
			(141, 'Retailers', '', NULL, 'Directory', 56, 210, 1, 306, 307, 1, 0, 0),
			(142, 'Wine & Alcoholic Beverages', '', NULL, 'Directory', 56, 220, 1, 308, 309, 1, 0, 0),
			(143, 'Car,  Garage Services & Mot Testing', '', NULL, 'Directory', 45, 30, 1, 50, 51, 1, 0, 0),
			(144, 'Accountants', '', NULL, 'Directory', 49, 5, 1, 116, 117, 1, 0, 0),
			(145, 'Builders', '', NULL, 'Directory', 45, 25, 1, 48, 49, 1, 0, 0),
			(146, 'Home Security & Burglar Alarms', '', NULL, 'Directory', 51, 170, 1, 196, 197, 1, 0, 0),
			(147, 'Carpenters & Joiners', '', NULL, 'Directory', 45, 35, 1, 52, 53, 1, 0, 0),
			(148, 'Carpet Cleaners', '', NULL, 'Directory', 45, 36, 1, 54, 55, 1, 0, 0),
			(149, 'Post Offices', '', NULL, 'Directory', 57, 1, 1, 322, 323, 1, 0, 0),
			(150, 'Libraries', '', NULL, 'Directory', 57, 1, 1, 324, 325, 1, 0, 0),
			(151, 'Pest Control', '', NULL, 'Directory', 45, 175, 1, 64, 65, 1, 0, 0),
			(152, 'Locksmiths', '', NULL, 'Directory', 51, 175, 1, 198, 199, 1, 0, 0),
			(153, 'Painters & Decorators', '', NULL, 'Directory', 51, 190, 1, 200, 201, 1, 0, 0),
			(155, 'Swimming Pools', '', NULL, 'Directory', 53, 1, 1, 216, 217, 1, 0, 0),
			(156, 'Taxis & Mini Cabs', '', NULL, 'Directory', 45, 205, 1, 70, 71, 1, 0, 0),
			(157, 'Van & Truck Hire', '', NULL, 'Directory', 45, 250, 1, 76, 77, 1, 0, 0),
			(158, 'Primary Schools', '', NULL, 'Directory', 47, 1, 1, 80, 81, 1, 0, 0),
			(159, 'Secondary Schools', '', NULL, 'Directory', 47, 1, 1, 82, 83, 1, 0, 0),
			(160, 'Colleges', '', NULL, 'Directory', 47, 1, 1, 84, 85, 1, 0, 0),
			(161, 'Universities', '', NULL, 'Directory', 47, 1, 1, 86, 87, 1, 0, 0),
			(162, 'Nursery Child Care', '', NULL, 'Directory', 47, 1, 1, 88, 89, 1, 0, 0),
			(163, 'Pharmacies', '', NULL, 'Directory', 50, 200, 1, 158, 159, 1, 0, 0),
			(164, 'Hospitals', '', NULL, 'Directory', 50, 170, 1, 148, 149, 1, 0, 0),
			(165, 'General Practitioners', '', NULL, 'Directory', 50, 130, 1, 140, 141, 1, 0, 0),
			(166, 'Dentists', '', NULL, 'Directory', 50, 100, 1, 138, 139, 1, 0, 0),
			(167, 'Nutritionists', '', NULL, 'Directory', 50, 180, 1, 154, 155, 1, 0, 0),
			(168, 'Health Insurance', '', NULL, 'Directory', 50, 160, 1, 146, 147, 1, 0, 0),
			(169, 'Veterinary Medicine', '', NULL, 'Directory', 50, 220, 1, 162, 163, 1, 0, 0),
			(170, 'Business Services (Corporate)', '', NULL, 'Directory', 0, 15, 1, 17, 40, 0, 0, 0),
			(171, 'Sport Centres', '', NULL, 'Directory', 53, 1, 1, 218, 219, 1, 0, 0),
			(172, 'Golf', '', NULL, 'Directory', 53, 1, 1, 220, 221, 1, 0, 0),
			(173, 'Horse Riding Stables', '', NULL, 'Directory', 53, 1, 1, 222, 223, 1, 0, 0),
			(174, 'Tennis & Badminton Courts', '', NULL, 'Directory', 53, 1, 1, 224, 225, 1, 0, 0),
			(175, 'Restaurants', '', NULL, 'Directory', 0, 105, 1, 227, 246, 0, 0, 0),
			(176, 'Asian Restaurants', '', NULL, 'Directory', 175, 1, 1, 228, 229, 1, 0, 0),
			(177, 'European Restaurants', '', NULL, 'Directory', 175, 1, 1, 230, 231, 1, 0, 0),
			(178, 'Organic Restaurants', '', NULL, 'Directory', 175, 1, 1, 232, 233, 1, 0, 0),
			(179, 'Pizzerias', '', NULL, 'Directory', 175, 1, 1, 234, 235, 1, 0, 0),
			(180, 'Pub Food', '', NULL, 'Directory', 175, 1, 1, 236, 237, 1, 0, 0),
			(181, 'Steak House Restaurants', '', NULL, 'Directory', 175, 1, 1, 238, 239, 1, 0, 0),
			(182, 'Sea Food Restaurants', '', NULL, 'Directory', 175, 1, 1, 240, 241, 1, 0, 0),
			(183, 'Vegetarian Restaurants', '', NULL, 'Directory', 175, 1, 1, 242, 243, 1, 0, 0),
			(184, 'Wine Bars', '', NULL, 'Directory', 175, 1, 1, 244, 245, 1, 0, 0),
			(185, 'Art Exhibitions', '', NULL, 'Directory', 44, 1, 1, 8, 9, 1, 0, 0),
			(186, 'Churches', '', NULL, 'Directory', 57, 1, 1, 326, 327, 1, 0, 0),
			(187, 'Museums', '', NULL, 'Directory', 57, 1, 1, 328, 329, 1, 0, 0),
			(188, 'Information Service', '', NULL, 'Directory', 57, 1, 1, 330, 331, 1, 0, 0),
			(189, 'Tax Office', '', NULL, 'Directory', 57, 1, 1, 332, 333, 1, 0, 0),
			(190, 'Butchers', '', NULL, 'Directory', 56, 1, 1, 248, 249, 1, 0, 0),
			(191, 'Newsagents', '', NULL, 'Directory', 56, 1, 1, 250, 251, 1, 0, 0),
			(192, 'Travel Agents', '', NULL, 'Directory', 56, 1, 1, 252, 253, 1, 0, 0),
			(193, 'Hairdressers', '', NULL, 'Directory', 45, 1, 1, 42, 43, 1, 0, 0),
			(194, 'Party Shops', '', NULL, 'Directory', 56, 1, 1, 254, 255, 1, 0, 0),
			(195, 'Chauffeur Car / Limousine Hire', '', NULL, 'Directory', 44, 1, 1, 10, 11, 1, 0, 0),
			(196, 'Function Rooms, Halls & Banqueting Facilities', '', NULL, 'Directory', 44, 1, 1, 12, 13, 1, 0, 0),
			(197, 'Bars & Pubs', '', NULL, 'Directory', 44, 1, 1, 14, 15, 1, 0, 0),
			(198, 'Bed Shops', '', NULL, 'Directory', 56, 42, 1, 264, 265, 1, 0, 0),
			(199, 'Bike Shops', '', NULL, 'Directory', 56, 52, 1, 268, 269, 1, 0, 0),
			(200, 'Kitchen Showrooms', '', NULL, 'Directory', 56, 172, 1, 292, 293, 1, 0, 0),
			(201, 'Mens Tailors', '', NULL, 'Directory', 56, 182, 1, 296, 297, 1, 0, 0),
			(202, 'Pet Shops', '', NULL, 'Directory', 56, 202, 1, 302, 303, 1, 0, 0),
			(203, 'Wedding & Bridal wear Shops', '', NULL, 'Directory', 56, 202, 1, 304, 305, 1, 0, 0),
			(204, 'Beauty Consultants', '', NULL, 'Directory', 50, 50, 1, 132, 133, 1, 0, 0),
			(205, 'Beauty Salons', '', NULL, 'Directory', 50, 60, 1, 134, 135, 1, 0, 0),
			(206, 'Chiropodists & Podiatrists', '', NULL, 'Directory', 50, 80, 1, 136, 137, 1, 0, 0),
			(207, 'Hairdressers', '', NULL, 'Directory', 50, 140, 1, 142, 143, 1, 0, 0),
			(208, 'Health & Fitness Centres', '', NULL, 'Directory', 50, 150, 1, 144, 145, 1, 0, 0),
			(209, 'Make Up Artists', '', NULL, 'Directory', 50, 172, 1, 150, 151, 1, 0, 0),
			(210, 'Massage Therapists', '', NULL, 'Directory', 50, 175, 1, 152, 153, 1, 0, 0),
			(211, 'Opticians', '', NULL, 'Directory', 50, 190, 1, 156, 157, 1, 0, 0),
			(212, 'Tattooists', '', NULL, 'Directory', 50, 210, 1, 160, 161, 1, 0, 0),
			(213, 'Gas Central Heating Service', '', NULL, 'Directory', 51, 160, 1, 192, 193, 1, 0, 0),
			(214, 'Glaziers', '', NULL, 'Directory', 51, 162, 1, 194, 195, 1, 0, 0),
			(215, 'Plumbers', '', NULL, 'Directory', 51, 200, 1, 202, 203, 1, 0, 0),
			(216, 'Property & Building Maintenance', '', NULL, 'Directory', 51, 210, 1, 204, 205, 1, 0, 0),
			(217, 'Scaffolding Contractors', '', NULL, 'Directory', 51, 220, 1, 206, 207, 1, 0, 0),
			(218, 'TV Repairs', '', NULL, 'Directory', 51, 230, 1, 208, 209, 1, 0, 0),
			(219, 'Waste Disposal', '', NULL, 'Directory', 51, 240, 1, 210, 211, 1, 0, 0),
			(220, 'Window Cleaners', '', NULL, 'Directory', 51, 250, 1, 212, 213, 1, 0, 0)
			");	
		}

		
		if(!$isUpgrade)
		{
			$db->query("
			INSERT IGNORE INTO `kmk_jobvacan_dir` (`node_id`, `discussion_count`, `message_count`, `last_post_id`, `last_post_date`, `last_post_user_id`, `last_post_username`, `last_thread_title`, `moderate_messages`, `allow_posting`) VALUES
			(44, 0, 0, 0, 0, 0, '', '', 0, 0),
			(45, 0, 0, 0, 0, 0, '', '', 0, 1),
			(46, 0, 0, 0, 0, 0, '', '', 0, 1),
			(47, 0, 0, 0, 0, 0, '', '', 0, 1),
			(49, 0, 0, 0, 0, 0, '', '', 0, 1),
			(50, 0, 0, 0, 0, 0, '', '', 0, 1),
			(51, 0, 0, 0, 0, 0, '', '', 0, 1),
			(53, 0, 0, 0, 0, 0, '', '', 0, 1),
			(56, 0, 0, 0, 0, 0, '', '', 0, 1),
			(57, 0, 0, 0, 0, 0, '', '', 0, 1),
			(58, 0, 0, 0, 0, 0, '', '', 0, 1),
			(59, 0, 0, 0, 0, 0, '', '', 0, 1),
			(60, 0, 0, 0, 0, 0, '', '', 0, 1),
			(61, 0, 0, 0, 0, 0, '', '', 0, 1),
			(62, 0, 0, 0, 0, 0, '', '', 0, 1),
			(63, 0, 0, 0, 0, 0, '', '', 0, 1),
			(64, 0, 0, 0, 0, 0, '', '', 0, 1),
			(65, 0, 0, 0, 0, 0, '', '', 0, 1),
			(66, 0, 0, 0, 0, 0, '', '', 0, 1),
			(67, 0, 0, 0, 0, 0, '', '', 0, 1),
			(68, 0, 0, 0, 0, 0, '', '', 0, 1),
			(69, 0, 0, 0, 0, 0, '', '', 0, 1),
			(71, 0, 0, 0, 0, 0, '', '', 0, 1),
			(72, 0, 0, 0, 0, 0, '', '', 0, 1),
			(74, 0, 0, 0, 0, 0, '', '', 0, 1),
			(75, 0, 0, 0, 0, 0, '', '', 0, 1),
			(76, 0, 0, 0, 0, 0, '', '', 0, 1),
			(77, 0, 0, 0, 0, 0, '', '', 0, 1),
			(79, 0, 0, 0, 0, 0, '', '', 0, 1),
			(80, 0, 0, 0, 0, 0, '', '', 0, 1),
			(81, 0, 0, 0, 0, 0, '', '', 0, 1),
			(82, 0, 0, 0, 0, 0, '', '', 0, 1),
			(83, 0, 0, 0, 0, 0, '', '', 0, 1),
			(84, 0, 0, 0, 0, 0, '', '', 0, 1),
			(85, 0, 0, 0, 0, 0, '', '', 0, 1),
			(86, 0, 0, 0, 0, 0, '', '', 0, 1),
			(87, 0, 0, 0, 0, 0, '', '', 0, 1),
			(88, 0, 0, 0, 0, 0, '', '', 0, 1),
			(89, 0, 0, 0, 0, 0, '', '', 0, 1),
			(90, 0, 0, 0, 0, 0, '', '', 0, 1),
			(92, 0, 0, 0, 0, 0, '', '', 0, 1),
			(93, 0, 0, 0, 0, 0, '', '', 0, 1),
			(94, 0, 0, 0, 0, 0, '', '', 0, 1),
			(95, 0, 0, 0, 0, 0, '', '', 0, 1),
			(96, 0, 0, 0, 0, 0, '', '', 0, 1),
			(97, 0, 0, 0, 0, 0, '', '', 0, 1),
			(98, 0, 0, 0, 0, 0, '', '', 0, 1),
			(99, 0, 0, 0, 0, 0, '', '', 0, 1),
			(101, 0, 0, 0, 0, 0, '', '', 0, 1),
			(102, 0, 0, 0, 0, 0, '', '', 0, 1),
			(103, 0, 0, 0, 0, 0, '', '', 0, 1),
			(104, 0, 0, 0, 0, 0, '', '', 0, 1),
			(105, 0, 0, 0, 0, 0, '', '', 0, 1),
			(106, 0, 0, 0, 0, 0, '', '', 0, 1),
			(107, 0, 0, 0, 0, 0, '', '', 0, 1),
			(108, 0, 0, 0, 0, 0, '', '', 0, 0),
			(109, 0, 0, 0, 0, 0, '', '', 0, 1),
			(110, 0, 0, 0, 0, 0, '', '', 0, 1),
			(111, 0, 0, 0, 0, 0, '', '', 0, 1),
			(112, 0, 0, 0, 0, 0, '', '', 0, 1),
			(113, 0, 0, 0, 0, 0, '', '', 0, 1),
			(114, 0, 0, 0, 0, 0, '', '', 0, 1),
			(115, 0, 0, 0, 0, 0, '', '', 0, 1),
			(116, 0, 0, 0, 0, 0, '', '', 0, 1),
			(117, 0, 0, 0, 0, 0, '', '', 0, 1),
			(118, 0, 0, 0, 0, 0, '', '', 0, 1),
			(119, 0, 0, 0, 0, 0, '', '', 0, 1),
			(120, 0, 0, 0, 0, 0, '', '', 0, 1),
			(121, 0, 0, 0, 0, 0, '', '', 0, 1),
			(122, 0, 0, 0, 0, 0, '', '', 0, 1),
			(123, 0, 0, 0, 0, 0, '', '', 0, 1),
			(124, 0, 0, 0, 0, 0, '', '', 0, 1),
			(125, 0, 0, 0, 0, 0, '', '', 0, 1),
			(126, 0, 0, 0, 0, 0, '', '', 0, 1),
			(127, 0, 0, 0, 0, 0, '', '', 0, 1),
			(128, 0, 0, 0, 0, 0, '', '', 0, 1),
			(129, 0, 0, 0, 0, 0, '', '', 0, 1),
			(130, 0, 0, 0, 0, 0, '', '', 0, 1),
			(131, 0, 0, 0, 0, 0, '', '', 0, 1),
			(132, 0, 0, 0, 0, 0, '', '', 0, 1),
			(133, 0, 0, 0, 0, 0, '', '', 0, 1),
			(134, 0, 0, 0, 0, 0, '', '', 0, 1),
			(135, 0, 0, 0, 0, 0, '', '', 0, 1),
			(136, 0, 0, 0, 0, 0, '', '', 0, 1),
			(137, 0, 0, 0, 0, 0, '', '', 0, 1),
			(138, 0, 0, 0, 0, 0, '', '', 0, 1),
			(139, 0, 0, 0, 0, 0, '', '', 0, 1),
			(140, 0, 0, 0, 0, 0, '', '', 0, 1),
			(141, 0, 0, 0, 0, 0, '', '', 0, 1),
			(142, 0, 0, 0, 0, 0, '', '', 0, 1),
			(143, 0, 0, 0, 0, 0, '', '', 0, 1),
			(144, 0, 0, 0, 0, 0, '', '', 0, 1),
			(145, 0, 0, 0, 0, 0, '', '', 0, 1),
			(146, 0, 0, 0, 0, 0, '', '', 0, 1),
			(147, 0, 0, 0, 0, 0, '', '', 0, 1),
			(148, 0, 0, 0, 0, 0, '', '', 0, 1),
			(149, 0, 0, 0, 0, 0, '', '', 0, 1),
			(150, 0, 0, 0, 0, 0, '', '', 0, 1),
			(151, 0, 0, 0, 0, 0, '', '', 0, 1),
			(152, 0, 0, 0, 0, 0, '', '', 0, 1),
			(153, 0, 0, 0, 0, 0, '', '', 0, 1),
			(155, 0, 0, 0, 0, 0, '', '', 0, 1),
			(156, 0, 0, 0, 0, 0, '', '', 0, 1),
			(157, 0, 0, 0, 0, 0, '', '', 0, 1),
			(158, 0, 0, 0, 0, 0, '', '', 0, 1),
			(159, 0, 0, 0, 0, 0, '', '', 0, 1),
			(160, 0, 0, 0, 0, 0, '', '', 0, 1),
			(161, 0, 0, 0, 0, 0, '', '', 0, 1),
			(162, 0, 0, 0, 0, 0, '', '', 0, 1),
			(163, 0, 0, 0, 0, 0, '', '', 0, 1),
			(164, 0, 0, 0, 0, 0, '', '', 0, 1),
			(165, 0, 0, 0, 0, 0, '', '', 0, 1),
			(166, 0, 0, 0, 0, 0, '', '', 0, 1),
			(167, 0, 0, 0, 0, 0, '', '', 0, 1),
			(168, 0, 0, 0, 0, 0, '', '', 0, 1),
			(169, 0, 0, 0, 0, 0, '', '', 0, 1),
			(170, 0, 0, 0, 0, 0, '', '', 0, 1),
			(171, 0, 0, 0, 0, 0, '', '', 0, 1),
			(172, 0, 0, 0, 0, 0, '', '', 0, 1),
			(173, 0, 0, 0, 0, 0, '', '', 0, 1),
			(174, 0, 0, 0, 0, 0, '', '', 0, 1),
			(175, 0, 0, 0, 0, 0, '', '', 0, 1),
			(176, 0, 0, 0, 0, 0, '', '', 0, 1),
			(177, 0, 0, 0, 0, 0, '', '', 0, 1),
			(178, 0, 0, 0, 0, 0, '', '', 0, 1),
			(179, 0, 0, 0, 0, 0, '', '', 0, 1),
			(180, 0, 0, 0, 0, 0, '', '', 0, 1),
			(181, 0, 0, 0, 0, 0, '', '', 0, 1),
			(182, 0, 0, 0, 0, 0, '', '', 0, 1),
			(183, 0, 0, 0, 0, 0, '', '', 0, 1),
			(184, 0, 0, 0, 0, 0, '', '', 0, 1),
			(185, 0, 0, 0, 0, 0, '', '', 0, 1),
			(186, 0, 0, 0, 0, 0, '', '', 0, 1),
			(187, 0, 0, 0, 0, 0, '', '', 0, 1),
			(188, 0, 0, 0, 0, 0, '', '', 0, 1),
			(189, 0, 0, 0, 0, 0, '', '', 0, 1),
			(190, 0, 0, 0, 0, 0, '', '', 0, 1),
			(191, 0, 0, 0, 0, 0, '', '', 0, 1),
			(192, 0, 0, 0, 0, 0, '', '', 0, 1),
			(193, 0, 0, 0, 0, 0, '', '', 0, 1),
			(194, 0, 0, 0, 0, 0, '', '', 0, 1),
			(195, 0, 0, 0, 0, 0, '', '', 0, 1),
			(196, 0, 0, 0, 0, 0, '', '', 0, 1),
			(197, 0, 0, 0, 0, 0, '', '', 0, 1),
			(198, 0, 0, 0, 0, 0, '', '', 0, 1),
			(199, 0, 0, 0, 0, 0, '', '', 0, 1),
			(200, 0, 0, 0, 0, 0, '', '', 0, 1),
			(201, 0, 0, 0, 0, 0, '', '', 0, 1),
			(202, 0, 0, 0, 0, 0, '', '', 0, 1),
			(203, 0, 0, 0, 0, 0, '', '', 0, 1),
			(204, 0, 0, 0, 0, 0, '', '', 0, 1),
			(205, 0, 0, 0, 0, 0, '', '', 0, 1),
			(206, 0, 0, 0, 0, 0, '', '', 0, 1),
			(207, 0, 0, 0, 0, 0, '', '', 0, 1),
			(208, 0, 0, 0, 0, 0, '', '', 0, 1),
			(209, 0, 0, 0, 0, 0, '', '', 0, 1),
			(210, 0, 0, 0, 0, 0, '', '', 0, 1),
			(211, 0, 0, 0, 0, 0, '', '', 0, 1),
			(212, 0, 0, 0, 0, 0, '', '', 0, 1),
			(213, 0, 0, 0, 0, 0, '', '', 0, 1),
			(214, 0, 0, 0, 0, 0, '', '', 0, 1),
			(215, 0, 0, 0, 0, 0, '', '', 0, 1),
			(216, 0, 0, 0, 0, 0, '', '', 0, 1),
			(217, 0, 0, 0, 0, 0, '', '', 0, 1),
			(218, 0, 0, 0, 0, 0, '', '', 0, 1),
			(219, 0, 0, 0, 0, 0, '', '', 0, 1),
			(220, 0, 0, 0, 0, 0, '', '', 0, 1)		
			");	
		}
		return true;
	}

	
	
	
	public static function uninstallCode()
	{
		$db = XenForo_Application::get('db');
		// clear out moderation queue related to KomuKuJVC
		$db->query("DELETE FROM kmk_moderation_queue WHERE content_type = 'listingclaim'");	
		// remove kmk_content_type_field and kmk_content_type		
		$db->query("DELETE FROM kmk_content_type WHERE addon_id = 'KomuKuJVC'");
		$db->query("DELETE FROM kmk_content_type_field WHERE field_value = 'KomuKuJVC_ModerationQueueHandler_Listingclaim'");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
		
		// drop tables
		$db->query("
			DROP TABLE IF EXISTS
				`kmk_jobvacan_dir`,
				`kmk_jobvacan_directory_node`,
				`kmk_jobvacan_directory_node_type`,
				`kmk_jobvacan_thread_map`,
				`kmk_jobvacan_listingclaim`
		");
		return true;
	}

	
	public static function addColumnIfNotExist($db, $table, $field, $attr)
	{
		if ($db->fetchRow('SHOW columns FROM `'.$table.'` WHERE Field = ?', $field))
		{
			return false;
		}
		
		return $db->query("ALTER TABLE `".$table."` ADD `".$field."` ".$attr);
	}
	
	
	public static function tableHasRows($db, $tableName)
	{
		$stmt  = $db->query('SELECT COUNT(node_id) AS c FROM '.$tableName.' LIMIT 1');
		$row = $stmt->fetch();
		$num_rows = $row['c'];
		if($num_rows > 0){return true;}
		return false;	
	}
	

	
	

}
