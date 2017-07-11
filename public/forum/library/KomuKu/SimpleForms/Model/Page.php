<?php

class KomuKu_SimpleForms_Model_Page extends XenForo_Model
{
	/**
	 * Gets a page by ID.
	 *
	 * @param string $pageId
	 *
	 * @return array|false
	 */
	public function getPageById($pageId)
	{
		return $this->_getDb()->fetchRow('
			SELECT `page`.*
			FROM `kmkform__page` AS `page`
			WHERE `page`.`page_id` = ?
		', $pageId);
	}
	
	public function getPageIdByFormId($formId)
	{
	    return $this->_getDb()->fetchOne("
	        SELECT `page_id`
	        FROM `kmkform__page`
	        WHERE `form_id` = ?
	    ", $formId);
	}
}