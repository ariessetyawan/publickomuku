<?php /*e92ea5fed28859f98744400aed15e7b1b3b84fb4*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_ClassifiedWatch extends GFNClassifieds_Model
{
    public function sendNotificationToWatchUsersOnCreate(array $classified, array $noAlerts = array(), array $noEmail = array())
    {
        if ($classified['classified_state'] != 'visible')
        {
            return array();
        }

        $classifiedModel = $this->_getClassifiedModel();
        $userModel = $this->_getUserModel();

        if (XenForo_Application::get('options')->emailWatchedThreadIncludeMessage)
        {
            $parseBbCode = true;
            $emailTemplate = 'watched_category_classified_messagetext';
        }
        else
        {
            $parseBbCode = false;
            $emailTemplate = 'watched_category_classified';
        }

        $classifiedUser = $userModel->getUserById($classified['user_id']);
        if (!$classifiedUser)
        {
            $classifiedUser = $userModel->getVisitingGuestUser();
        }

        if (!empty($classified['category_breadcrumb']))
        {
            $category = $classified;
        }
        else
        {
            $category = $this->_getCategoryModel()->getCategoryById($classified['category_id']);
            if (!$category)
            {
                return array();
            }
        }

        $alerted = array();
        $emailed = array();

        $users = $this->getUsersWatchingClassified($classified['classified_id'], $category['category_id']);
        foreach ($users as $user)
        {
            if ($user['user_id'] == $classified['user_id'])
            {
                continue;
            }

            $user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);
            $categoryPermissions = XenForo_Permission::unserializePermissions($user['category_permission_cache']);

            if (!$classifiedModel->canViewClassifiedAndContainer($classified, $category, $null, $user, $categoryPermissions))
            {
                continue;
            }

            if ($user['email_subscribe'] && !in_array($user['user_id'], $noEmail) && $user['email'] && $user['user_state'] == 'valid')
            {
                if (!isset($classified['descriptionText']) && $parseBbCode)
                {
                    $bbCodeParserText = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Text'));
                    $classified['descriptionText'] = new XenForo_BbCode_TextWrapper($classified['description'], $bbCodeParserText);

                    $bbCodeParserHtml = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('HtmlEmail'));
                    $classified['descriptionHtml'] = new XenForo_BbCode_TextWrapper($classified['description'], $bbCodeParserHtml);
                }

                if (!isset($classified['titleCensored']))
                {
                    $classified['titleCensored'] = XenForo_Helper_String::censorString($classified['title']);
                }

                $user['email_confirm_key'] = $userModel->getUserEmailConfirmKey($user);

                $mail = XenForo_Mail::create($emailTemplate, array(
                    'classified' => $classified,
                    'category' => $category,
                    'receiver' => $user,
                    'classifiedUser' => $classifiedUser
                ), $user['language_id']);

                $mail->enableAllLanguagePreCache();
                $mail->queue($user['email'], $user['username']);

                $emailed[] = $user['user_id'];
                $noEmail[] = $user['user_id'];
            }

            if (!in_array($user['user_id'], $noAlerts))
            {
                XenForo_Model_Alert::alert(
                    $user['user_id'], $classified['user_id'], $classified['username'],
                    'classified', $classified['classified_id'], 'insert'
                );

                $alerted[] = $user['user_id'];
                $noAlerts[] = $user['user_id'];
            }
        }

        return array(
            'emailed' => $emailed,
            'alerted' => $alerted
        );
    }

    public function sendNotificationToWatchUsersOnComment(array $comment, array $classified, array $noAlerts = array(), array $noEmail = array())
    {
        if ($comment['message_state'] != 'visible' || $classified['classified_state'] != 'visible')
        {
            return array();
        }

        $commentModel = $this->_getCommentModel();
        $userModel = $this->_getUserModel();

        if (XenForo_Application::get('options')->emailWatchedThreadIncludeMessage)
        {
            $parseBbCode = true;
            $emailTemplate = 'watched_classified_comment_messagetext';
        }
        else
        {
            $parseBbCode = false;
            $emailTemplate = 'watched_classified_comment';
        }

        $classifiedUser = $userModel->getUserById($classified['user_id']);
        if (!$classifiedUser)
        {
            $classifiedUser = $userModel->getVisitingGuestUser();
        }

        $commentUser = $userModel->getUserById($comment['user_id']);
        if (!$commentUser)
        {
            $commentUser = $userModel->getVisitingGuestUser();
        }

        if (!empty($classified['category_breadcrumb']))
        {
            $category = $classified;
        }
        else
        {
            $category = $this->_getCategoryModel()->getCategoryById($classified['category_id']);
            if (!$category)
            {
                return array();
            }
        }

        $alerted = array();
        $emailed = array();

        $users = $this->getUsersWatchingClassified($classified['classified_id'], $classified['category_id']);
        foreach ($users as $user)
        {
            if ($comment['user_id'] == $user['user_id'])
            {
                continue;
            }

            $user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);
            $categoryPermissions = XenForo_Permission::unserializePermissions($user['category_permission_cache']);

            if (!$commentModel->canViewCommentAndContainer($comment, $classified, $category, $null, $user, $categoryPermissions))
            {
                continue;
            }

            if ($user['email_subscribe'] && !in_array($user['user_id'], $noEmail) && $user['email'] && $user['user_state'] == 'valid')
            {
                if (!isset($comment['messageText']) && $parseBbCode)
                {
                    $bbCodeParserText = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Text'));
                    $comment['messageText'] = new XenForo_BbCode_TextWrapper($comment['message'], $bbCodeParserText);

                    $bbCodeParserHtml = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('HtmlEmail'));
                    $comment['messageHtml'] = new XenForo_BbCode_TextWrapper($comment['message'], $bbCodeParserHtml);
                }

                if (!isset($classified['titleCensored']))
                {
                    $classified['titleCensored'] = XenForo_Helper_String::censorString($classified['title']);
                }

                if (!isset($comment['classified_title']))
                {
                    $comment['classified_title'] = $classified['titleCensored'];
                }

                $user['email_confirm_key'] = $userModel->getUserEmailConfirmKey($user);

                $mail = XenForo_Mail::create($emailTemplate, array(
                    'comment' => $comment,
                    'classified' => $classified,
                    'category' => $category,
                    'receiver' => $user,
                    'classifiedUser' => $classifiedUser,
                    'commentUser' => $commentUser
                ), $user['language_id']);

                $mail->enableAllLanguagePreCache();
                $mail->queue($user['email'], $user['username']);

                $emailed[] = $user['user_id'];
                $noEmail[] = $user['user_id'];
            }

            if (!in_array($user['user_id'], $noAlerts))
            {
                if (XenForo_Model_Alert::userReceivesAlert($user, 'classified', 'comment'))
                {
                    XenForo_Model_Alert::alert(
                        $user['user_id'], $comment['user_id'], $comment['username'],
                        'classified_comment', $comment['comment_id'], 'insert'
                    );

                    $alerted[] = $user['user_id'];
                    $noAlerts[] = $user['user_id'];
                }
            }
        }

        return array(
            'emailed' => $emailed,
            'alerted' => $alerted
        );
    }

    public function getUserClassifiedWatchByClassifiedId($userId, $classifiedId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_classified_watch
            WHERE user_id = ?
            AND classified_id = ?', array($userId, $classifiedId)
        );
    }

    public function getUserClassifiedWatchByClassifiedIds($userId, array $classifiedIds)
    {
        if (!$classifiedIds)
        {
            return array();
        }

        return $this->fetchAllKeyed(
            'SELECT *
            FROM kmk_classifieds_classified_watch
            WHERE user_id = ?
            AND classified_id IN (' . $this->_getDb()->quote($classifiedIds) . ')', 'classified_id', $userId
        );
    }

    public function getUsersWatchingClassified($classifiedId, $categoryId)
    {
        $activeLimitOption = XenForo_Application::getOptions()->watchAlertActiveOnly;
        if ($activeLimitOption && !empty($activeLimitOption['enabled']))
        {
            $activeLimit = ' AND user.last_activity >= ' . (XenForo_Application::$time - 86400 * $activeLimitOption['days']);
        }
        else
        {
            $activeLimit = '';
        }

        return $this->fetchAllKeyed(
            'SELECT user.*, user_option.*, user_profile.*,
            permission_combination.cache_value AS global_permission_cache,
            permission_cache_content.cache_value AS category_permission_cache,
            classified_watch.email_subscribe
            FROM kmk_classifieds_classified_watch AS classified_watch
            INNER JOIN kmk_user AS user
              ON (user.user_id = classified_watch.user_id AND user.user_state = \'valid\' AND user.is_banned = 0' . $activeLimit . ')
            INNER JOIN kmk_user_option AS user_option ON (user.user_id = user_option.user_id)
            INNER JOIN kmk_user_profile AS user_profile ON (user.user_id = user_profile.user_id)
            INNER JOIN kmk_permission_combination AS permission_combination
                ON (user.permission_combination_id = permission_combination.permission_combination_id)
            INNER JOIN kmk_permission_cache_content AS permission_cache_content
              ON (
                user.permission_combination_id = permission_cache_content.permission_combination_id
                AND permission_cache_content.content_type = \'classified_category\'
                AND permission_cache_content.content_id = ?
              )
            WHERE classified_watch.classified_id = ?', 'user_id', array($categoryId, $classifiedId)
        );
    }

    public function getClassifiedsWatchedByUser($userId, array $fetchOptions = array())
    {
        $joinOptions = $this->_getClassifiedModel()->prepareClassifiedFetchOptions($fetchOptions);
        $limitOption = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT classified.*, classified_watch.email_subscribe
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_classified_watch AS classified_watch
            INNER JOIN kmk_classifieds_classified AS classified ON (classified.classified_id = classified_watch.classified_id)
            ' . $joinOptions['joinTables'] . '
            WHERE classified_watch.user_id = ?
            AND classified.classified_state = \'visible\'
            ORDER BY classified.classified_date DESC', $limitOption['limit'], $limitOption['offset']
        ), 'classified_id', $userId);
    }

    public function countClassifiedsWatchedByUser($userId)
    {
        return $this->_getDb()->fetchOne(
            'SELECT COUNT(*)
            FROM kmk_classifieds_classified_watch AS classified_watch
            INNER JOIN kmk_classifieds_classified AS classified ON (classified.classified_id = classified_watch.classified_id)
            WHERE classified_watch.user_id = ?
            AND classified.classified_state = \'visible\'', $userId
        );
    }

    public function setClassifiedWatchState($userId, $classifiedId, $state)
    {
        if (!$userId)
        {
            return false;
        }

        $classifiedWatch = $this->getUserClassifiedWatchByClassifiedId($userId, $classifiedId);

        switch ($state)
        {
            case 'watch_email':
            case 'watch_no_email':
                $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_ClassifiedWatch', XenForo_DataWriter::ERROR_SILENT);

                if ($classifiedWatch)
                {
                    $writer->setExistingData($classifiedWatch, true);
                }
                else
                {
                    $writer->set('user_id', $userId);
                    $writer->set('classified_id', $classifiedId);
                }

                $writer->set('email_subscribe', ($state == 'watch_email' ? 1 : 0));
                $writer->save();
                return true;

            case '':
                if ($classifiedWatch)
                {
                    $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_ClassifiedWatch', XenForo_DataWriter::ERROR_SILENT);
                    $writer->setExistingData($classifiedWatch, true);
                    $writer->delete();
                }
                return true;

            default:
                return false;
        }
    }

    public function setClassifiedWatchSateForAll($userId, $state)
    {
        $userId = intval($userId);
        if (!$userId)
        {
            return false;
        }

        $db = $this->_getDb();

        switch ($state)
        {
            case 'watch_email':
                return $db->update('kmk_classifieds_classified_watch',
                    array('email_subscribe' => 1),
                    "user_id = " . $db->quote($userId)
                );

            case 'watch_no_email':
                return $db->update('kmk_classifieds_classified_watch',
                    array('email_subscribe' => 0),
                    "user_id = " . $db->quote($userId)
                );

            case '':
                return $db->delete('kmk_classifieds_classified_watch', "user_id = " . $db->quote($userId));

            default:
                return false;
        }
    }

    public function setClassifiedWatchStateWithUserDefault($userId, $classifiedId, $state)
    {
        if (!$userId)
        {
            return false;
        }

        $resourceWatch = $this->getUserClassifiedWatchByClassifiedId($userId, $classifiedId);
        if ($resourceWatch)
        {
            return true;
        }

        switch ($state)
        {
            case 'watch_email':
            case 'watch_no_email':
                $dw = XenForo_DataWriter::create('GFNClassifieds_DataWriter_ClassifiedWatch');
                $dw->set('user_id', $userId);
                $dw->set('classified_id', $classifiedId);
                $dw->set('email_subscribe', ($state == 'watch_email' ? 1 : 0));
                $dw->save();
                return true;

            default:
                return false;
        }
    }

    public function setVisitorClassifiedWatchStateFromInput($classifiedId, array $input)
    {
        $visitor = XenForo_Visitor::getInstance();

        if (!$visitor['user_id'])
        {
            return false;
        }

        if ($input['watch_classified_state'])
        {
            if ($input['watch_classified'])
            {
                $watchState = $input['watch_classified_email'] ? 'watch_email' : 'watch_no_email';
            }
            else
            {
                $watchState = '';
            }

            return $this->setClassifiedWatchState($visitor['user_id'], $classifiedId, $watchState);
        }
        else
        {
            return $this->setClassifiedWatchStateWithUserDefault($visitor['user_id'], $classifiedId, $visitor['default_classified_watch_state']);
        }
    }

    public function getClassifiedWatchStateForVisitor($classifiedId = false, $useDefaultIfNotWatching = true)
    {
        $visitor = XenForo_Visitor::getInstance();
        if (!$visitor['user_id'])
        {
            return '';
        }

        if ($classifiedId)
        {
            $classifiedWatch = $this->getUserClassifiedWatchByClassifiedId($visitor['user_id'], $classifiedId);
        }
        else
        {
            $classifiedWatch = false;
        }

        if ($classifiedWatch)
        {
            return ($classifiedWatch['email_subscribe'] ? 'watch_email' : 'watch_no_email');
        }
        elseif ($useDefaultIfNotWatching)
        {
            return $visitor['default_classified_watch_state'];
        }
        else
        {
            return '';
        }
    }

    /**
     * @return GFNClassifieds_Model_Comment
     */
    public function _getCommentModel()
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
     * @return XenForo_Model_User
     */
    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }

    /**
     * @return GFNClassifieds_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Category');
    }

    /**
     * @return XenForo_Model_Alert
     */
    protected function _getAlertModel()
    {
        return $this->getModelFromCache('XenForo_Model_Alert');
    }
}