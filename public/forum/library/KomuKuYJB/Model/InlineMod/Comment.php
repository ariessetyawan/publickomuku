<?php /*98da64f69d847e2f596b0aeb702b7b181b7520a1*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_InlineMod_Comment extends XenForo_Model
{
    public $enableLogging = true;

    public function approveComments(array $commentIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($comments, $classifieds, $categories) = $this->getCommentsAndParentData($commentIds);

        if (empty($options['skipPermissions']) && !$this->canApproveCommentsData($comments, $classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateCommentsMessageState($comments, $classifieds, $categories, 'visible', 'moderated');
        return true;
    }

    public function canApproveCommentsData(array $comments, array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$comments)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $commentModel = $this->_getCommentModel();

        foreach ($comments as $comment)
        {
            $classified = $classifieds[$comment['classified_id']];
            $category = $categories[$classified['category_id']];

            if ($comment['message_state'] == 'moderated' && !$commentModel->canApproveComment($comment, $classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function unapproveComments(array $commentIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($comments, $classifieds, $categories) = $this->getCommentsAndParentData($commentIds);

        if (empty($options['skipPermissions']) && !$this->canUnapproveCommentsData($comments, $classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateCommentsMessageState($comments, $classifieds, $categories, 'moderated', 'visible');
        return true;
    }

    public function canUnapproveCommentsData(array $comments, array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$comments)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $commentModel = $this->_getCommentModel();

        foreach ($comments as $comment)
        {
            $classified = $classifieds[$comment['classified_id']];
            $category = $categories[$classified['category_id']];

            if ($comment['message_state'] == 'visible' && !$commentModel->canUnapproveComment($comment, $classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function undeleteComments(array $commentIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($comments, $classifieds, $categories) = $this->getCommentsAndParentData($commentIds);

        if (empty($options['skipPermissions']) && !$this->canUndeleteCommentsData($comments, $classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateCommentsMessageState($comments, $classifieds, $categories, 'visible', 'deleted');
        return true;
    }

    public function canUndeleteCommentsData(array $comments, array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$comments)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $commentModel = $this->_getCommentModel();

        foreach ($comments as $comment)
        {
            $classified = $classifieds[$comment['classified_id']];
            $category = $categories[$classified['category_id']];

            if ($comment['message_state'] == 'deleted' && !$commentModel->canUndeleteComment($comment, $classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function deleteComments(array $commentIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $options = array_merge(
            array(
                'deleteType' => '',
                'reason' => ''
            ), $options
        );

        if (!$options['deleteType'])
        {
            throw new XenForo_Exception('No deletion type specified.');
        }

        list ($comments, $classifieds, $categories) = $this->getCommentsAndParentData($commentIds);

        if (empty($options['skipPermissions']) && !$this->canDeleteCommentsData($comments, $options['deleteType'], $classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($comments as $comment)
        {
            /** @var KomuKuYJB_DataWriter_Comment $writer */
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
            $writer->setExistingData($comment);

            if (!$writer->get('comment_id'))
            {
                continue;
            }

            if ($options['deleteType'] == 'hard')
            {
                $writer->delete();
            }
            else
            {
                $writer->setExtraData($writer::DATA_DELETE_REASON, $options['reason']);
                $writer->set('message_state', 'deleted');
                $writer->save();
            }

            if ($this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction(
                    'classified_comment', $comment, 'delete_' . $options['deleteType'], array('reason' => $options['reason'])
                );
            }
        }

        return true;
    }

    public function canDeleteCommentsData(array $comments, $deleteType, array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$comments)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $commentModel = $this->_getCommentModel();

        foreach ($comments as $comment)
        {
            $classified = $classifieds[$comment['classified_id']];
            $category = $categories[$classified['category_id']];

            if ($comment['message_state'] != 'deleted' && !$commentModel->canDeleteComment($comment, $classified, $category, $deleteType, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function canDeleteComments(array $commentIds, $deleteType = 'soft', &$errorKey = '', array $viewingUser = null)
    {
        list ($comments, $classifieds, $categories) = $this->getCommentsAndParentData($commentIds);
        return $this->canDeleteCommentsData($comments, $deleteType, $classifieds, $categories, $errorKey, $viewingUser);
    }

    public function getCommentsAndParentData(array $commentIds)
    {
        $comments = $this->_getCommentModel()->getCommentsByIds($commentIds);
        $classifiedIds = array();

        foreach ($comments as $comment)
        {
            $classifiedIds[$comment['classified_id']] = true;
        }

        $classifieds = $this->_getClassifiedModel()->getClassifiedsByIds(array_keys($classifiedIds));
        $categoryIds = array();

        foreach ($classifieds as $classified)
        {
            $categoryIds[$classified['category_id']] = true;
        }

        $categories = $this->_getCategoryModel()->getCategoriesByIds(array_keys($categoryIds), array(
            'permissionCombinationId' => XenForo_Visitor::getInstance()->permission_combination_id
        ));

        $this->_getCategoryModel()->bulkSetCategoryPermCache(null, $categories, 'category_permission_cache');
        return array($comments, $classifieds, $categories);
    }

    protected function _updateCommentsMessageState(array $comments, array $classifieds, array $categories, $newState, $expectedOldState = false)
    {
        switch ($newState)
        {
            case 'visible':
                switch (strval($expectedOldState))
                {
                    case 'visible': return;
                    case 'moderated': $logAction = 'approve'; break;
                    case 'deleted': $logAction = 'undelete'; break;
                    default: $logAction = 'undelete'; break;
                }
                break;

            case 'moderated':
                switch (strval($expectedOldState))
                {
                    case 'visible': $logAction = 'unapprove'; break;
                    case 'moderated': return;
                    case 'deleted': $logAction = 'unapprove'; break;
                    default: $logAction = 'unapprove'; break;
                }
                break;

            case 'deleted':
                switch (strval($expectedOldState))
                {
                    case 'visible': $logAction = 'delete_soft'; break;
                    case 'moderated': $logAction = 'delete_soft'; break;
                    case 'deleted': return;
                    default: $logAction = 'delete_soft'; break;
                }
                break;

            default: return;
        }

        foreach ($comments as $comment)
        {
            if ($expectedOldState && $comment['message_state'] != $expectedOldState)
            {
                continue;
            }

            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
            $writer->setExistingData($comment);
            $writer->set('message_state', $newState);
            $writer->save();

            if ($this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction('classified_comment', $comment, $logAction);
            }
        }
    }

    /**
     * @return KomuKuYJB_Model_Comment
     */
    protected function _getCommentModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Comment');
    }

    /**
     * @return KomuKuYJB_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Classified');
    }

    /**
     * @return KomuKuYJB_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Category');
    }
}