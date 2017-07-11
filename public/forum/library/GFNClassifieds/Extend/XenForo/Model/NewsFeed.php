<?php /*c00b6ef4f9470a53cfd21d8084a46a97c4b57bc0*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_Model_NewsFeed extends XFCP_GFNClassifieds_Extend_XenForo_Model_NewsFeed
{
    public function getNewsFeed(array $conditions = array(), $fetchOlderThanId = 0, array $viewingUser = null)
    {
        if (empty($conditions['classifiedOnly']))
        {
            return parent::getNewsFeed($conditions, $fetchOlderThanId, $viewingUser);
        }

        $this->standardizeViewingUserReference($viewingUser);

        if ($fetchOlderThanId)
        {
            $conditions['news_feed_id'] = array('<', $fetchOlderThanId);
        }

        $newsFeed = $this->getNewsFeedItemsForClassified($conditions, $viewingUser);

        $newsFeed = $this->fillOutNewsFeedItems($newsFeed, $viewingUser);
        $this->_cacheHandlersForNewsFeed($newsFeed);

        return array(
            'newsFeed' => $newsFeed,
            'newsFeedHandlers' => $this->_handlerCache,
            'oldestItemId' => $this->getOldestNewsFeedIdFromArray($newsFeed),
            'feedEnds' => (sizeof($newsFeed) == 0) // permissions make this hard to calculate
        );
    }

    public function getNewsFeedItemsForClassified(array $conditions = array(), array $viewingUser, $maxItems = null)
    {
        $db = $this->_getDb();
        $sqlConditions = array();

        if (isset($conditions['news_feed_id']) && is_array($conditions['news_feed_id']))
        {
            list($operator, $newsFeedId) = $conditions['news_feed_id'];

            $this->assertValidCutOffOperator($operator);
            $sqlConditions[] = "news_feed.news_feed_id $operator " . $db->quote($newsFeedId);
        }

        if (isset($conditions['user_id']))
        {
            if (is_array($conditions['user_id']))
            {
                $sqlConditions[] = 'news_feed.user_id IN (' . $db->quote($conditions['user_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'news_feed.user_id = ' . $db->quote($conditions['user_id']);
            }
            $forceIndex = '';
        }
        else
        {
            $forceIndex = 'FORCE INDEX (event_date)';

            if ($viewingUser['user_id'] && !empty($viewingUser['ignored']))
            {
                $ignored = XenForo_Helper_Php::safeUnserialize($viewingUser['ignored']);
                if ($ignored)
                {
                    $ignored = array_map('intval', array_keys($ignored));
                    $sqlConditions[] = 'news_feed.user_id NOT IN (' . $db->quote($ignored) . ')';
                }
            }

            $sqlConditions[] = "user.user_state IN ('valid', 'email_confirm_edit')";
            $sqlConditions[] = "user.is_banned = 0";
        }

        $whereClause = $this->getConditionsForClause($sqlConditions);

        if ($maxItems === null)
        {
            $maxItems = XenForo_Application::get('options')->newsFeedMaxItems;
        }

        $viewingUserIdQuoted = $db->quote($viewingUser['user_id']);
        $isRegistered = ($viewingUser['user_id'] > 0 ? 1 : 0);
        $bypassPrivacy = $this->getModelFromCache('XenForo_Model_User')->canBypassUserPrivacy($errorPhraseKey, $viewingUser);

        // TODO: restore user_id = 0 announcements functionality down the line
        return $this->fetchAllKeyed($this->limitQueryResults(
            '
				SELECT
					user.*,
					user_profile.*,
					user_privacy.*,
					news_feed.*
				FROM kmk_news_feed AS news_feed ' . $forceIndex . '
				INNER JOIN kmk_user AS user ON
					(user.user_id = news_feed.user_id)
				INNER JOIN kmk_user_profile AS user_profile ON
					(user_profile.user_id = user.user_id)
				LEFT JOIN kmk_user_follow AS user_follow ON
					(user_follow.user_id = user.user_id
					AND user_follow.follow_user_id = ' . $viewingUserIdQuoted . ')
				INNER JOIN kmk_user_privacy AS user_privacy ON
					(user_privacy.user_id = user.user_id
						' . ($bypassPrivacy ? '' : '
							AND (user.user_id = ' . $viewingUserIdQuoted . '
								OR (
									user_privacy.allow_receive_news_feed <> \'none\'
									AND IF(user_privacy.allow_receive_news_feed = \'members\', ' . $isRegistered . ', 1)
									AND IF(user_privacy.allow_receive_news_feed = \'followed\', user_follow.user_id IS NOT NULL, 1)
								)
							)
						') . '
					)
				WHERE ' . $whereClause . '
				AND content_type IN (' . $db->quote(GFNClassifieds_Application::$contentTypes) . ')
				ORDER BY news_feed.event_date DESC
			', $maxItems
        ), 'news_feed_id');
    }
}