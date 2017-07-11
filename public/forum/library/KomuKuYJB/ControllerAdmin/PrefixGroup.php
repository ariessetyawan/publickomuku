<?php /*0a66e7ba618f703295e700ed62f9add99f079e74*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerAdmin_PrefixGroup extends KomuKuYJB_ControllerAdmin_Abstract
{
    protected function _getAdminPermission()
    {
        return 'classifiedPrefix';
    }

    public function actionIndex()
    {
        return $this->getNotFoundResponse();
    }

    public function actionAdd()
    {
        return $this->_getAddEditResponse(array(
            'prefix_group_id' => null,
            'display_order' => 1
        ));
    }

    public function actionEdit()
    {
        return $this->_getAddEditResponse($this->_getPrefixGroupOrError());
    }

    protected function _getAddEditResponse(array $group, array $viewParams = array())
    {
        if (empty($group['prefix_group_id']))
        {
            $masterTitle = '';
        }
        else
        {
            $masterTitle = $this->models()->prefix()->getPrefixGroupMasterTitlePhraseValue($group['prefix_group_id']);
        }

        $viewParams += array(
            'group' => $group,
            'masterTitle' => $masterTitle
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_PrefixGroup_Edit', 'classifieds_prefix_group_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $data = $this->_input->filter(array(
            'display_order' => XenForo_Input::UINT,
            'title' => XenForo_Input::STRING
        ));

        /** @var KomuKuYJB_DataWriter_PrefixGroup $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_PrefixGroup');

        if ($existing = $this->_input->filterSingle('prefix_group_id', XenForo_Input::UINT))
        {
            $writer->setExistingData($existing);
        }

        $writer->set('display_order', $data['display_order']);
        $writer->setExtraData($writer::DATA_TITLE, $data['title']);
        $writer->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/prefixes') . $this->getLastHash('group_' . $writer->get('prefix_group_id'))
        );
    }

    public function actionDelete()
    {
        $group = $this->_getPrefixGroupOrError();

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_PrefixGroup');
            $writer->setExistingData($group, true);
            $writer->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/prefixes')
            );
        }

        $viewParams = array(
            'group' => $group
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_PrefixGroup_Delete', 'classifieds_prefix_group_delete', $viewParams);
    }

    protected function _getPrefixGroupOrError($groupId = null)
    {
        if ($groupId === null)
        {
            $groupId = $this->_input->filterSingle('prefix_group_id', XenForo_Input::UINT);
        }

        $group = $this->models()->prefix()->getPrefixGroupById($groupId);
        if (!$group)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_prefix_group_not_found');
        }

        return $this->models()->prefix()->preparePrefixGroup($group);
    }
} 