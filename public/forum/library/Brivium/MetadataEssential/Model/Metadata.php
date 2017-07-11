<?php

class Brivium_MetadataEssential_Model_Metadata extends XenForo_Model
{
	/**
	 * Get all metadatas , in their relative display order.
	 *
	 * @return array Format: [] => metadata info
	 */
	public function getAllMetadatas()
	{
		return $this->fetchAllKeyed('
				SELECT *
				FROM kmk_brivium_metadata
				ORDER BY `order` ASC, `title` 
			', 'metadata_id');
	}
	
	/**
	 * Returns metadata records based on metadata_id.
	 *
	 * @param string $metadataId
	 *
	 * @return array|false
	 */
	public function getMetadataById($metadataId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM kmk_brivium_metadata
			WHERE  metadata_id = ?
		', array( $metadataId));
	}
	/**
	 * Prepares a collection of metadata fetching related conditions into an SQL clause
	 *
	 * @param array $conditions List of conditions
	 * @param array $fetchOptions Modifiable set of fetch options (may have joins pushed on to it)
	 *
	 * @return string SQL clause (at least 1=1)
	 */
	public function prepareMetadataConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions = array();
		$db = $this->_getDb();

		if (!empty($conditions['description']))
		{
			if (is_array($conditions['description']))
			{
				$sqlConditions[] = 'metadata.description LIKE ' . XenForo_Db::quoteLike($conditions['description'][0], $conditions['description'][1], $db);
			}
			else
			{
				$sqlConditions[] = 'metadata.description LIKE ' . XenForo_Db::quoteLike($conditions['description'], 'lr', $db);
			}
		}
		if (!empty($conditions['metadata_id']))
		{
			if (is_array($conditions['metadata_id']))
			{
				$sqlConditions[] = 'metadata.metadata_id IN (' . $db->quote($conditions['metadata_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'metadata.metadata_id = ' . $db->quote($conditions['metadata_id']);
			}
		}
		
		if (!empty($conditions['content_type']))
		{
			$sqlConditions[] = 'metadata.content_type = ' . $db->quote($conditions['content_type']);
		}
		if (!empty($conditions['content_id']))
		{
			$sqlConditions[] = 'metadata.content_id = ' . $db->quote($conditions['content_id']);
		}
		
		return $this->getConditionsForClause($sqlConditions);
	}
	public function prepareMetadataFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinMetadatas = '';
		$orderBy = '';
		if (!empty($fetchOptions['order']))
		{
			switch ($fetchOptions['order'])
			{
				case 'content_type':
				case 'content_id':
					$orderBy = 'metadata.' . $fetchOptions['order'];
					break;
				default:
					$orderBy = 'metadata.metadata_id';	
			}
			if (!isset($fetchOptions['orderDirection']) || $fetchOptions['orderDirection'] == 'desc')
			{
				$orderBy .= ' DESC';
			}
			else
			{
				$orderBy .= ' ASC';
			}
		}
		return array(
			'selectFields' => $selectFields,
			'joinMetadatas'   => $joinMetadatas,
			'orderClause'  => ($orderBy ? "ORDER BY $orderBy" : '')
		);
	}
	
	/**
	 * Gets metadatas that match the given conditions.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return array Format: [metadata id] => info
	 */
	public function getMetadatas(array $conditions, array $fetchOptions = array())
	{
		$whereConditions = $this->prepareMetadataConditions($conditions, $fetchOptions);

		$sqlClauses = $this->prepareMetadataFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		
		return $this->fetchAllKeyed($this->limitQueryResults(			'
				SELECT metadata.*
					' . $sqlClauses['selectFields'] . '
				FROM kmk_brivium_metadata AS metadata
				' . $sqlClauses['joinMetadatas'] . '
				WHERE ' . $whereConditions . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'metadata_id');
	}
	
	/**
	 * Gets the count of metadatas with the specified criteria.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 *
	 * @return integer
	 */
	public function countMetadatas(array $conditions)
	{
		$fetchOptions = array();
		$whereConditions = $this->prepareMetadataConditions($conditions, $fetchOptions);
		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM `kmk_brivium_metadata` AS `metadata`
			WHERE ' . $whereConditions . '
		');
	}
	
	public function getRobotMetaData($metaData)
	{
		$robotOptions = XenForo_Application::get('options')->BRME_robotsMetadata;
		if(!empty($robotOptions['enabled_robots'])){
			if(empty($robotOptions['robots'])){
				$robotOptions['robots'] = '';
			}
			$robots = $robotOptions['robots'];
			$robots = strtolower($robots);
			$robots = str_replace(' ', '', $robots);

			if (!empty($robots)) {
				$listRobot = explode(',',$robots);
			}
			else{
				$listRobot = array();
			}

			if(!empty($metaData['index'])){
				if($metaData['index']==2){
					if(!in_array('noindex',$listRobot)){
						$indexPos = array_search('index', $listRobot);
						if($indexPos!==false && !empty($listRobot[$indexPos])){
							$listRobot[$indexPos] = 'noindex';
						}else{
							$listRobot[] = 'noindex';
						}
					}
				}else if($metaData['index']==1){
					if(!in_array('index',$listRobot)){
						$indexPos = array_search('noindex', $listRobot);
						if($indexPos!==false && !empty($listRobot[$indexPos])){
							$listRobot[$indexPos] = 'index';
						}else{
							$listRobot[] = 'index';
						}
					}
					else
					{
						if (array_search('nofollow', $listRobot) !== false)
						{
							unset($listRobot[array_search('nofollow', $listRobot)]);
						}
						if (array_search('NOFOLLOW', $listRobot) !== false)
						{
							unset($listRobot[array_search('NOFOLLOW', $listRobot)]);
						}
					}
				}
			}

			if(!empty($metaData['follow'])){
				if($metaData['follow'] == 2){
					if(!in_array('nofollow',$listRobot) || !in_array('NOFOLLOW',$listRobot)){
						$indexPos = array_search('follow', $listRobot);
						if($indexPos!=false && !empty($listRobot[$indexPos])){
							$listRobot[$indexPos] = 'nofollow';
						}else{
							$listRobot[] = 'nofollow';
						}
					}
				}else if($metaData['follow']==1){
					if(!in_array('follow',$listRobot)){
						$indexPos = array_search('nofollow', $listRobot);
						if($indexPos!=false && !empty($listRobot[$indexPos])){
							$listRobot[$indexPos] = 'follow';
						}else{
							$listRobot[] = 'follow';
						}
					}
					else
					{
						if (array_search('nofollow', $listRobot) !== false)
						{
							unset($listRobot[array_search('nofollow', $listRobot)]);
						}
						if (array_search('NOFOLLOW', $listRobot) !== false)
						{
							unset($listRobot[array_search('NOFOLLOW', $listRobot)]);
						}
					}
				}
			}
			$metaData['robot']  = implode(',',$listRobot);
		}
		return $metaData;
	}
	
	
	public function insertMetadata($data)
	{
		
		$defaultData = array(
			'content_type' 		=>	'',
			'content_id' 		=>	0,
			'title' 		=>	'',
			'description' 		=>	'',
			'keywords' 			=>	'',
			'author' 			=>	'',
			'index' 			=>	0,
			'follow' 			=>	0,
			'use_child' 			=>	1,
		);
		$defaultData = array_merge($defaultData, $data);
		$this->_getDb()->query('
			INSERT ' . (XenForo_Application::get('options')->enableInsertDelayed ? 'DELAYED' : '') . ' INTO kmk_brivium_metadata
				(`content_type`, `content_id`, `title`, `description`, `keywords`, `author`, `index`, `follow`, `use_child`)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?)
		', $defaultData);
		return true;
	}
	
	public function updateMetadata($data,$contentType,$contentId)
	{
		if(!$this->getMetadata($contentType,$contentId)){
			return $this->insertMetadata($data);
		}
		$db = $this->_getDb();
		$defaultData = array(
			'content_type' 		=>	'',
			'content_id' 		=>	0,
			'title' 		=>	'',
			'description' 		=>	'',
			'keywords' 			=>	'',
			'author' 			=>	'',
			'index' 			=>	0,
			'follow' 			=>	0,
			'use_child' 			=>	1,
		);
		$defaultData = array_merge($defaultData, $data);
		$condition = 'content_type = ' . $db->quote($contentType) . ' AND content_id = ' . $db->quote($contentId);
		$db->update('kmk_brivium_metadata',
			array(
				'title' => $defaultData['title'],
				'description' => $defaultData['description'],
				'keywords' => $defaultData['keywords'],
				'author' => $defaultData['author'],
				'index' => $defaultData['index'],
				'follow' => $defaultData['follow'],
				'use_child' => $defaultData['use_child'],
			),$condition
		);
		return true;
	}
	
	
	public function canChangeTitleMeta($nodeId, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($nodeId, $viewingUser, $nodePermissions);

		return XenForo_Permission::hasContentPermission($nodePermissions, 'BRME_changeTitleMeta');
	}
	public function canChangeDescriptionMeta($nodeId, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($nodeId, $viewingUser, $nodePermissions);

		return XenForo_Permission::hasContentPermission($nodePermissions, 'BRME_changeDesMeta');
	}
	public function canChangeKeywordMeta($nodeId, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($nodeId, $viewingUser, $nodePermissions);

		return XenForo_Permission::hasContentPermission($nodePermissions, 'BRME_changeKeyword');
	}
	
	public function canChangeRobotIndex($nodeId, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($nodeId, $viewingUser, $nodePermissions);

		return XenForo_Permission::hasContentPermission($nodePermissions, 'BRME_changeRobotIndex');
	}
	
	public function canChangeRobotFollow($nodeId, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReferenceForNode($nodeId, $viewingUser, $nodePermissions);

		return XenForo_Permission::hasContentPermission($nodePermissions, 'BRME_changeRobotFollow');
	}
	
	
	public function canChangeResourceTitleMeta($categoryId, &$errorPhraseKey = '', array $categoryPermissions = null, array $viewingUser = null)
	{
		$this->_getResourceModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, $categoryPermissions);
		
		return XenForo_Permission::hasContentPermission($categoryPermissions, 'BRME_changeTitleMeta');
	}
	public function canChangeResourceDescriptionMeta($categoryId, &$errorPhraseKey = '', array $categoryPermissions = null, array $viewingUser = null)
	{
		$this->_getResourceModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, $categoryPermissions);
		
		return XenForo_Permission::hasContentPermission($categoryPermissions, 'BRME_changeDesMeta');
	}
	public function canChangeResourceKeywordMeta($categoryId, &$errorPhraseKey = '', array $categoryPermissions = null, array $viewingUser = null)
	{
		$this->_getResourceModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, $categoryPermissions);
		
		return XenForo_Permission::hasContentPermission($categoryPermissions, 'BRME_changeKeyword');
	}
	
