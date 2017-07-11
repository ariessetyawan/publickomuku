<?php /*2efaaca2a95cccb06a4a2e0ab825b24d31fca4e4*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Search_DataHandler_Classified extends XenForo_Search_DataHandler_Abstract
{
    protected $_classifiedModel;

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        if (!$this->_classifiedModel)
        {
            $this->_classifiedModel = XenForo_Model::create('GFNClassifieds_Model_Classified');
        }

        return $this->_classifiedModel;
    }

    protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
    {
        if (!empty($data['tag_line']))
        {
            $message = $data['tag_line'] . ' ' . $data['description'];
        }
        else
        {
            $message = $data['description'];
        }

        $metadata = array();
        $metadata['classifiedcat'] = $data['category_id'];
        $metadata['classifiedtype'] = $data['advert_type_id'];

        if (!empty($data['prefix_id']))
        {
            $metadata['classifiedprefix'] = $data['prefix_id'];
        }

        if (!empty($data['country']))
        {
            $metadata['classifiedcountry'] = $data['country'];
        }

        if (isset($data['latitude']))
        {
            $message .= ' ' . $this->_getClassifiedModel()->getLocationStreetAddress($data, true);
        }

        $indexer->insertIntoIndex(
            'classified', $data['classified_id'], $data['title'], $message,
            $data['classified_date'], $data['user_id'], $data['classified_id'], $metadata
        );
    }

    protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
    {
        $indexer->updateIndex('classified', $data['classified_id'], $fieldUpdates);
    }

    protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
    {
        $classifiedIds = array();

        foreach ($dataList as $data)
        {
            if (is_array($data))
            {
                $classifiedIds[] = $data['classified_id'];
            }
            else
            {
                $classifiedIds[] = $data;
            }
        }

        $indexer->deleteFromIndex('classified', $classifiedIds);
    }

    public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
    {
        $classifiedIds = $this->_getClassifiedModel()->getClassifiedIdsInRange($lastId, $batchSize);
        if (!$classifiedIds)
        {
            return false;
        }

        $this->quickIndex($indexer, $classifiedIds);
        return max($classifiedIds);
    }

    public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
    {
        $classifieds = $this->_getClassifiedModel()->getClassifiedsByIds($contentIds, array(
            'join' => GFNClassifieds_Model_Classified::FETCH_CATEGORY
                      | GFNClassifieds_Model_Classified::FETCH_LOCATION
        ));

        foreach ($classifieds as $classified)
        {
            $this->insertIntoIndex($indexer, $classified);
        }

        return true;
    }

    public function getInlineModConfiguration()
    {
        return array(
            'name' => new XenForo_Phrase('classifieds'),
            'route' => 'classifieds/inline-mod/switch',
            'cookie' => 'classifieds',
            'template' => 'inline_mod_controls_classified'
        );
    }

    public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
    {
        $model = $this->_getClassifiedModel();

        $classifieds = $model->getClassifiedsByIds($ids, array(
            'join' => GFNClassifieds_Model_Classified::FETCH_CATEGORY
                      | GFNClassifieds_Model_Classified::FETCH_USER
                      | GFNClassifieds_Model_Classified::FETCH_LOCATION,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        return $model->unserializePermissionsInList($classifieds, 'category_permission_cache');
    }

    public function canViewResult(array $result, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canViewClassifiedAndContainer($result, $result, $null, $viewingUser, $result['permissions']);
    }

    public function prepareResult(array $result, array $viewingUser)
    {
        return $this->_getClassifiedModel()->prepareClassified($result, $result, $viewingUser);
    }

    public function addInlineModOption(array &$result)
    {
        return $this->_getClassifiedModel()->addInlineModOptionToClassified($result, $result, null, $result['permissions']);
    }

    public function getResultDate(array $result)
    {
        return $result['classified_date'];
    }

    public function renderResult(XenForo_View $view, array $result, array $search)
    {
        return $view->createTemplateObject('search_result_classified', array(
            'classified' => $result,
            'search' => $search,
            'enableInlineMod' => isset($this->_inlineModEnabled) ? $this->_inlineModEnabled : false
        ));
    }

    public function getTypeConstraintsFromInput(XenForo_Input $input)
    {
        $constraints = array();

        $categories = $input->filterSingle('categories', XenForo_Input::UINT, array('array' => true));
        if ($categories && !in_array(0, $categories))
        {
            if ($input->inRequest('child_categories'))
            {
                $includeChildren = $input->filterSingle('child_categories', XenForo_Input::UINT);
            }
            else
            {
                $includeChildren = true;
            }

            if ($includeChildren)
            {
                /** @var GFNClassifieds_Model_Category $categoryModel */
                $categoryModel = XenForo_Model::create('GFNClassifieds_Model_Category');
                $descendants = array_keys($categoryModel->getDescendantsOfCategoryIds($categories));
                $categories = array_merge($categories, $descendants);
            }

            $categories = array_unique($categories);
            $constraints['classifiedcat'] = implode(' ', $categories);
            if (!$constraints['classifiedcat'])
            {
                unset ($constraints['classifiedcat']);
            }
        }

        $prefixes = $input->filterSingle('prefixes', XenForo_Input::UINT, array('array' => true));
        if ($prefixes && reset($prefixes))
        {
            $prefixes = array_unique($prefixes);
            $constraints['classifiedprefix'] = implode(' ', $prefixes);
            if (!$constraints['classifiedprefix'])
            {
                unset ($constraints['classifiedprefix']);
            }
        }

        $advertTypes = $input->filterSingle('advert_types', XenForo_Input::UINT, array('array' => true));
        if ($advertTypes && reset($advertTypes))
        {
            $advertTypes = array_unique($advertTypes);
            $constraints['classifiedtype'] = implode(' ', $advertTypes);
            if (!$constraints['classifiedtype'])
            {
                unset ($constraints['classifiedtype']);
            }
        }

        $countries = $input->filterSingle('countries', XenForo_Input::STRING, array('array' => true));
        if ($countries && reset($countries))
        {
            $countries = array_unique($countries);

            foreach ($countries as $k => &$v)
            {
                if (strlen($v) <> 2)
                {
                    unset ($countries[$k]);
                }

                $v = strtoupper($v);
            }

            $constraints['classifiedcountry'] = implode(' ', $countries);
            if (!$constraints['classifiedcountry'])
            {
                unset ($constraints['classifiedcountry']);
            }
        }

        return $constraints;
    }

    public function processConstraint(XenForo_Search_SourceHandler_Abstract $sourceHandler, $constraint, $constraintInfo, array $constraints)
    {
        switch ($constraint)
        {
            case 'classifiedcat':
                if ($constraintInfo)
                {
                    return array(
                        'metadata' => array('classifiedcat', preg_split('/\D+/', strval($constraintInfo)))
                    );
                }
                break;

            case 'classifiedprefix':
                if ($constraintInfo)
                {
                    return array(
                        'metadata' => array('classifiedprefix', preg_split('/\D+/', strval($constraintInfo)))
                    );
                }
                break;

            case 'classifiedtype':
                if ($constraintInfo)
                {
                    return array(
                        'metadata' => array('classifiedtype', preg_split('/\D+/', strval($constraintInfo)))
                    );
                }
                break;

            case 'classifiedcountry':
                if ($constraintInfo)
                {
                    return array(
                        'metadata' => array('classifiedcountry', preg_split('/[^A-Z]+/', strval($constraintInfo)))
                    );
                }
                break;
        }

        return false;
    }

    public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
    {
        if (!XenForo_Application::isRegistered('showClassifiedSearchForm'))
        {
            return $controller->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('classifieds/search')
            );
        }

        /** @var GFNClassifieds_Model_Classified $classifiedModel */
        $classifiedModel = XenForo_Model::create('GFNClassifieds_Model_Classified');
        if (!$classifiedModel->canViewClassifieds())
        {
            return $controller->responseNoPermission();
        }

        $params = $input->filterSingle('c', XenForo_Input::ARRAY_SIMPLE);

        if (!empty($params['classifiedcat']))
        {
            $viewParams['search']['categories'] = array_fill_keys(explode(' ', $params['classifiedcat']), true);
        }
        else
        {
            $viewParams['search']['categories'] = array();
        }

        /** @var GFNClassifieds_Model_Category $categoryModel */
        $categoryModel = XenForo_Model::create('GFNClassifieds_Model_Category');
        $viewParams['search']['child_categories'] = true;
        $viewParams['categories'] = $categoryModel->getViewableCategories();

        if (!empty($params['prefix']))
        {
            $viewParams['search']['prefixes'] = array_fill_keys(explode(' ', $params['prefix']), true);
        }
        else
        {
            $viewParams['search']['prefixes'] = array();
        }

        /** @var GFNClassifieds_Model_Prefix $prefixModel */
        $prefixModel = XenForo_Model::create('GFNClassifieds_Model_Prefix');
        $viewParams['prefixes'] = $prefixModel->getPrefixesByGroups();

        if ($viewParams['prefixes'])
        {
            $visiblePrefixes = $prefixModel->getVisiblePrefixIds();

            foreach ($viewParams['prefixes'] AS $key => $prefixes)
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
                    unset ($viewParams['prefixes'][$key]);
                }
            }
        }

        if (!empty($params['type']))
        {
            $viewParams['search']['advertTypes'] = array_fill_keys(explode(' ', $params['type']), true);
        }
        else
        {
            $viewParams['search']['advertTypes'] = array();
        }

        /** @var GFNClassifieds_Model_AdvertType $advertTypeModel */
        $advertTypeModel = XenForo_Model::create('GFNClassifieds_Model_AdvertType');
        $viewParams['advertTypes'] = $advertTypeModel->getAdvertTypes(array());

        if ($viewParams['advertTypes'])
        {
            $visibleAdvertTypes = $advertTypeModel->getVisibleAdvertTypeIds();

            foreach ($viewParams['advertTypes'] as $advertTypeId => $advertType)
            {
                if (!isset($visibleAdvertTypes[$advertTypeId]))
                {
                    unset ($viewParams['advertTypes'][$advertTypeId]);
                }
            }

            if ($viewParams['advertTypes'])
            {
                $advertTypeModel->prepareAdvertTypes($viewParams['advertTypes']);
            }
        }

        if (!empty($params['country']))
        {
            $viewParams['search']['countries'] = array_fill_keys(explode(' ', $params['country']), true);
        }
        else
        {
            $viewParams['search']['countries'] = array();
        }

        $viewParams['countries'] = array();

        if (!empty($viewParams['categories']))
        {
            foreach ($classifiedModel->getAvailableCountryCodesForSearch(array_keys($viewParams['categories'])) as $code)
            {
                $viewParams['countries'][$code] = GFNCore_Helper_Country::getCountryByCode($code);
            }
        }

        return $controller->responseView('GFNClassifieds_ViewPublic_Search_Form_Classified', 'search_form_classified', $viewParams);
    }

    public function getSearchContentTypes()
    {
        return array('classified');
    }
}