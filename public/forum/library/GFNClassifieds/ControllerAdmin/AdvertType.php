<?php /*411348e4c48609acf822f26e5557caa46791f9ed*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 9
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_AdvertType extends GFNClassifieds_ControllerAdmin_Abstract
{
    protected function _getAdminPermission()
    {
        return 'classifiedAdvertType';
    }

    public function actionList()
    {
        $model = $this->models()->advertType();
        $advertTypes = $model->getAllAdvertTypes();
        $model->prepareAdvertTypes($advertTypes);

        $viewParams = array(
            'types' => $advertTypes
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_AdvertType_List', 'classifieds_advert_type_list', $viewParams);
    }

    public function actionAdd()
    {
        return $this->_getAddEditResponse(array(
            'advert_type_id' => null,
            'badge_color' => '',
            'complete_badge_color' => '',
            'show_badge' => true,
            'display_order' => 1
        ));
    }

    public function actionEdit()
    {
        return $this->_getAddEditResponse($this->_getAdvertTypeOrError());
    }

    protected function _getAddEditResponse(array $advertType, array $viewParams = array())
    {
        $model = $this->models()->advertType();

        if (empty($advertType['advert_type_id']))
        {
            $masterTitle = '';
            $masterZeroValueText = '';
            $masterCompleteText = 'Completed';
            $selCategoryIds = array();
        }
        else
        {
            $masterTitle = $model->getAdvertTypeMasterTitlePhraseValue($advertType['advert_type_id']);
            $masterZeroValueText = $model->getZeroValueTextMasterTitlePhraseValue($advertType['advert_type_id']);
            $masterCompleteText = $model->getCompleteTextMasterTitlePhraseValue($advertType['advert_type_id']);
            $selCategoryIds = $this->models()->association()->advertType()->getAssociationByAdvertType($advertType['advert_type_id']);
        }

        $viewParams += array(
            'type' => $advertType,
            'masterTitle' => $masterTitle,
            'masterZeroValueText' => $masterZeroValueText,
            'masterCompleteText' => $masterCompleteText,

            'categories' => $this->models()->category()->getAllCategories(),
            'selCategoryIds' => $selCategoryIds,
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_AdvertType_Edit', 'classifieds_advert_type_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $data = $this->_input->filter(array(
            'badge_color' => XenForo_Input::STRING,
            'complete_badge_color' => XenForo_Input::STRING,
            'show_badge' => XenForo_Input::BOOLEAN,
            'display_order' => XenForo_Input::UINT
        ));

        $extra = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'zero_value_text' => XenForo_Input::STRING,
            'complete_text' => XenForo_Input::STRING,
            'applicable_categories' => array(XenForo_Input::UINT, 'array' => true)
        ));

        /** @var GFNClassifieds_DataWriter_AdvertType $writer */
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_AdvertType');

        if ($existing = $this->_input->filterSingle('advert_type_id', XenForo_Input::UINT))
        {
            $writer->setExistingData($existing);
        }

        $writer->bulkSet($data);
        $writer->setExtraData($writer::DATA_TITLE, $extra['title']);
        $writer->setExtraData($writer::DATA_ZERO_VALUE_TEXT, $extra['zero_value_text']);
        $writer->setExtraData($writer::DATA_COMPLETE_TEXT, $extra['complete_text']);
        $writer->setExtraData($writer::DATA_CATEGORIES, $extra['applicable_categories']);
        $writer->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/advert-types') . $this->getLastHash($writer->get('advert_type_id'))
        );
    }

    public function actionDelete()
    {
        $advertType = $this->_getAdvertTypeOrError();

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_AdvertType');
            $writer->setExistingData($advertType, true);
            $writer->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/advert-types')
            );
        }

        $viewParams = array(
            'type' => $this->models()->advertType()->prepareAdvertType($advertType)
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_AdvertType_Delete', 'classifieds_advert_type_delete', $viewParams);
    }

    protected function _getAdvertTypeOrError($advertTypeId = null)
    {
        if ($advertTypeId === null)
        {
            $advertTypeId = $this->_input->filterSingle('advert_type_id', XenForo_Input::UINT);
        }

        $advertType = $this->models()->advertType()->getAdvertTypeById($advertTypeId);
        if (!$advertType)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_advert_type_not_found');
        }

        return $advertType;
    }
}