	public function canChangeResourceRobotIndex($categoryId, &$errorPhraseKey = '', array $categoryPermissions = null, array $viewingUser = null)
	{
		$this->_getResourceModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, $categoryPermissions);
		
		return XenForo_Permission::hasContentPermission($categoryPermissions, 'BRME_changeRobotIndex');
	}
	
	public function canChangeResourceRobotFollow($categoryId, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
		$this->_getResourceModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, $categoryPermissions);
		
		return XenForo_Permission::hasContentPermission($categoryPermissions, 'BRME_changeRobotFollow');
	}
	 
	public function getMetadata($contentType,$contentId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM kmk_brivium_metadata
			WHERE  content_type = ? AND content_id = ?
		', array( $contentType,$contentId));
	}
	
	
	public function cleanString($string)
	{
		
		$string = str_ireplace('prbreak]', 'prebreak]', $string);
		$string = preg_replace('#\n{3,}#', "\n\n", trim($string));
		$string = preg_replace('#\[(quote)[^\]]*\].*\[/\\1\]#siU', ' ', $string);
		$string = preg_replace('#\[(attach|media|img)[^\]]*\].*\[/\\1\]#siU', ' ', $string);
		while ($string != ($newString = preg_replace('#\[([a-z0-9]+)(=[^\]]*)?\](.*)\[/\1\]#siU', '\3', $string)))
		{
			$string = $newString;
		}
		$string = str_replace('[*]', '', $string);
		if ($trimLoc = stripos($string, '[prebreak]'))
		{
			$prbreak = '';

			if (($breakLoc = stripos($string, '[/prebreak]', $trimLoc+10)) && ($length = $breakLoc - $trimLoc-10))
			{
				$link = XenForo_Link::buildPublicLink('full:threads', $post);
				$prbreak = " [url='".$link."']".substr($string, $trimLoc+10, $length).'[/url]...';
			}

			$string = substr($string, 0, $trimLoc).$prbreak;
		}
		$string = htmlspecialchars($string);
		$string = XenForo_Helper_String::wholeWordTrim($string, 250);
		return $string;
	}
	
	public function checkExcludeUserGroup()
	{
		$visitor = XenForo_Visitor::getInstance();
		$check = true;
		$excludeGroups = XenForo_Application::get('options')->BRME_excludeUserGroup;
		if($excludeGroups){
			$belongstogroups = $visitor['user_group_id'];
			if (!empty($visitor['secondary_group_ids']))
			{
				$belongstogroups .= ','.$visitor['secondary_group_ids'];
			}
			$groupcheck = explode(',',$belongstogroups);
			unset($belongstogroups);
			foreach ($groupcheck AS $groupId)
			{
				if (in_array($groupId, $excludeGroups))
				{
					$check = false;
					break;
				}
			}
		}
		return $check;
	}
	
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}

	
}