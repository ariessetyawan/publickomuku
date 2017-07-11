<?php

class KomuKu_featuredmembers_WidgetRenderer_VerifiedMembers extends WidgetFramework_WidgetRenderer
{
    public function extraPrepareTitle(array $widget)
    {
        if (empty($widget['title'])) {
            return new XenForo_Phrase('dad_fm_vm');
        }

        return parent::extraPrepareTitle($widget);
    }

    protected function _getConfiguration()
    {
        return array(
            'name' => '[HA] Verified Members',
            'options' => array(
                'limit' => XenForo_Input::UINT,
                'displayMode' => XenForo_Input::STRING,
            ),
            'useCache' => true,
            'cacheSeconds' => 86400, // cache for a day
        );
    }

    protected function _getOptionsTemplate()
    {
        return 'dad_fm_widget_options';
    }

    protected function _getRenderTemplate(array $widget, $positionCode, array $params)
    {
        return 'dad_fm_widget';
    }

    protected function _render(
        array $widget,
        $positionCode,
        array $params,
        XenForo_Template_Abstract $renderTemplateObject
    ) {
        if (!isset($widget['options']['limit'])) {
            $widget['options']['limit'] = 0;
        }

        /** @var WidgetFramework_Model_User $wfUserModel */
        $wfUserModel = WidgetFramework_Core::getInstance()->getModelFromCache('WidgetFramework_Model_User');
        $userIds = $wfUserModel->getUserIds(array('dad_fm_is_verified' => true), array(
            'limit' => $widget['options']['limit'],
            'order' => 'username',
        ));
        $users = $wfUserModel->getUsersByIdsInOrder($userIds, XenForo_Model_User::FETCH_USER_FULL);

        $renderTemplateObject->setParam('users', $users);

        return $renderTemplateObject->render();
    }

}
