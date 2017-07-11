<?php /*b56514e48d5bd2c957f06a56f4579ff076bf3e67*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_ControllerPublic_FindNew extends XFCP_GFNClassifieds_Extend_XenForo_ControllerPublic_FindNew
{
    public function actionClassifieds()
    {
        $this->getRouteMatch()->setSections('classifieds');

        $searchId = $this->_input->filterSingle('search_id', XenForo_Input::UINT);
        if (!$searchId)
        {
            return $this->findNewClassifieds();
        }

        $classifiedModel = $this->_getClassifiedModel();
        $searchModel = $this->_getSearchModel();
        $visitor = XenForo_Visitor::getInstance();

        $search = $searchModel->getSearchById($searchId);
        if (!$search || $search['user_id'] != $visitor->getUserId() || $search['search_type'] != 'new-classifieds')
        {
            return $this->findNewClassifieds();
        }

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = GFNClassifieds_Options::getInstance()->get('classifiedsPerPage');

        $pageResultIds = $searchModel->sliceSearchResultsToPage($search, $page, $perPage);
        $classifiedIds = XenForo_Application::arrayColumn($pageResultIds, 1);

        $classifieds = $classifiedModel->getClassifiedsByIds($classifiedIds, array(
            'join' => $classifiedModel::FETCH_CATEGORY | $classifiedModel::FETCH_USER,
            'permissionCombinationId' => $visitor->get('permission_combination_id'),
            'limit' => XenForo_Application::getOptions()->get('maximumSearchResults')
        ));

        $this->_getClassifiedCategoryModel()->bulkSetCategoryPermCache(null, $classifieds, 'category_permission_cache');

        $classifieds = $classifiedModel->filterUnviewableClassifieds($classifieds);
        $classifieds = $classifiedModel->prepareClassifieds($classifieds);
        $inlineModOptions = $classifiedModel->getInlineModOptionsForClassifieds($classifieds);
        $output = array();

        foreach ($classifiedIds as $classifiedId)
        {
            if (isset($classifieds[$classifiedId]))
            {
                $output[$classifiedId] = $classifieds[$classifiedId];
            }
        }

        $classifieds = $output;
        $resultStartOffset = ($page - 1) * $perPage + 1;
        $resultEndOffset = ($page - 1) * $perPage + count($classifiedIds);

        $viewParams = array(
            'search' => $search,
            'classifieds' => $classifieds,
            'inlineModOptions' => $inlineModOptions,

            'startOffset' => $resultStartOffset,
            'endOffset' => $resultEndOffset,

            'ignoredNames' => $this->_getIgnoredContentUserNames($classifieds),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $search['result_count'],
            'nextPage' => ($resultEndOffset < $search['result_count'] ? ($page + 1) : 0)
        );

        return $this->getFindNewWrapper($this->responseView(
            'GFNClassifieds_ViewPublic_FindNew_Classifieds',
            'find_new_classifieds', $viewParams
        ), 'classifieds');
    }

    public function findNewClassifieds()
    {
        $classifiedModel = $this->_getClassifiedModel();
        $searchModel = $this->_getSearchModel();
        $visitor = XenForo_Visitor::getInstance();

        $cutOff = XenForo_Application::$time - XenForo_Application::getOptions()->get('readMarkingDataLifetime') * 86400;

        $classifieds = $classifiedModel->getClassifieds(array(
            'last_update' => array('>', $cutOff),
            'moderated' => false,
            'deleted' => false
        ), array(
            'join' => $classifiedModel::FETCH_CATEGORY | $classifiedModel::FETCH_USER,
            'permissionCombinationId' => $visitor->get('permission_combination_id'),
            'limit' => XenForo_Application::getOptions()->get('maximumSearchResults')
        ));

        $this->_getClassifiedCategoryModel()->bulkSetCategoryPermCache(null, $classifieds, 'category_permission_cache');

        $classifieds = $classifiedModel->filterUnviewableClassifieds($classifieds);
        $searchType = 'new-classifieds';
        $results = array();

        foreach ($classifieds as $classified)
        {
            $results[] = array(
                XenForo_Model_Search::CONTENT_TYPE => 'classified',
                XenForo_Model_Search::CONTENT_ID => $classified['classified_id']
            );
        }

        if (!$results)
        {
            return $this->getNoClassifiedsResponse();
        }

        $search = $searchModel->insertSearch($results, $searchType, '', array(), 'date', false);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('find-new/classifieds', $search)
        );
    }

    public function getNoClassifiedsResponse()
    {
        $this->getRouteMatch()->setSections('classifieds');

        return $this->getFindNewWrapper($this->responseView(
            'GFNClassifieds_ViewPublic_FindNew_ClassifiedsNone',
            'find_new_classifieds_none'
        ), 'classifieds');
    }

    protected function _getWrapperTabs()
    {
        $tabs = parent::_getWrapperTabs();

        if (!XenForo_Visitor::getInstance()->hasPermission('classifieds', 'view'))
        {
            return $tabs;
        }

        $i = 0;

        foreach ($tabs as $tabId => $tab)
        {
            $i++;

            if ($tabId == 'posts')
            {
                break;
            }
        }

        return array_merge(array_slice($tabs, 0, $i), array(
            'classifieds' => array(
                'href' => $this->_buildLink('find-new/classifieds'),
                'title' => new XenForo_Phrase('new_classifieds')
            )
        ), array_slice($tabs, $i));
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Classified');
    }

    /**
     * @return GFNClassifieds_Model_Category
     */
    protected function _getClassifiedCategoryModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Category');
    }
}