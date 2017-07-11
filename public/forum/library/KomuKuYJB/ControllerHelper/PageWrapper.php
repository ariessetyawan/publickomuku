<?php /*b8f531484fce965f2429e40fa4efa624e8289c9d*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerHelper_PageWrapper extends XenForo_ControllerHelper_Abstract
{
    public function getClassifiedListWrapper(XenForo_ControllerResponse_View $subView, array $containerParams = array())
    {
        /** @var KomuKuYJB_Model_Category $categoryModel */ /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $categoryModel = $this->_controller->getModelFromCache('KomuKuYJB_Model_Category');
        $classifiedModel = $this->_controller->getModelFromCache('KomuKuYJB_Model_Classified');

        if (!isset($containerParams['categories']))
        {
            $containerParams['categories'] = $categoryModel->prepareCategories($categoryModel->getViewableCategories());
        }

        if (!empty($containerParams['categories']))
        {
            if (!isset($containerParams['groupedCategories']))
            {
                $containerParams['groupedCategories'] = $categoryModel->groupCategoriesByParent($containerParams['categories']);
                $containerParams['groupedCategories'] = $categoryModel->applyRecursiveCountsToGrouped($containerParams['groupedCategories']);
            }

            if (isset($containerParams['category']))
            {
                $containerParams['selectedCategoryId'] = $containerParams['category']['category_id'];
                $containerParams['selectedCategories'] = array_merge(
                    array_keys($categoryModel->getCategoryAncestors($containerParams['category'], $containerParams['categories'])),
                    array($containerParams['category']['category_id'])
                );
            }

            $categoryIds = array_keys($containerParams['categories']);
            if ($categoryIds)
            {
                $randomClassifieds = $classifiedModel->getClassifieds(array(
                    'category_id' => $categoryIds,
                    'expired' => false,
                    'closed' => false
                ), array(
                    'join' => $classifiedModel::FETCH_CATEGORY
                        | $classifiedModel::FETCH_USER
                        | $classifiedModel::FETCH_LOCATION,
                    'order' => 'random',
                    'limit' => 1,
                    'offset' => 0
                ));

                if ($randomClassifieds)
                {
                    $containerParams['randomClassified'] = $classifiedModel->prepareClassified(reset($randomClassifieds));
                }
            }
        }

        $wrapper = $this->_controller->responseView('KomuKuYJB_ViewPublic_ClassifiedList_PageWrapper', 'classifieds_page_wrapper', $containerParams);
        $wrapper->containerParams['bodyClasses'] = 'sidebarTo' . ucfirst(KomuKuYJB_Options::getInstance()->get('sidebarLocation'));
        $wrapper->subView = $subView;

        return $wrapper;
    }

    public function getClassifiedViewWrapper($selectedTab, XenForo_ControllerResponse_View $subView, array $classified, array $category, array $containerParams = array())
    {
        /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $classifiedModel = $this->_controller->getModelFromCache('KomuKuYJB_Model_Classified');
        /** @var KomuKuYJB_Model_Category $categoryModel */
        $categoryModel = $this->_controller->getModelFromCache('KomuKuYJB_Model_Category');
        /** @var XenForo_Model_User $userModel */
        $userModel = $this->_controller->getModelFromCache('XenForo_Model_User');

        if ($classified['discussion_thread_id'])
        {
            /** @var XenForo_Model_Thread $threadModel */
            $threadModel = $this->_controller->getModelFromCache('XenForo_Model_Thread');
            $thread = $threadModel->getThreadById($classified['discussion_thread_id'], array(
                'join' => $threadModel::FETCH_FORUM,
                'permissionCombinationId' => XenForo_Visitor::getPermissionCombinationId()
            ));

            if (
                $thread && $thread['discussion_type'] == 'classified'
                && !$threadModel->canViewThreadAndContainer($thread, $thread, $null, XenForo_Permission::unserializePermissions($thread['node_permission_cache']))
            )
            {
                $thread = false;
            }

            if ($thread)
            {
                $thread['title'] = XenForo_Helper_String::censorString($thread['title']);
            }
        }
        else
        {
            $thread = false;
        }

        if (empty($containerParams['advertType']))
        {
            /** @var KomuKuYJB_Model_AdvertType $advertTypeModel */
            $advertTypeModel = $this->_controller->getModelFromCache('KomuKuYJB_Model_AdvertType');
            $containerParams['advertType'] = $advertTypeModel->getAdvertTypeById($classified['advert_type_id']);
            $advertTypeModel->prepareAdvertType($containerParams['advertType']);
        }

        if (!isset($containerParams['galleryImages']))
        {
            /** @var XenForo_Model_Attachment $attachmentModel */
            $attachmentModel = $this->_controller->getModelFromCache('XenForo_Model_Attachment');
            $containerParams['galleryImages'] = $attachmentModel->getAttachmentsByContentId('classified_gallery', $classified['classified_id']);
            $containerParams['galleryImages'] = $attachmentModel->prepareAttachments($containerParams['galleryImages']);
        }

        $conditions = $classifiedModel->getPermissionBasedFetchConditions($category);

        if ($classified['canViewHistory'])
        {
            /** @var XenForo_Model_EditHistory $historyModel */
            $historyModel = $this->_controller->getModelFromCache('XenForo_Model_EditHistory');
            $containerParams['hasHistory'] = $historyModel->getEditHistoryListForContent('classified', $classified['classified_id']) ? true : false;
        }

        if ($conditions['deleted'] === true || $conditions['moderated'] === true || $conditions['moderated'] == $classified['user_id'])
        {

        }

        $autoLinkTrigger = XenForo_Application::getSession()->get('classifiedAutoLinkTrigger');
        if ($autoLinkTrigger)
        {
            XenForo_Application::getSession()->remove('classifiedAutoLinkTrigger');
        }

        /** @var KomuKuYJB_Model_Comment $commentModel */
        $commentModel = $this->_controller->getModelFromCache('KomuKuYJB_Model_Comment');

        $criteria = array(
            'classified_id' => $classified['classified_id']
        );

        $criteria += $commentModel->getPermissionBasedFetchConditions($category);

        if (isset($containerParams['totalComments']))
        {
            $totalComments = $containerParams['totalComments'];
        }
        else
        {
            $totalComments = $commentModel->countComments($criteria);
        }

        if (empty($containerParams['hideComments']) && $totalComments)
        {
            $fetchOptions = array(
                'join' => $commentModel::FETCH_USER,
                'page' => 1,
                'perPage' => KomuKuYJB_Options::getInstance()->get('commentsPerPage'),
                'likeUserId' => XenForo_Visitor::getUserId()
            );

            if ($criteria['deleted'])
            {
                $fetchOptions['join'] |= $commentModel::FETCH_DELETION_LOG;
            }

            $comments = $commentModel->getComments($criteria, $fetchOptions);

            if (!empty($comments))
            {
                foreach ($comments as $commentId => $comment)
                {
                    if (!$commentModel->canViewComment($comment, $classified, $category))
                    {
                        unset ($comments[$commentId]);
                    }
                }

                if (!empty($comments))
                {
                    $parentCommentIds = array_keys($comments);
                    unset ($criteria['classified_id']);

                    $replies = $commentModel->getRepliesByParentCommentIds($parentCommentIds, $criteria, $fetchOptions);
                    $replies = $commentModel->prepareComments($replies, $classified, $category);
                    $inlineModOptions = $commentModel->getInlineModOptionsForComments($comments, $classified, $category);

                    $comments = $commentModel->prepareComments($comments, $classified, $category);
                    $commentModel->mergeRepliesToComments($replies, $comments);
                    $containerParams['comments'] = $comments;
                    $containerParams['inlineModOptions'] = $inlineModOptions;

                    if (count($comments) < $totalComments)
                    {
                        $containerParams['showMoreCommentsLink'] = true;
                    }
                }
            }
        }

        if ($classified['isCompleted'])
        {
            /** @var KomuKuYJB_Model_TraderRating $ratingModel */
            $ratingModel = $this->_controller->getModelFromCache('KomuKuYJB_Model_TraderRating');

            $rating = $ratingModel->getTraderRatings(array(
                'for_user_id' => $classified['user_id'],
                'classified_id' => $classified['classified_id']
            ));

            if ($rating)
            {
                $rating = reset($rating);
                $rating = $ratingModel->prepareTraderRating($rating);
            }

            $containerParams['rating'] = $rating;
        }

        $otherClassifiedsLimit = KomuKuYJB_Options::getInstance()->get('classifiedViewOtherClassifieds');
        if ($otherClassifiedsLimit)
        {
            $otherClassifieds = $classifiedModel->getClassifieds(array(
                'deleted' => false,
                'moderated' => false,
                'pending' => false,
                'expired' => false,
                'completed' => false,
                'closed' => false,
                'on_hold' => false,
                'not_classified_id' => $classified['classified_id'],
                'user_id' => $classified['user_id']
            ), array(
                'limit' => max($otherClassifiedsLimit * 2, 10),
                'join' => $classifiedModel::FETCH_CATEGORY | $classifiedModel::FETCH_USER,
                'permissionCombinationId' => XenForo_Visitor::getInstance()->get('permission_combination_id'),
                'order' => 'bump_date',
                'direction' => 'desc'
            ));

            foreach ($otherClassifieds as $i => &$cc)
            {
                $cc['permissions'] = XenForo_Permission::unserializePermissions($cc['category_permission_cache']);
                if (!$classifiedModel->canViewClassified($cc, $cc, $null, null, $cc['permissions']))
                {
                    unset ($otherClassifieds[$i]);
                }

                $cc = $classifiedModel->prepareClassified($cc, $cc, $null, $cc['permissions']);
            }

            $otherClassifieds = array_slice($otherClassifieds, 0, $otherClassifiedsLimit, true);
        }
        else
        {
            $otherClassifieds = array();
        }

        $containerParams += array(
            'classified' => $classified,
            'category' => $category,
            'thread' => $thread,

            'socialLinks' => KomuKuYJB_Helper_Misc::getSocialShareLinks(
                $classified['title'], XenForo_Link::buildPublicLink('canonical:classifieds', $classified)
            ),

            'canViewOnlineStatus' => $userModel->canViewUserOnlineStatus($classified),
            'categoryBreadcrumbs' => $categoryModel->getCategoryBreadcrumb($category),
            'selectedTab' => $selectedTab,
            'totalComments' => $totalComments,
            'autoLinkTrigger' => $autoLinkTrigger,
            'canViewIps' => $userModel->canViewIps(),
            'lastCommentDate' => $classified['last_comment_date'],
            'classifiedViewPage' => 'classified_view',
            'otherClassifieds' => $otherClassifieds
        );

        $wrapper = $this->_controller->responseView('KomuKuYJB_ViewPublic_Classified_PageWrapper', 'classifieds_item_view', $containerParams);
        $wrapper->subView = $subView; $wrapper->subView->params['classifiedViewPage'] = $containerParams['classifiedViewPage'];

        return $wrapper;
    }

    public function getAccountViewWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
    {
        $viewParams = array(
            'selectedGroup' => $selectedGroup,
            'selectedLink' => $selectedLink,
            'selectedKey' => "$selectedGroup/$selectedLink"
        );

        $wrapper = $this->_controller->responseView('KomuKuYJB_ViewPublic_Account_PageWrapper', 'classifieds_account_wrapper', $viewParams);
        $wrapper->subView = $subView;

        return $wrapper;
    }
}