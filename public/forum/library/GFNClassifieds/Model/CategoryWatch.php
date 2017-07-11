<?php /*f3b3ecde42ea689b8aad3aa5d460fb74d4af0640*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_CategoryWatch extends GFNClassifieds_Model
{
    protected static $_preventDoubleNotify = array();

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

        $users = $this->getUsersWatchingCategory($category);
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

            if ($user['send_email'] && !in_array($user['user_id'], $noEmail) && $user['email'] && $user['user_state'] == 'valid')
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

            if ($user['send_alert'] && !in_array($user['user_id'], $noAlerts))
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

    public function getUserCategoryWatchByCategoryId($userId, $categoryId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_category_watch
            WHERE user_id = ?
            AND category_id = ?', array($userId, $categoryId)
        );
    }

    public function getUserCategoryWatchByCategoryIds($userId, array $categoryIds)
    {
        if (!$categoryIds)
        {
            return array();
        }

        return $this->fetchAllKeyed(
            'SELECT *
            FROM kmk_classifieds_category_watch
            WHERE user_id = ?
            AND category_id IN (' . $this->_getDb()->quote($categoryIds) . ')', 'category_id', $userId
        );
    }

    public function getUserCategoryWatchByUser($userId)
    {
        return $this->fetchAllKeyed(
            'SELECT *
            FROM kmk_classifieds_category_watch
            WHERE user_id = ?', 'category_id', $userId
        );
    }

    public function getUsersWatchingCategory(array $category)
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

        $breadcrumb = XenForo_Helper_Php::safeUnserialize($category['category_breadcrumb']);
        $categoryIds = array_keys($breadcrumb);
        $categoryIds[] = $category['category_id'];

        return $this->fetchAllKeyed(
            'SELECT user.*, user_option.*, user_profile.*,
            category_watch.category_id AS watch_category_id,
            category_watch.notify_on, category_watch.send_alert, category_watch.send_email,
            permission_combination.cache_value AS global_permission_cache,
            permission_cache_content.cache_value AS category_permission_cache
            FROM kmk_classifieds_category_watch AS category_watch
            INNER JOIN kmk_user AS user
              ON (user.user_id = category_watch.user_id AND user.user_state = \'valid\' AND user.is_banned = 0' . $activeLimit . ')
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
            WHERE category_watch.category_id IN (' . $this->_getDb()->quote($categoryIds) . ')
            AND (category_watch.include_children <> 0 OR category_watch.category_id = ?)
            AND (category_watch.send_alert <> 0 OR category_watch.send_email <> 0)', 'user_id', array($category['category_id'], $category['category_id'])
        );
    }

    public function setCategoryWatchState($userId, $categoryId, $notifyOn = null, $sendAlert = null, $sendEmail = null, $includeChildren = null)
    {
        if (!$userId)
        {
            return false;
        }

        $categoryWatch = $this->getUserCategoryWatchByCategoryId($userId, $categoryId);

        if ($notifyOn === 'delete')
        {
            if ($categoryWatch)
            {
                $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_CategoryWatch');
                $writer->setExistingData($categoryWatch, true);
                $writer->delete();
            }

            return true;
        }

        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_CategoryWatch');

        if ($categoryWatch)
        {
            $writer->setExistingData($categoryWatch, true);
        }
        else
        {
            $writer->set('user_id', $userId);
            $writer->set('category_id', $categoryId);
        }
        if ($notifyOn !== null)
        {
            $writer->set('notify_on', $notifyOn);
        }
        if ($sendAlert !== null)
        {
            $writer->set('send_alert', $sendAlert ? 1 : 0);
        }
        if ($sendEmail !== null)
        {
            $writer->set('send_email', $sendEmail ? 1 : 0);
        }
        if ($includeChildren !== null)
        {
            $writer->set('include_children', $includeChildren ? 1 : 0);
        }

        $writer->save();
        return true;
    }

    public function setCategoryWatchStateForAll($userId, $state)
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
                return $db->update('kmk_classifieds_category_watch',
                    array('send_email' => 1),
                    "user_id = " . $db->quote($userId)
                );

            case 'watch_no_email':
                return $db->update('kmk_classifieds_category_watch',
                    array('send_email' => 0),
                    "user_id = " . $db->quote($userId)
                );

            case 'watch_alert':
                return $db->update('kmk_classifieds_category_watch',
                    array('send_alert' => 1),
                    "user_id = " . $db->quote($userId)
                );

            case 'watch_no_alert':
                return $db->update('kmk_classifieds_category_watch',
                    array('send_alert' => 0),
                    "user_id = " . $db->quote($userId)
                );

            case 'watch_include_children':
                return $db->update('kmk_classifieds_category_watch',
                    array('include_children' => 1),
                    "user_id = " . $db->quote($userId)
                );

            case 'watch_no_include_children':
                return $db->update('kmk_classifieds_category_watch',
                    array('include_children' => 0),
                    "user_id = " . $db->quote($userId)
                );

            case '':
                return $db->delete('kmk_classifieds_category_watch', "user_id = " . $db->quote($userId));

            default:
                return false;
        }
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