<?php /*2883334a19cc24ceb613c7dcd1e416a4763b70ad*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerPublic_Home extends KomuKuYJB_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        if ($this->_input->filterSingle('classified_id', XenForo_Input::UINT))
        {
            return $this->responseReroute('KomuKuYJB_ControllerPublic_Classified', 'view');
        }

        $categoryModel = $this->models()->category();
        $classifiedModel = $this->models()->classified();
        $advertTypeModel = $this->models()->advertType();

        $defaultOrder = 'bump_date';
        $defaultOrderDirection = 'desc';
        $defaultViewMode = XenForo_Helper_Cookie::getCookie('classified_view_mode', $this->_request);
        if (!$defaultViewMode)
        {
            $defaultViewMode = KomuKuYJB_Options::getInstance()->get('defaultListViewMode');
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
        $criteria['category_id'] = array_keys($viewableCategories);

        $categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
        $categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);
        $categories = isset($categoryList[0]) ? $categoryList[0] : array();

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = KomuKuYJB_Options::getInstance()->get('classifiedsPerPage');
        $totalClassifieds = $classifiedModel->countClassifieds($criteria);

        $totalFeatured = 0;
        foreach ($categories AS $category)
        {
            $totalFeatured += $category['featured_count'];
        }

        $this->canonicalizePageNumber($page, $perPage, $totalClassifieds, 'classifieds');
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds', null, array('page' => $page)));

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
            'canAddClassified' => count($viewableCategories) > 0 && $categoryModel->canAddClassified(),
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

        $extraSidebarBlocks = array(
            'bottom' => array(
                $this->responseView('KomuKuYJB_SidebarBlock_ClassifiedStats', 'classifieds_sidebar_block_stats')
            )
        );

        $containerParams = array(
            'categories' => $viewableCategories,
            'groupedCategories' => $categoryList,
            'advertTypes' => $advertTypes,
            'extraSidebarBlocks' => $extraSidebarBlocks,
            'featuredClassifieds' => $featuredClassifieds
        );

        return $this->_getWrapper(
            $this->responseView(
                'KomuKuYJB_ViewPublic_Home', 'classifieds_home', $viewParams
            ), $containerParams
        );
    }

    public function actionFeatured()
    {
        if ($categoryId = $this->_input->filterSingle('category_id', XenForo_Input::UINT))
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                $this->_buildLink('classifieds/categories', array('category_id' => $categoryId), array('featured' => true))
            );
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
            $this->_buildLink('classifieds', null, array('featured' => true))
        );
    }

    public function actionCreate()
    {
        if ($this->isConfirmedPost())
        {
            return $this->responseReroute('KomuKuYJB_ControllerPublic_Category', 'create-item');
        }

        $categoryModel = $this->models()->category();

        if (!$categoryModel->canAddClassified(null, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $categories = $categoryModel->getViewableCategories();
        $categories = $categoryModel->prepareCategories($categories);

        if (count($categories) == 1)
        {
            $category = reset($categories);

            if ($category['canAdd'])
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                    $this->_buildLink('classifieds/categories/create-item', $category)
                );
            }
        }

        $viewParams = array(
            'categories' => $categoryModel->prepareCategories($categoryModel->getViewableCategories())
        );

        return $this->responseView('KomuKuYJB_ViewPublic_ChooseCategory', 'classifieds_choose_category', $viewParams);
    }

    public function actionPackageInfo()
    {
        $this->_assertPostOnly();

        $packageId = $this->_input->filterSingle('package_id', XenForo_Input::UINT);
        if (!$package = $this->models()->package()->getPackageById($packageId))
        {
            return $this->responseError(new XenForo_Phrase('requested_package_not_found'), 404);
        }

        $this->models()->package()->preparePackage($package);

        $viewParams = array(
            'package' => $package,
            'baseCurrency' => $this->models()->package()->getDefaultCurrency()
        );

        return $this->responseView('KomuKuYJB_ViewPublic_PackageInfo', 'classifieds_package_info', $viewParams);
    }

    public function actionFetchLocation()
    {
        $classifiedId = $this->_input->filterSingle('classified_id', XenForo_Input::UINT);
        if ($classifiedId)
        {
            list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable($classifiedId, array(
                'join' => KomuKuYJB_Model_Classified::FETCH_LOCATION
            ));

            if (!$this->models()->classified()->canEditClassified($classified, $category, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
        }
        else
        {
            $category = false;
            $classified = false;

            $ip = XenForo_Helper_Ip::getBinaryIp();
            if ($ip)
            {
                $ip = XenForo_Helper_Ip::convertIpBinaryToString($ip);

                try
                {
                    $client = XenForo_Helper_Http::getClient('http://freegeoip.net/json/' . $ip);
                    $response = $client->request();

                    if ($response->isSuccessful() && $json = json_decode($response->getBody(), true))
                    {
                        if (isset($json['latitude'], $json['longitude']))
                        {
                            $classified = array(
                                'latitude' => $json['latitude'],
                                'longitude' => $json['longitude']
                            );
                        }
                    }
                }
                catch (Exception $e) { }
            }
        }

        $viewParams = array(
            'category' => $category,
            'classified' => $classified
        );

        return $this->responseView('KomuKuYJB_ViewPublic_FetchLocation', 'classifieds_location_fetch', $viewParams);
    }

    public function actionFilterMenu()
    {
        $categoryModel = $this->models()->category();
        $viewableCategories = $categoryModel->getViewableCategories();

        $categoryId = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
        if ($categoryId)
        {
            $category = $this->getContentHelper()->assertCategoryValidAndViewable();
            $categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
            $childCategories = isset($categoryList[$category['category_id']]) ? $categoryList[$category['category_id']] : array();

            if ($childCategories)
            {
                $searchCategoryIds = $categoryModel->getDescendantCategoryIdsFromGrouped($categoryList, $category['category_id']);
                $searchCategoryIds[] = $category['category_id'];
            }
            else
            {
                $searchCategoryIds = array($category['category_id']);
            }
        }
        else
        {
            $category = null;
            $searchCategoryIds = array_keys($viewableCategories);
        }

        $params = $this->_input->filterSingle('params', XenForo_Input::ARRAY_SIMPLE);
        $typeFilter = isset($params['advert_type_id']) ? intval($params['advert_type_id']) : '';
        $prefixFilter = isset($params['prefix_id']) ? intval($params['prefix_id']) : '';
        $isExpired = !empty($params['expired']);
        $isClosed = !empty($params['closed']);
        $isCompleted = !empty($params['completed']);

        $prefixModel = $this->models()->prefix();
        $prefixesGrouped = $prefixModel->getPrefixesByGroups();

        if ($prefixesGrouped)
        {
            $visiblePrefixes = $prefixModel->getVisiblePrefixIds(null, $searchCategoryIds);
            foreach ($prefixesGrouped AS $key => &$prefixes)
            {
                foreach ($prefixes AS $prefixId => $prefix)
                {
                    if (!isset($visiblePrefixes[$prefixId]))
                    {
                        unset ($prefixes[$prefixId]);
                    }
                }

                if (!count($prefixes))
                {
                    unset ($prefixesGrouped[$key]);
                }
            }
        }

        $advertTypeModel = $this->models()->advertType();
        $advertTypes = $advertTypeModel->getAllAdvertTypes();

        if ($advertTypes)
        {
            $visibleAdvertTypes = $advertTypeModel->getVisibleAdvertTypeIds(null, $searchCategoryIds);
            foreach ($advertTypes as $advertTypeId => $advertType)
            {
                if (!isset($visibleAdvertTypes[$advertTypeId]))
                {
                    unset ($advertTypes[$advertTypeId]);
                }
            }
        }

        $advertTypeModel->prepareAdvertTypes($advertTypes);

        $viewParams = array(
            'category' => $category,
            'advertTypes' => $advertTypes,
            'params' => $params,
            'prefixesGrouped' => $prefixesGrouped,
            'typeFilter' => $typeFilter,
            'prefixFilter' => $prefixFilter,
            'isExpired' => $isExpired,
            'isClosed' => $isClosed,
            'isCompleted' => $isCompleted,
            'isActiveOnly' => !$isExpired && !$isClosed && !$isCompleted
        );

        return $this->responseView('KomuKuYJB_ViewPublic_FilterMenu', 'classifieds_filter_menu', $viewParams);
    }

    protected function _getWrapper(XenForo_ControllerResponse_View $subView, array $containerParams = array())
    {
        return $this->getHelper('KomuKuYJB_ControllerHelper_PageWrapper')->getClassifiedListWrapper($subView, $containerParams);
    }
}