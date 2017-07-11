<?php /*d28d56ef62f0974b17e9471b9e8bb0bd2cbaba22*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_ControllerPublic_Forum extends XFCP_KomuKuYJB_Extend_XenForo_ControllerPublic_Forum
{
    protected function _postDispatch($controllerResponse, $controllerName, $action)
    {
        parent::_postDispatch($controllerResponse, $controllerName, $action);

        if (!KomuKuYJB_Options::getInstance()->get('showAddButtonInForum'))
        {
            return;
        }

        if ($controllerResponse instanceof XenForo_ControllerResponse_View && $controllerResponse->templateName == 'forum_view')
        {
            $params = &$controllerResponse->params;
            if (isset($params['addClassifiedButton']))
            {
                return;
            }

            /** @var KomuKuYJB_Model_Category $categoryModel */
            $categoryModel = $this->getModelFromCache('KomuKuYJB_Model_Category');
            $categories = $categoryModel->getCategories(array(
                'thread_node_id' => $params['forum']['node_id']
            ), array(
                'permissionCombinationId' => XenForo_Visitor::getPermissionCombinationId()
            ));

            if (!$categories)
            {
                return;
            }

            $categoryModel->bulkSetCategoryPermCache(XenForo_Visitor::getPermissionCombinationId(), $categories, 'category_permission_cache');
            $categories = $categoryModel->prepareCategories($categories);

            foreach ($categories as $i => $v)
            {
                if (!$v['canAdd'] || !$v['allowClassifieds'])
                {
                    unset ($categories[$i]);
                }
            }

            if (!$categories)
            {
                return;
            }

            if (count($categories) == 1)
            {
                $params['addClassifiedButton'] = array(
                    'link' => XenForo_Link::buildPublicLink('classifieds/categories/create-item', reset($categories)),
                    'overlay' => false
                );
            }
            else
            {
                $params['addClassifiedButton'] = array(
                    'link' => XenForo_Link::buildPublicLink('classifieds/create'),
                    'overlay' => true
                );
            }
        }
    }

    public function actionIndex()
    {
        $controllerResponse = parent::actionIndex();

        if (!$controllerResponse instanceof XenForo_ControllerResponse_View || $controllerResponse->viewName != 'XenForo_ViewPublic_Forum_List')
        {
            return $controllerResponse;
        }

        $newClassifiedLimit = KomuKuYJB_Options::getInstance()->get('forumListNewClassifieds');
        if ($newClassifiedLimit)
        {
            $newClassifieds = $this->_getClassifieds($newClassifiedLimit, 'item_date', 'desc');
        }
        else
        {
            $newClassifieds = array();
        }

        $randomClassifiedLimit = KomuKuYJB_Options::getInstance()->get('forumListRandomClassifieds');
        if ($randomClassifiedLimit)
        {
            $randomClassifieds = $this->_getClassifieds($randomClassifiedLimit, 'random');
        }
        else
        {
            $randomClassifieds = array();
        }

        $featuredClassifiedLimit = KomuKuYJB_Options::getInstance()->get('forumListFeaturedClassifieds');
        if ($featuredClassifiedLimit)
        {
            $featuredClassifieds = $this->_getClassifieds($featuredClassifiedLimit, 'bump_date', 'desc', array('featured' => true));
        }
        else
        {
            $featuredClassifieds = array();
        }

        $controllerResponse->params += array(
            'newClassifieds' => $newClassifieds,
            'randomClassifieds' => $randomClassifieds,
            'featuredClassifieds' => $featuredClassifieds
        );

        return $controllerResponse;
    }

    public function actionForum()
    {
        $controllerResponse = parent::actionForum();

        if (!$controllerResponse instanceof XenForo_ControllerResponse_View || $controllerResponse->viewName != 'XenForo_ViewPublic_Forum_View')
        {
            return $controllerResponse;
        }

        $threads = &$controllerResponse->params['threads'];
        $classifiedRelated = array();

        foreach ($threads as $thread)
        {
            if ($thread['discussion_type'] == 'classified')
            {
                $classifiedRelated[] = $thread['thread_id'];
            }
        }

        if (!$classifiedRelated)
        {
            return $controllerResponse;
        }

        /** @var KomuKuYJB_Model_Classified $model */
        $model = $this->getModelFromCache('KomuKuYJB_Model_Classified');
        $classifieds = $model->getClassifiedsByDiscussionIds($classifiedRelated);

        foreach ($classifieds as $classified)
        {
            if (isset($threads[$classified['discussion_thread_id']])) // sanity check...
            {
                $thread = &$threads[$classified['discussion_thread_id']];

                $thread += array(
                    'classified_id' => $classified['classified_id'],
                    'classified_title' => $classified['title'],
                    'classified_price' => $classified['price'],
                    'classified_currency' => $classified['currency'],
                    'classified_featured_image_date' => $classified['featured_image_date'],
                    'classified_advert_type_id' => $classified['advert_type_id'],
                    'classified_prefix_id' => $classified['prefix_id'],
                    'classified_state' => $classified['classified_state']
                );
            }
        }

        return $controllerResponse;
    }

    protected function _getClassifieds($limit, $order, $direction = false, array $conditions = array())
    {
        /** @var KomuKuYJB_Model_Classified $model */
        $model = $this->getModelFromCache('KomuKuYJB_Model_Classified');
        $visitor = XenForo_Visitor::getInstance();

        $fetchOptions = array(
            'limit' => max($limit * 2, 10),
            'join' => $model::FETCH_CATEGORY | $model::FETCH_USER,
            'permissionCombinationId' => $visitor->get('permission_combination_id'),
            'order' => $order
        );

        if ($direction)
        {
            $fetchOptions['direction'] = $direction;
        }

        $conditions += array(
            'deleted' => false,
            'moderated' => false,
            'pending' => false,
            'expired' => false,
            'completed' => false,
            'closed' => false,
            'on_hold' => false
        );

        $classifieds = $model->getClassifieds($conditions, $fetchOptions);

        foreach ($classifieds as $i => &$classified)
        {
            $classified['permissions'] = XenForo_Permission::unserializePermissions($classified['category_permission_cache']);
            if (!$model->canViewClassified($classified, $classified, $null, $null, $classified['permissions']) || $visitor->isIgnoring($classified['user_id']))
            {
                unset ($classifieds[$i]);
            }

            $classified = $model->prepareClassified($classified, $classified, $null, $classified['permissions']);
        }

        return array_slice($classifieds, 0, $limit, true);
    }
}