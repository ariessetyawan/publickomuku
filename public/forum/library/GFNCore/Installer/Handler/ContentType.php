<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */

use GFNCore_Rebuilder_ContentType as Rebuilder;

class GFNCore_Installer_Handler_ContentType extends GFNCore_Installer_Handler_Abstract
{
    const ALERT_HANDLER            = 'alert_handler_class';
    const ATTACHMENT_HANDLER       = 'attachment_handler_class';
    const EDIT_HISTORY_HANDLER     = 'edit_history_handler_class';
    const LIKE_HANDLER             = 'like_handler_class';
    const MODERATION_QUEUE_HANDLER = 'moderation_queue_handler_class';
    const MODERATOR_HANDLER        = 'moderator_handler_class';
    const MODERATOR_LOG_HANDLER    = 'moderator_log_handler_class';
    const NEWS_FEED_HANDLER        = 'news_feed_handler_class';
    const PERMISSION_HANDLER       = 'permission_handler_class';
    const REPORT_HANDLER           = 'report_handler_class';
    const SEARCH_HANDLER           = 'search_handler_class';
    const SITEMAP_HANDLER          = 'sitemap_handler_class';
    const SPAM_HANDLER             = 'spam_handler_class';
    const STATS_HANDLER            = 'stats_handler_class';
    const TAG_HANDLER              = 'tag_handler_class';
    const WARNING_HANDLER          = 'warning_handler_class';

    protected static $_contentTableMap = array(
        self::ALERT_HANDLER => 'kmk_user_alert',
        self::ATTACHMENT_HANDLER => 'kmk_attachment',
        self::EDIT_HISTORY_HANDLER => 'kmk_edit_history',
        self::LIKE_HANDLER => 'kmk_liked_content',
        self::MODERATION_QUEUE_HANDLER => 'kmk_moderation_queue',
        self::MODERATOR_HANDLER => null,
        self::MODERATOR_LOG_HANDLER => 'kmk_moderator_log',
        self::NEWS_FEED_HANDLER => 'kmk_news_feed',
        self::PERMISSION_HANDLER => null,
        self::REPORT_HANDLER => 'kmk_report',
        self::SEARCH_HANDLER => 'kmk_search_index',
        self::SITEMAP_HANDLER => null,
        self::SPAM_HANDLER => null,
        self::STATS_HANDLER => null,
        self::WARNING_HANDLER => null
    );

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    protected $_addOnId;

    public function __construct($addOnId)
    {
        $this->_addOnId = $addOnId;
        $this->_db = XenForo_Application::getDb();
        Rebuilder::init();
    }

    public function add($contentType, $fieldName, $fieldValue)
    {
        Rebuilder::get()->add($contentType, $this->_addOnId);

        $this->_db->query(
            'INSERT IGNORE INTO kmk_content_type_field
            (content_type, field_name, field_value)
            VALUES
            (?, ?, ?)', array($contentType, $fieldName, $fieldValue)
        );
    }

    public function delete($contentType, $fieldNames = null)
    {
        $db = $this->_db;

        $db->delete('kmk_content_type', 'content_type = ' . $db->quote($contentType));

        if ($fieldNames === null)
        {
            $fieldNames = $db->fetchCol('SELECT field_name FROM kmk_content_type_field WHERE content_type = ?', $contentType);
            $db->delete('kmk_content_type_field', 'content_type = ' . $db->quote($contentType));
        }
        elseif (is_array($fieldNames))
        {
            $db->delete('kmk_content_type_field', 'content_type = ' . $db->quote($contentType) . ' AND field_name IN (' . $db->quote($fieldNames) . ')');
        }
        else
        {
            $db->delete('kmk_content_type_field', 'content_type = ' . $db->quote($contentType) . ' AND field_name = ' . $db->quote($fieldNames));
            $fieldNames = array($fieldNames);
        }

        $contentTypesGrouped = array();

        foreach ($fieldNames as $field)
        {
            $contentTypesGrouped[$field][] = $contentType;
        }

        $this->_clearContentTables($contentTypesGrouped);
        Rebuilder::get()->add($contentType, $this->_addOnId);
    }

    public function deleteAll()
    {
        $db = $this->_db;
        Rebuilder::get()->clear($this->_addOnId);

        $contentTypeFields = $db->fetchAll(
            'SELECT field.content_type, field.field_name
            FROM kmk_content_type_field AS field
            INNER JOIN kmk_content_type AS content
              ON (content.content_type = field.content_type)
            WHERE content.addon_id = ?
            ORDER BY field.content_type', $this->_addOnId
        );

        if (!$contentTypeFields)
        {
            return;
        }

        $contentTypes = array();
        $contentTypesGrouped = array();

        foreach ($contentTypeFields as $field)
        {
            $contentTypes[$field['content_type']] = true;
            $contentTypesGrouped[$field['field_name']][] = $field['content_type'];
        }

        $this->_clearContentTables($contentTypesGrouped);
        $db->delete('kmk_content_type_field', 'content_type IN (' . $db->quote(array_keys($contentTypes)) . ')');
        $db->delete('kmk_content_type', 'addon_id = ' . $db->quote($this->_addOnId));
    }

    protected function _clearContentTables(array $contentTypesGrouped)
    {
        $db = $this->_db;

        foreach ($contentTypesGrouped as $fieldName => $contentTypes)
        {
            if (isset(self::$_contentTableMap[$fieldName]))
            {
                switch ($fieldName)
                {
                    case self::ATTACHMENT_HANDLER:
                        $db->update('kmk_attachment', array('unassociated' => 1), 'content_type IN (' . $db->quote($contentTypes) . ')');
                        break;

                    default:
                        $db->delete(self::$_contentTableMap[$fieldName], 'content_type IN (' . $db->quote($contentTypes) . ')');
                        break;
                }
            }
        }
    }
}