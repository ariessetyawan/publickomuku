<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class GFNCore_ControllerAdmin_SystemOption extends XenForo_ControllerAdmin_Abstract
{
    /**
     * @return string
     */
    abstract protected function _getOptionGroupId();

    /**
     * @return array
     */
    abstract protected function _getGroupedOptions();

    /**
     * @return string
     */
    abstract protected function _getRoutePrefixLink();

    public function actionIndex()
    {
        $pageId = $this->_input->filterSingle('page_id', XenForo_Input::STRING);
        $options = $this->_getGroupedOptions();

        if ($pageId == 'save')
        {
            return $this->responseReroute(get_called_class(), 'save');
        }

        if (empty($pageId))
        {
            $pageId = key($options);
        }

        if (!isset($options[$pageId]))
        {
            return $this->getNotFoundResponse();
        }

        /** @var XenForo_Model_Option $model */
        $model = $this->getModelFromCache('XenForo_Model_Option');
        $fetchOptions = array('join' => XenForo_Model_Option::FETCH_ADDON);

        $group = $model->getOptionGroupById($this->_getOptionGroupId(), $fetchOptions);
        if (!$group)
        {
            return $this->responseNoPermission();
        }

        $pages = array();

        foreach (array_keys($options) as $i)
        {
            $pages[$i] = array(
                'title' => new XenForo_Phrase(sprintf('%s_option_page_%s', $group['group_id'], $i)),
                'link' => $this->_buildLink($this->_getRoutePrefixLink(), array('page_id' => $i))
            );
        }

        $allowedOptions = $options[$pageId];
        $options = $model->getOptionsInGroup($group['group_id'], $fetchOptions);
        
        foreach ($options as $optionId => $option)
        {
            if (!in_array($optionId, $allowedOptions))
            {
                unset ($options[$optionId]);
            }
        }

        $canEdit = $model->canEditOptionAndGroupDefinitions();

        $viewParams = array(
            'pages' => $pages,
            'page' => $pages[$pageId],
            'group' => $group,
            'preparedOptions' => $model->prepareOptions($options, false),
            'canEditGroup' => $model->canEditOptionAndGroupDefinitions(),
            'canEditOptionDefinition' => $canEdit,
            'saveLink' => $this->_buildLink($this->_getRoutePrefixLink() . '/save')
        );

        return $this->responseView('GFNCore_ViewAdmin_SystemOption_View', 'gfncore_system_option_view', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $input = $this->_input->filter(array(
            'options' => XenForo_Input::ARRAY_SIMPLE,
            'options_listed' => array(XenForo_Input::STRING, array('array' => true))
        ));

        foreach ($input['options_listed'] AS $optionName)
        {
            if (!isset($input['options'][$optionName]))
            {
                $input['options'][$optionName] = '';
            }
        }

        /** @var XenForo_Model_Option $optionModel */
        $optionModel = $this->getModelFromCache('XenForo_Model_Option');
        $optionModel->updateOptions($input['options']);

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->getDynamicRedirect($this->_buildLink($this->_getRoutePrefixLink()))
        );
    }
}