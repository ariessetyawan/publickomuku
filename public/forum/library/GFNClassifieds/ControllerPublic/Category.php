<?php /*c46719dbcc3a064f6b719d6e9f6a5aea2774dbfa*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerPublic_Category extends GFNClassifieds_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        if ($this->_input->filterSingle('category_id', XenForo_Input::UINT))
        {
            return $this->responseReroute(__CLASS__, 'view');
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
            $this->_buildLink('classifieds')
        );
    }

    public function actionView()
    {
        $category = $this->getContentHelper()->assertCategoryValidAndViewable(null, array(
            'watchUserId' => XenForo_Visitor::getUserId()
        ));

        $categoryModel = $this->models()->category();
        $classifiedModel = $this->models()->classified();
        $advertTypeModel = $this->models()->advertType();

        $defaultOrder = 'bump_date';
        $defaultOrderDirection = 'desc';
        $defaultViewMode = XenForo_Helper_Cookie::getCookie('classified_view_mode', $this->_request);
        if (!$defaultViewMode)
        {
            $defaultViewMode = GFNClassifieds_Options::getInstance()->get('defaultListViewMode');
        }

        $order = $this->_input->filterSingle('order', XenForo_Input::STRING, array('default' => $defaultOrder));
        if ($order == 'expiring' || $order == 'title' || $order == 'username')
        {
            $defaultOrderDirection = 'asc';
        }

        $orderDirection = $this->_input->filterSingle('direction', XenForo_Input::STRING, array('default' => $defaultOrderDirection));
        $viewMode = $this->_input->filterSingle('view', XenForo_Input::STRING, array('default' => $defaultViewMode));
        if ($viewMode != $defaultViewMode)
        {
            XenForo_Helper_Cookie::setCookie('classified_view_mode', $viewMode, GFNCore_Cache::YEAR);
        }

        $typeFilter = $this->_input->filterSingle('advert_type_id', XenForo_Input::INT);
        $prefixFilter = $this->_input->filterSingle('prefix_id', XenForo_Input::INT);
        $featuredFilter = $this->_input->filterSingle('featured', XenForo_Input::BOOLEAN);

        $criteria = array();

        if ($typeFilter)
        {
            $criteria['advert_type_id'] = $typeFilter;
        }

        if ($prefixFilter)
        {
            $criteria['prefix_id'] = $prefixFilter;
        }

        if ($featuredFilter)
        {
            $criteria['feature_date'] = array('>', 0);
        }

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = GFNClassifieds_Options::getInstance()->get('classifiedsPerPage');

        $criteria += $classifiedModel->getPermissionBasedFetchConditions();

        $criteria['expired'] = $this->_input->filterSingle('expired', XenForo_Input::BOOLEAN);
        $criteria['closed'] = $this->_input->filterSingle('closed', XenForo_Input::BOOLEAN);
        $criteria['completed'] = $criteria['closed'];

        if ($criteria['expired'] || $criteria['closed'])
        {
            $criteria['visible'] = false;
            $criteria['pending'] = false;
        }

        $viewableCategories = $categoryModel->prepareCategories($categoryModel->getViewableCategories());

        $categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
        $categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);

        $childCategories = isset($categoryList[$category['category_id']]) ? $categoryList[$category['category_id']] : array();
        $categories = array_merge(array($category['category_id'] => $category), $childCategories);

        if ($childCategories)
        {
            $searchCategoryIds = $categoryModel->getDescendantCategoryIdsFromGrouped($categoryList, $category['category_id']);
            $searchCategoryIds[] = $category['category_id'];
        }
        else
        {
            $searchCategoryIds = array($category['category_id']);
        }

        $criteria['category_id'] = $searchCategoryIds;
        $totalClassifieds = $classifiedModel->countClassifieds($criteria);

        $totalFeatured = 0;
        foreach ($categories AS $_category)
        {
            $totalFeatured += $_category['featured_count'];
        }

        $this->canonicalizePageNumber($page, $perPage, $totalClassifieds, 'classifieds/categories', $category);
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/categories', $category, array('page' => $page)));

        $fetchOptions = $this->_getClassifiedListFetchOptions();

        if ($criteria['deleted'])
        {
            $fetchOptions['join'] |= $classifiedModel::FETCH_DELETION_LOG;
        }

        $fetchOptions += array(
            'perPage' => $perPage,
            'page' => $page,
            'order' => $order,
            'direction' => $orderDirection
        );

        $classifieds = $classifiedModel->getClassifieds($criteria, $fetchOptions);
        $classifieds = $classifiedModel->filterUnviewableClassifieds($classifieds);
        $classifieds = $classifiedModel->prepareClassifieds($classifieds);
        $inlineModOptions = $classifiedModel->getInlineModOptionsForClassifieds($classifieds);

        $advertTypes = $advertTypeModel->getAllAdvertTypes();
        $advertTypeModel->prepareAdvertTypes($advertTypes);

        if ($totalFeatured && !$featuredFilter && !$typeFilter && !$prefixFilter && $order == $defaultOrder)
        {
            $featuredClassifieds = $classifiedModel->getFeaturedClassifiedsInCategories(
                $criteria['category_id'],
                array_merge(
                    $this->_getClassifiedListFetchOptions(),
                    array('limit' => 6, 'order' => 'random')
                )
            );

            $featuredClassifieds = $classifiedModel->filterUnviewableClassifieds($featuredClassifieds);
            $featuredClassifieds = $classifiedModel->prepareClassifieds($featuredClassifieds);
        }
        else
        {
            $featuredClassifieds = array();
        }

        $pageNavParams = array(
            'order' => ($order != $defaultOrder ? $order : false),
            'direction' => ($orderDirection != $defaultOrderDirection ? $orderDirection : false),
            'advert_type_id' => ($typeFilter ? $typeFilter : false),
            'prefix_id' => ($prefixFilter ? $prefixFilter : false),
            'expired' => $criteria['expired'],
            'closed' => $criteria['closed'],
            'featured' => ($featuredFilter ? $featuredFilter : false)
        );

        $viewParams = array(
            'category' => $category,
            'childCategories' => $childCategories,
            'categoryBreadcrumbs' => $categoryModel->getCategoryBreadcrumb($category, false),

            'canAddClassified' => $categoryModel->canAddClassified($category),
            'totalClassifieds' => $totalClassifieds,
            'classifieds' => $classifieds,

            'pageNavParams' => $pageNavParams,
            'page' => $page,
            'perPage' => $perPage,

            'order' => $order,
            'orderPhrases' => $classifiedModel->getOrderPhrases(),
            'orderOptions' => $classifiedModel->getOrderOptions($order, $defaultOrder),
            'orderDirection' => $orderDirection,
            'typeFilter' => $typeFilter,
            'prefixFilter' => $prefixFilter,
            'isExpired' => $criteria['expired'],
            'isClosed' => $criteria['closed'],
            'showFilterTabs' => $this->_displayFilterOptions($viewableCategories, array_keys($viewableCategories)),
            'viewMode' => $viewMode,

            'advertTypes' => $advertTypes,

            'inlineModOptions' => $inlineModOptions,
            'ignoredNames' => $this->_getIgnoredContentUserNames($classifieds)
        );

        $containerParams = array(
            'categories' => $viewableCategories,
            'groupedCategories' => $categoryList,
            'category' => $category,
            'advertTypes' => $advertTypes,
            'featuredClassifieds' => $featuredClassifieds
        );

        return $this->_getWrapper(
            $this->responseView(
                'GFNClassifieds_ViewPublic_Category_View',
                'classifieds_category_view', $viewParams
            ), $category, $containerParams
        );
    }

    public function actionWatch()
    {
        $category = $this->getContentHelper()->assertCategoryValidAndViewable();
        $categoryModel = $this->models()->category();

        if (!$categoryModel->canWatchCategory($category, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        $watchModel = $this->models()->categoryWatch();

        if ($this->isConfirmedPost())
        {
            if ($this->_input->filterSingle('stop', XenForo_Input::STRING))
            {
                $notifyOn = 'delete';
            }
            else
            {
                $notifyOn = $this->_input->filterSingle('notify_on', XenForo_Input::STRING);
            }

            $sendAlert = $this->_input->filterSingle('send_alert', XenForo_Input::BOOLEAN);
            $sendEmail = $this->_input->filterSingle('send_email', XenForo_Input::BOOLEAN);
            $includeChildren = $this->_input->filterSingle('include_children', XenForo_Input::BOOLEAN);

            $watchModel->setCategoryWatchState(
                XenForo_Visitor::getUserId(), $category['category_id'],
                $notifyOn, $sendAlert, $sendEmail, $includeChildren
            );

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('classifieds/categories', $category),
                null,
                array('linkPhrase' => ($notifyOn != 'delete' ? new XenForo_Phrase('unwatch_category') : new XenForo_Phrase('watch_category')))
            );
        }
        else
        {
            $watch = $watchModel->getUserCategoryWatchByCategoryId(
                XenForo_Visitor::getUserId(), $category['category_id']
            );

            $viewParams = array(
                'category' => $category,
                'watch' => $watch,
                'categoryBreadcrumbs' => $categoryModel->getCategoryBreadcrumb($category),
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Category_Watch', 'classifieds_category_watch', $viewParams);
        }
    }

    public function actionCreateItem()
    {
        $categoryModel = $this->models()->category();
        $category = $this->getContentHelper()->assertCategoryValidAndViewable();

        if (!$categoryModel->canAddClassified($category, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        if ($this->isConfirmedPost())
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/categories/create-item', $category)
            );
        }

        XenForo_Application::set('classifiedCreateCategory', $category);
        return $this->responseReroute('GFNClassifieds_ControllerPublic_Classified', 'add');
    }

    public function actionSaveDraft()
    {
        $category = $this->getContentHelper()->assertCategoryValidAndViewable();
        $categoryModel = $this->models()->category();

        if (!$categoryModel->canAddClassified($category, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        $extra = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'tag_line' => XenForo_Input::STRING,
            'prefix_id' => XenForo_Input::UINT,
            'advert_type_id' => XenForo_Input::UINT,
            'package_id' => XenForo_Input::UINT,
            'attachment_hash' => XenForo_Input::STRING,
            'price' => XenForo_Input::UNUM,
            'currency' => XenForo_Input::STRING,
            'tags' => XenForo_Input::STRING
        ));

        $extra['custom_fields'] = $this->getContentHelper()->getCustomFieldValues();
        $description = $this->getHelper('Editor')->getMessageText('description', $this->_input);

        $forceDelete = $this->_input->filterSingle('delete_draft', XenForo_Input::BOOLEAN);
        $draftId = 'classifieds-category-' . $category['category_id'];

        if (!strlen($description) || $forceDelete)
        {
            $draftSaved = false;
            $draftDeleted = $this->_getDraftModel()->deleteDraft($draftId) || $forceDelete;
        }
        else
        {
            $this->_getDraftModel()->saveDraft($draftId, $description, $extra);
            $draftSaved = true;
            $draftDeleted = false;
        }

        $viewParams = array(
            'draftSaved' => $draftSaved,
            'draftDeleted' => $draftDeleted
        );

        $controllerResponse = $this->responseView();
        $controllerResponse->params = $viewParams;
        $controllerResponse->jsonParams = $viewParams;
        return $controllerResponse;
    }

    protected function _getCategoryOrError($categoryId = null)
    {
        if ($categoryId === null)
        {
            $categoryId = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
        }

        $category = $this->models()->category()->getCategoryById($categoryId);
        if (!$category)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_category_not_found');
        }

        return $this->models()->category()->prepareCategory($category);
    }

    /**
     * @return XenForo_Model_Draft
     */
    protected function _getDraftModel()
    {
        return $this->getModelFromCache('XenForo_Model_Draft');
    }

    protected function _getWrapper(XenForo_ControllerResponse_View $subView, array $category, array $containerParams = array())
    {
        $containerParams += array('category' => $category);
        return $this->getHelper('GFNClassifieds_ControllerHelper_PageWrapper')->getClassifiedListWrapper($subView, $containerParams);
    }

    public static function getSessionActivityDetailsForList(array $activities)
    {
        $categoryIds = array();

        foreach ($activities as $activity)
        {
            if (!empty($activity['params']['category_id']))
            {
                $categoryIds[$activity['params']['category_id']] = intval($activity['params']['category_id']);
            }
        }

        $categoryData = array();

        if ($categoryIds)
        {
            /** @var GFNClassifieds_Model_Category $categoryModel */
            $categoryModel = XenForo_Model::create('GFNClassifieds_Model_Category');
            $permissionCombinationId = XenForo_Visitor::getPermissionCombinationId();

            $categories = $categoryModel->getCategoriesByIds($categoryIds, array(
                'permissionCombinationId' => $permissionCombinationId
            ));

            foreach ($categories as $category)
            {
                $categoryModel->setCategoryPermCache($permissionCombinationId, $category['category_id'], $category['category_permission_cache']);

                if ($categoryModel->canViewCategory($category))
                {
                    $categoryData[$category['category_id']] = array(
                        'title' => $category['title'],
                        'url' => XenForo_Link::buildPublicLink('classifieds/categories', $category)
                    );
                }
            }
        }

        $output = array();

        foreach ($activities as $key => $activity)
        {
            $category = false;

            if (!empty($activity['params']['category_id']))
            {
                $categoryId = $activity['params']['category_id'];
                if (isset($categoryData[$categoryId]))
                {
                    $category = $categoryData[$categoryId];
                }
            }

            if ($category)
            {
                $output[$key] = array(
                    new XenForo_Phrase('viewing_classified_category'),
                    $category['title'],
                    $category['url'],
                    ''
                );
            }
            else
            {
                $output['key'] = new XenForo_Phrase('viewing_classified_category');
            }
        }

        return $output;
    }
}