<?php /*dac75f4104707e30369ccfa12b7ad9c1fce4b3a6*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_ControllerPublic_Member extends XFCP_GFNClassifieds_Extend_XenForo_ControllerPublic_Member
{
    public function actionMember()
    {
        $controllerResponse = parent::actionMember();

        if ($controllerResponse instanceof XenForo_ControllerResponse_View)
        {
            if ($controllerResponse->viewName == 'XenForo_ViewPublic_Member_View')
            {
                $viewParams = &$controllerResponse->params;

                if (!empty($viewParams['user']['rating_count']))
                {
                    $viewParams['traderRating'] = array(
                        'positive' => ($viewParams['user']['rating_positive_count'] / $viewParams['user']['rating_count']) * 100,
                        'neutral' => ($viewParams['user']['rating_neutral_count'] / $viewParams['user']['rating_count']) * 100,
                        'negative' => ($viewParams['user']['rating_negative_count'] / $viewParams['user']['rating_count']) * 100
                    );
                }
                else
                {
                    $viewParams['traderRating'] = array('positive' => 0, 'neutral' => 0, 'negative' => 0);
                }
            }
        }

        return $controllerResponse;
    }

    protected function _getNotableMembers($type, $limit)
    {
        if ($type == 'classifieds' && XenForo_Visitor::getInstance()->hasPermission('classifieds', 'view'))
        {
            $notableCriteria = array(
                'is_banned' => 0,
                'classified_count' => array('>', 0)
            );

            return array($this->_getUserModel()->getUsers($notableCriteria, array(
                'join' => XenForo_Model_User::FETCH_USER_FULL,
                'limit' => $limit,
                'order' => 'classified_count',
                'direction' => 'desc'
            )), 'classified_count');
        }

        return parent::_getNotableMembers($type, $limit);
    }
}