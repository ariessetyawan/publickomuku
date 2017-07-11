<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_Listener_Listener
{
    public static function account($class, array &$extend)
    {
        $extend[] = 'ThemeHouse_UserRatings_ControllerPublic_Account';
    }

    public static function controller($class, array &$extend)
    {
        $extend[] = 'ThemeHouse_UserRatings_ControllerPublic_Member';
    }

    public static function model($class, array &$extend)
    {
        $extend[] = 'ThemeHouse_UserRatings_Model_XenForo_User';
    }

    public static function ratingTab(array &$extraTabs, $selectedTabId)
    {
        /* @var $ratingModel ThemeHouse_UserRatings_Model_Ratings */
        $ratingModel = XenForo_Model::create('ThemeHouse_UserRatings_Model_Ratings');

        if (XenForo_Application::get('options')->tabShow and $ratingModel->canViewRatings()) {
            $extraTabs['ratings'] = array(
                'title' => XenForo_Application::get('options')->tabName,
                'href' => XenForo_Link::buildPublicLink('ratings'),
                'selected' => ($selectedTabId == 'ratings'),
                'position' => XenForo_Application::get('options')->tabPosition,
            );
        }
    }

    public static function template($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
        if ($hookName == 'moderator_bar') {
            //Get moderated ratings.
            $conditions = array(
               'active' => 0,
            );

            $unapprovedRatings = XenForo_Model::create(ThemeHouse_UserRatings_Model_Ratings)->countRatings($conditions);

            $unapproved = $template->create('th_moderation_center_link');
            $unapproved->setParam('unapprovedRatings', $unapprovedRatings);

            $contents .= $unapproved;
        }
    }
}
