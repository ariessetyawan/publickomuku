<?php /*1130c7f034716ffca5d40bfe5aa46afd73336a2a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_NewsFeed extends XenForo_Model
{
    public static function publish($source, array $data, array $extraData = array())
    {
        /** @var XenForo_Model_NewsFeed $model */
        $model = XenForo_Model::create('XenForo_Model_NewsFeed');

        $userId = $data['user_id'];

        if (isset($data['username']))
        {
            $username = $data['username'];
        }
        elseif (isset($extraData['username']))
        {
            $username = $extraData['username'];
        }
        else
        {
            $user = XenForo_Model::create('XenForo_Model_User')->getUserById($userId);
            $username = $user['username']; unset ($user);
        }

        switch ($source)
        {
            case 'classified':
                $model->publish(
                    $userId, $username, 'classified',
                    $data['classified_id'], 'insert', $extraData
                );
                break;

            case 'comment':
                $model->publish(
                    $userId, $username, 'classified_comment',
                    $data['comment_id'], 'insert', $extraData
                );
                break;

            case 'trader_rating':
                $model->publish(
                    $userId, $username, 'classified_trader_rating',
                    $data['feedback_id'], 'insert', $extraData
                );
                break;

            case 'classified_complete':
                $model->publish(
                    $userId, $username, 'classified',
                    $data['classified_id'], 'complete', $extraData
                );
                break;
        }
    }
}