<?php /*3c2c61b47437c06e24333b7080d04be52ae3b912*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_Prefix extends GFNClassifieds_ControllerAdmin_Abstract
{
    protected function _getAdminPermission()
    {
        return 'classifiedPrefix';
    }

    public function actionList()
    {
        $model = $this->models()->prefix();

        $prefixGroups = $model->getAllPrefixGroups();
        $prefixes = $model->getPrefixesByGroups(array(), array(), $prefixCount);
        $prefixGroups = $model->mergePrefixesIntoGroups($prefixes, $prefixGroups);

        $viewParams = array(
            'prefixGroups' => $prefixGroups,
            'prefixCount' => $prefixCount
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_Prefix_List', 'classifieds_prefix_list', $viewParams);
    }

    public function actionAdd()
    {
        return $this->_getAddEditResponse(array(
            'prefix_id' => null,
            'css_class' => 'prefix prefixPrimary',
            'prefix_group_id' => 0,
            'display_order' => 1
        ));
    }

    public function actionEdit()
    {
        return $this->_getAddEditResponse($this->_getPrefixOrError());
    }

    protected function _getAddEditResponse(array $prefix, array $viewParams = array())
    {
        $model = $this->models()->prefix();

        /** @var XenForo_Model_UserGroup $userGroupModel */
        $userGroupModel = $this->getModelFromCache('XenForo_Model_UserGroup');
        $userGroups = $userGroupModel->getAllUserGroups();

        if (empty($prefix['prefix_id']))
        {
            $selCategoryIds = array();
            $allUserGroups = true;
            $selUserGroupIds = array_keys($userGroups);
            $masterTitle = '';
        }
        else
        {
            $selCategoryIds = $this->models()->association()->prefix()->getAssociationByPrefix($prefix['prefix_id']);

            $selUserGroupIds = explode(',', $prefix['allowed_user_group_ids']);
            if (in_array(-1, $selUserGroupIds))
            {
                $allUserGroups = true;
                $selUserGroupIds = array_keys($userGroups);
            }
            else
            {
                $allUserGroups = false;
            }

            $masterTitle = $this->models()->prefix()->getPrefixMasterTitlePhraseValue($prefix['prefix_id']);
        }

        $displayStyles = array(
            '',
            'prefix prefixPrimary',
            'prefix prefixSecondary',
            'prefix prefixGreen',
            'prefix prefixOlive',
            'prefix prefixLightGreen',
            'prefix prefixBlue',
            'prefix prefixRoyalBlue',
            'prefix prefixSkyBlue',
            'prefix prefixRed',
            'prefix prefixOrange',
            'prefix prefixYellow',
            'prefix prefixGray',
            'prefix prefixSilver',
        );

        $viewParams += array(
            'prefix' => $prefix,
            'prefixGroupOptions' => $model->getPrefixGroupOptions($prefix['prefix_group_id']),
            'masterTitle' => $masterTitle,

            'selCategoryIds' => $selCategoryIds,
            'allUserGroups' => $allUserGroups,
            'selUserGroupIds' => $selUserGroupIds,

            'displayStyles' => $displayStyles,
            'displayStylesOther' => !in_array($prefix['css_class'], $displayStyles),

            'categories' => $this->models()->category()->getAllCategories(),
            'userGroups' => $userGroups
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_Prefix_Edit', 'classifieds_prefix_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $data = $this->_input->filter(array(
            'prefix_group_id' => XenForo_Input::UINT,
            'display_order' => XenForo_Input::UINT,
            'css_class' => XenForo_Input::STRING
        ));

        $extra = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'applicable_categories' => array(XenForo_Input::UINT, 'array' => true)
        ));

        /** @var GFNClassifieds_DataWriter_Prefix $writer */
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Prefix');

        if ($existing = $this->_input->filterSingle('prefix_id', XenForo_Input::UINT))
        {
            $writer->setExistingData($existing);
        }

        if ($this->_input->filterSingle('usable_user_group_type', XenForo_Input::STRING) == 'all')
        {
            $allowedGroupIds = array(-1); // -1 is a sentinel for all groups
        }
        else
        {
            $allowedGroupIds = $this->_input->filterSingle('user_group_ids', XenForo_Input::UINT, array('array' => true));
        }

        $writer->bulkSet($data);
        $writer->set('allowed_user_group_ids', $allowedGroupIds);
        $writer->setExtraData($writer::DATA_TITLE, $extra['title']);
        $writer->setExtraData($writer::DATA_CATEGORIES, $extra['applicable_categories']);
        $writer->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/prefixes') . $this->getLastHash($writer->get('prefix_id'))
        );
    }

    public function actionDelete()
    {
        $prefix = $this->_getPrefixOrError();

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Prefix');
            $writer->setExistingData($prefix, true);
            $writer->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/prefixes')
            );
        }

        $viewParams = array(
            'prefix' => $prefix
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_Prefix_Delete', 'classifieds_prefix_delete', $viewParams);
    }

    protected function _getPrefixOrError($prefixId = null)
    {
        if ($prefixId === null)
        {
            $prefixId = $this->_input->filterSingle('prefix_id', XenForo_Input::UINT);
        }

        $prefix = $this->models()->prefix()->getPrefixById($prefixId);
        if (!$prefix)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_prefix_not_found');
        }

        return $this->models()->prefix()->preparePrefix($prefix);
    }
} 