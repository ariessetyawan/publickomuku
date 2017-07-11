<?php

class KomuKu_SimpleForms_Listener_Proxy_ModelForum extends XFCP_KomuKu_SimpleForms_Listener_Proxy_ModelForum
{
	public function prepareForumJoinOptions(array $fetchOptions)
	{
		$joinOptions = parent::prepareForumJoinOptions($fetchOptions);

		$joinOptions['selectFields'] .= ',
            `forum_form`.`form_id` AS `kmkform__form_id`,
			`forum_form`.`forum_form_id` AS `kmkform__forum_form_id`,
			`forum_form`.`button_text` AS `kmkform__button_text`,
			`forum_form`.`replace_button` AS `kmkform__replace_button`';
		$joinOptions['joinTables'] .= '
            LEFT JOIN `kmkform__forum_form` AS `forum_form` ON
                (`forum_form`.`forum_id` = `forum`.`node_id`)';
		
		return $joinOptions;
	}
	
	/**
	 * Prepares a collection of forum fetching related conditions into an SQL clause
	 *
	 * @param array $conditions List of conditions
	 * @param array $fetchOptions Modifiable set of fetch options (may have joins pushed on to it)
	 *
	 * @return string SQL clause (at least 1=1)
	 */
	public function prepareForumConditions(array $conditions, array &$fetchOptions)
	{
		$parentConditions = parent::prepareForumConditions($conditions, $fetchOptions);
		$db = $this->_getDb();
		
		$sqlConditions = array();
		if (!empty($conditions['title']))
		{
			if (is_array($conditions['title']))
			{
				$sqlConditions[] = 'node.title LIKE ' . XenForo_Db::quoteLike($conditions['title'][0], $conditions['title'][1], $db);
			}
			else
			{
				$sqlConditions[] = 'node.title LIKE ' . XenForo_Db::quoteLike($conditions['title'], 'lr', $db);
			}
		}	
		
		if ($parentConditions != '1=1')
		{
			return $parentConditions;
		}
		else 
		{
			return $this->getConditionsForClause($sqlConditions);
		}
	}
}