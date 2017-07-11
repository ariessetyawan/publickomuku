<?php /*e4ab7418ce55c129c46e933ca0b7d3bd94ad9d84*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_Comment extends XenForo_DataWriter
{
    const DATA_DELETE_REASON            = 'deleteReason';

    const OPTION_MAX_COMMENT_LENGTH     = 'maxCommentLength';
    const OPTION_MAX_TAGGED_USERS       = 'maxTaggedUsers';
    const OPTION_SET_IP_ADDRESS         = 'setIpAddress';
    const OPTION_PUBLISH_FEED           = 'publishFeed';
    const OPTION_DELETE_CHILDREN        = 'deleteChildren';

    protected $_taggedUsers = array();

    /**
     * @var GFNClassifieds_Eloquent_Classified
     */
    protected $_classified;

    /**
     * @var GFNClassifieds_Eloquent_Comment
     */
    protected $_parentComment;

    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_MAX_COMMENT_LENGTH => GFNClassifieds_Options::getInstance()->get('maxCommentLength'),
            self::OPTION_MAX_TAGGED_USERS => XenForo_Visitor::getInstance()->hasPermission('general', 'maxTaggedUsers'),
            self::OPTION_SET_IP_ADDRESS => true,
            self::OPTION_PUBLISH_FEED => true,
            self::OPTION_DELETE_CHILDREN => true
        );
    }

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_comment' => array(
                'comment_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'username' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'message' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'requiredError' => 'please_enter_valid_message'
                ),
                'ip_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'post_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => XenForo_Application::$time
                ),
                'likes' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'like_users' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'message_state' => array(
                    'type' => self::TYPE_STRING,
                    'allowedValues' => array('visible', 'moderated', 'deleted'),
                    'default' => 'visible'
                ),
                'reply_comment_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'reply_parent_comment_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'reply_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'first_reply_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'warning_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'warning_message' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $commentId = $this->_getExistingPrimaryKey($data);
        if (!$commentId)
        {
            return false;
        }

        $comment = $this->_getCommentModel()->getCommentById($commentId);
        if (!$comment)
        {
            return false;
        }

        return array('kmk_classifieds_comment' => $comment);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'comment_id = ' . $this->_db->quote($this->getExisting('comment_id'));
    }

    protected function _preSave()
    {
        if ($this->get('message_state') === null)
        {
            $this->set('message_state', 'visible');
        }

        $length = utf8_strlen($this->get('message'));
        $maxLength = $this->getOption(self::OPTION_MAX_COMMENT_LENGTH);

        if ($length > $maxLength)
        {
            $this->error(new XenForo_Phrase('please_enter_comment_with_no_more_than_x_characters', array('length' => $maxLength, 'over' => $length - $maxLength)), 'message');
        }

        if ($this->isChanged('reply_comment_id'))
        {
            if ($this->get('reply_comment_id'))
            {
                $this->set('reply_parent_comment_id', $this->_db->fetchOne(
                    'SELECT IF(reply_parent_comment_id > 0, reply_parent_comment_id, comment_id)
                    FROM kmk_classifieds_comment
                    WHERE comment_id = ?', $this->get('reply_comment_id')
                ));
            }
            else
            {
                $this->set('reply_parent_comment_id', 0);
            }
        }

        /** @var XenForo_Model_UserTagging $tagModel */
        $tagModel = $this->getModelFromCache('XenForo_Model_UserTagging');
        $this->_taggedUsers = $tagModel->getTaggedUsersInMessage($this->get('message'), $newMessage);
        $this->set('message', $newMessage);
    }

    protected function _postSave()
    {
        $postSaveChanges = array();

        if ($this->isInsert() && $this->getOption(self::OPTION_SET_IP_ADDRESS) && !$this->get('ip_id'))
        {
            $postSaveChanges['ip_id'] = XenForo_Model_Ip::log($this->get('user_id'), 'classified_comment', $this->get('comment_id'), 'insert');
        }

        if ($this->isInsert() && $this->getOption(self::OPTION_PUBLISH_FEED))
        {
            GFNClassifieds_Model_NewsFeed::publish('comment', $this->getMergedData());
        }

        $removed = false;
        if ($this->isChanged('message_state'))
        {
            if ($this->isUpdate() && $this->getExisting('message_state') == 'visible')
            {
                $removed = true;
                $this->getClassified()->writer()->commentRemoved($this);

                if ($this->get('reply_parent_comment_id'))
                {
                    $this->getParentComment()->writer()->replyRemoved($this);
                }
            }

            $this->_updateDeletionLog();
            $this->_updateModerationQueue();
        }

        if (!$removed)
        {
            $this->getClassified()->writer()->commentUpdate($this);

            if ($this->get('reply_parent_comment_id'))
            {
                $this->getParentComment()->writer()->replyUpdate($this);
            }
        }

        if ($postSaveChanges)
        {
            $this->bulkSet($postSaveChanges, array('setAfterPreSave' => true));
            $this->_db->update('kmk_classifieds_comment', $postSaveChanges, 'comment_id = ' . $this->_db->quote($this->get('comment_id')));
        }
    }

    protected function _postSaveAfterTransaction()
    {
        if ($this->isInsert())
        {
            $classified = $this->getClassified();
            $comment = $this->getMergedData();

            if ($this->get('message_state') == 'visible')
            {
                $notified = $this->_getClassifiedWatchModel()->sendNotificationToWatchUsersOnComment(
                    $comment, $classified->toArray()
                );

                $this->_getCommentModel()->alertTaggedMembers(
                    $comment, $this->_taggedUsers,
                    empty($notified['alerted']) ? array() : $notified['alerted'],
                    $this->get('reply_comment_id') <> 0
                );
            }
        }

        $this->getClassified()->save();
    }

    protected function _postDelete()
    {
        if ($this->getExisting('message_state') == 'visible')
        {
            $this->getClassified()->writer()->commentRemoved($this);

            if ($this->get('reply_parent_comment_id'))
            {
                $this->getParentComment()->writer()->replyRemoved($this);
            }
        }

        if ($this->getOption(self::OPTION_DELETE_CHILDREN))
        {
            $this->_getCommentModel()->removeChildComments($this->getExisting('comment_id'));
        }

        $this->_updateDeletionLog(true);
        $this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
            'classified_comment', $this->get('comment_id')
        );
    }

    protected function _updateDeletionLog($hardDelete = false)
    {
        /** @var XenForo_Model_DeletionLog $model */
        $model = $this->getModelFromCache('XenForo_Model_DeletionLog');

        if ($hardDelete || ($this->isChanged('message_state') && $this->getExisting('message_state') == 'deleted'))
        {
            $model->removeDeletionLog('classified_comment', $this->get('comment_id'));
        }

        if ($this->isChanged('message_state') && $this->get('message_state') == 'deleted')
        {
            $reason = $this->getExtraData(self::DATA_DELETE_REASON);
            $model->logDeletion('classified_comment', $this->get('comment_id'), $reason);
        }
    }

    protected function _updateModerationQueue()
    {
        if (!$this->isChanged('message_state'))
        {
            return;
        }

        /** @var XenForo_Model_ModerationQueue $model */
        $model = $this->getModelFromCache('XenForo_Model_ModerationQueue');

        if ($this->get('message_state') == 'moderated')
        {
            $model->insertIntoModerationQueue('classified_comment', $this->get('comment_id'), $this->get('post_date'));
        }
        elseif ($this->getExisting('message_state') == 'moderated')
        {
            $model->deleteFromModerationQueue('classified_comment', $this->get('comment_id'));
        }
    }

    public function getClassified()
    {
        if (!$this->_classified)
        {
            if (!$this->get('classified_id'))
            {
                throw new XenForo_Exception(new XenForo_Phrase('classified_id_needs_to_be_set_first'));
            }

            $this->_classified = new GFNClassifieds_Eloquent_Classified($this->get('classified_id'));
        }

        return $this->_classified;
    }

    public function getParentComment()
    {
        if (!$this->_parentComment)
        {
            if (!$this->get('reply_parent_comment_id'))
            {
                throw new XenForo_Exception(new XenForo_Phrase('parent_comment_id_needs_to_be_set_first'));
            }

            $this->_parentComment = new GFNClassifieds_Eloquent_Comment($this->get('reply_parent_comment_id'));
        }

        return $this->_parentComment;
    }

    public function replyRemoved(GFNClassifieds_DataWriter_Comment $reply)
    {
        if ($reply->getExisting('message_state') != 'visible')
        {
            return;
        }

        $this->updateReplyCount(-1);
    }

    public function replyUpdate(GFNClassifieds_DataWriter_Comment $reply)
    {
        if ($reply->get('message_state') != 'visible')
        {
            return;
        }

        if ($reply->isChanged('message_state'))
        {
            $this->updateReplyCount(1);
        }

        if (!$this->get('first_reply_date'))
        {
            $this->set('first_reply_date', $reply->get('post_date'));
        }
    }

    public function updateReplyCount($adjust = null)
    {
        if ($adjust === null)
        {
            $this->set('reply_count', $this->_db->fetchOne(
                'SELECT COUNT(*)
                FROM kmk_classifieds_comment
                WHERE reply_parent_comment_id = ?
                AND message_state = ?', array($this->get('comment_id'), 'visible')
            ));
        }
        else
        {
            $this->set('reply_count', $this->get('reply_count') + $adjust);
        }
    }

    /**
     * @return GFNClassifieds_Model_Comment
     */
    protected function _getCommentModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Comment');
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Classified');
    }

    /**
     * @return GFNClassifieds_Model_ClassifiedWatch
     */
    protected function _getClassifiedWatchModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_ClassifiedWatch');
    }
}