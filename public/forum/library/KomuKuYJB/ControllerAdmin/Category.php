<?php /*a70ae509a16735b2bffa357ca6fa1c301453522c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerAdmin_Category extends KomuKuYJB_ControllerAdmin_Abstract
{
    protected function _getAdminPermission()
    {
        return 'classifiedCategory';
    }

    public function actionList()
    {
        $categories = $this->models()->category()->getAllCategories();

        $moderatorsGrouped = array();
        $moderators = $this->_getModeratorModel()->getContentModerators(
            array('content' => 'classified_category')
        );

        foreach ($moderators AS $moderator)
        {
            $moderatorsGrouped[$moderator['content_id']][] = $moderator;
        }

        foreach ($categories as &$category)
        {
            if (isset($moderatorsGrouped[$category['category_id']]))
            {
                $category['moderators'] = $moderatorsGrouped[$category['category_id']];
            }
            else
            {
                $category['moderators'] = array();
            }

            $category['moderatorCount'] = count($category['moderators']);
        }

        $permissionSets = $this->_getPermissionModel()->getUserCombinationsWithContentPermissions('classified_category');
        $categoriesWithPerms = array();

        foreach ($permissionSets AS $set)
        {
            $categoriesWithPerms[$set['content_id']] = true;
        }

        $viewParams = array(
            'categories' => $categories,
            'categoriesWithPerms' => $categoriesWithPerms
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_Category_List', 'classifieds_category_list', $viewParams);
    }

    public function actionAdd()
    {
        if (!$this->models()->advertType()->getAllAdvertTypes() || !$this->models()->package()->getAllPackages())
        {
            return $this->responseError(new XenForo_Phrase('you_need_to_have_at_least_one_advert_type_and_one_package'));
        }

        return $this->_getAddEditResponse(array(
            'category_id' => null,
            'title' => '',
            'description' => '',
            'parent_category_id' => 0,
            'display_order' => 1,
            'advert_type_cache' => array(),
            'require_location' => false,
            'require_prefix' => false,
            'thread_node_id' => 0,
            'thread_prefix_id' => 0,
            'complete_thread_prefix_id' => 0,
            'package_cache' => array(),
            'enable_comment' => true
        ));
    }

    public function actionEdit()
    {
        return $this->_getAddEditResponse($this->_getCategoryOrError());
    }

    protected function _getAddEditResponse(array $category, array $viewParams = array())
    {
        $model = $this->models()->category();
        $fieldModel = $this->models()->field();
        $prefixModel = $this->models()->prefix();
        $advertTypeModel = $this->models()->advertType();
        $packageModel = $this->models()->package();

        if (empty($category['category_id']))
        {
            $selectedFields = array();
            $categoryPrefixes = array();
            $parentCategoryOptions = $model->getAllCategories();
        }
        else
        {
            $selectedFields = $this->models()->association()->field()->getAssociationByCategory($category['category_id']);
            $categoryPrefixes = $this->models()->association()->prefix()->getAssociationByCategory($category['category_id']);
            $parentCategoryOptions = $model->getPossibleParentCategories($category);
        }

        if (empty($category['thread_node_id']))
        {
            $threadPrefixOptions = array();
        }
        else
        {
            $threadPrefixOptions = $this->getModelFromCache('XenForo_Model_ThreadPrefix')->getPrefixOptions(array(
                'node_id' => $category['thread_node_id']
            ));
        }

        $nodes = $this->getModelFromCache('XenForo_Model_Node')->getAllNodes();
        $threadNodeOptions = array();

        foreach ($nodes as $i => $n)
        {
            $threadNodeOptions[] = array(
                'value' => $i,
                'label' => $n['title'],
                'depth' => $n['depth'],
                'selected' => ($category['thread_node_id'] == $i),
                'disabled' => ($n['node_type_id'] != 'Forum')
            );
        }

        $fields = $fieldModel->getAllFields();
        $fieldModel->prepareFields($fields);

        $viewParams += array(
            'category' => $category,
            'parentCategoryOptions' => $model->getCategoryOptionArray($parentCategoryOptions, $category['parent_category_id']),

            'packageOptions' => $packageModel->getPackageOption(array_keys($category['package_cache'])),
            'advertTypeOptions' => $advertTypeModel->getAdvertTypeOptions(array_keys($category['advert_type_cache'])),

            'threadNodeOptions' => $threadNodeOptions,
            'threadPrefixOptions' => $threadPrefixOptions,

            'fieldsGrouped' => $fieldModel->groupFields($fields),
            'fieldGroups' => $fieldModel->getFieldGroups(),
            'selectedFields' => $selectedFields,

            'prefixGroups' => $prefixModel->getPrefixesByGroups(),
            'prefixOptions' => $prefixModel->getPrefixOptions(),
            'categoryPrefixes' => $categoryPrefixes
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_Category_Edit', 'classifieds_category_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $data = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'description' => XenForo_Input::STRING,
            'parent_category_id' => XenForo_Input::UINT,
            'display_order' => XenForo_Input::UINT,
            'thread_node_id' => XenForo_Input::UINT,
            'thread_prefix_id' => XenForo_Input::UINT,
            'complete_thread_prefix_id' => XenForo_Input::UINT,
            'require_prefix' => XenForo_Input::BOOLEAN,
            'require_location' => XenForo_Input::BOOLEAN,
            'enable_comment' => XenForo_Input::BOOLEAN
        ));

        $extra = $this->_input->filter(array(
            'allowed_advert_types' => array(XenForo_Input::UINT, 'array' => true),
            'available_fields' => array(XenForo_Input::STRING, 'array' => true),
            'available_prefixes' => array(XenForo_Input::UINT, 'array' => true),
            'available_packages' => array(XenForo_Input::UINT, 'array' => true)
        ));

        /** @var KomuKuYJB_DataWriter_Category $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Category');

        if ($existing = $this->_input->filterSingle('category_id', XenForo_Input::UINT))
        {
            $writer->setExistingData($existing);
        }

        $writer->bulkSet($data);
        $writer->setExtraData($writer::DATA_ADVERT_TYPES, $extra['allowed_advert_types']);
        $writer->setExtraData($writer::DATA_FIELDS, $extra['available_fields']);
        $writer->setExtraData($writer::DATA_PREFIXES, $extra['available_prefixes']);
        $writer->setExtraData($writer::DATA_PACKAGES, $extra['available_packages']);
        $writer->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/categories') . $this->getLastHash($writer->get('category_id'))
        );
    }

    public function actionDelete()
    {
        $category = $this->_getCategoryOrError();

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Category');
            $writer->setExistingData($category, true);
            $writer->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/categories')
            );
        }

        $viewParams = array(
            'category' => $category
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_Category_Delete', 'classifieds_category_delete', $viewParams);
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
     * @return XenForo_Model_Permission
     */
    protected function _getPermissionModel()
    {
        return $this->getModelFromCache('XenForo_Model_Permission');
    }

    /**
     * @return XenForo_Model_Moderator
     */
    protected function _getModeratorModel()
    {
        return $this->getModelFromCache('XenForo_Model_Moderator');
    }
} 