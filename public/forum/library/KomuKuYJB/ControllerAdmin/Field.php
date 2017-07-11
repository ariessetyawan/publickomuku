<?php /*56c1920e96dfaa325f3d7976fe814efedf2d2ea8*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerAdmin_Field extends KomuKuYJB_ControllerAdmin_Abstract
{
    protected function _getAdminPermission()
    {
        return 'classifiedField';
    }

    public function actionList()
    {
        $model = $this->models()->field();
        $fields = $model->getAllFields();
        $model->prepareFields($fields);

        $viewParams = array(
            'fieldsGrouped' => $model->groupFields($fields),
            'fieldCount' => count($fields),
            'fieldGroups' => $model->getFieldGroups(),
            'fieldTypes' => $model->getFieldTypes()
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_Field_List', 'classifieds_field_list', $viewParams);
    }

    public function actionAdd()
    {
        return $this->_getAddEditResponse(array(
            'field_id' => null,
            'display_group' => 'above_info',
            'display_order' => 1,
            'field_type' => 'textbox',
            'field_choices' => '',
            'match_type' => 'none',
            'match_regex' => '',
            'match_callback_class' => '',
            'match_callback_method' => '',
            'max_length' => 0,
            'required' => 0,
            'display_template' => '',
            'can_be_filtered' => false,
            'include_in_thread_list' => false,
            'include_in_classified_list' => false,
            'include_in_classified_view' => true,
            'include_in_classified_editor' => true,
            'include_in_thread_view' => false,
            'hint_text' => ''
        ));
    }

    public function actionEdit()
    {
        return $this->_getAddEditResponse($this->_getFieldOrError());
    }

    protected function _getAddEditResponse(array $field, array $viewParams = array())
    {
        $model = $this->models()->field();

        $typeMap = $model->getFieldTypeMap();
        $validFieldTypes = $model->getFieldTypes();

        if (empty($field['field_id']))
        {
            $selCategoryIds = array();
            $masterTitle = '';
            $masterDescription = '';
            $existingType = false;
        }
        else
        {
            $selCategoryIds = $this->models()->association()->field()->getAssociationByField($field['field_id']);

            $masterTitle = $model->getFieldTitleMasterPhraseValue($field['field_id']);
            $masterDescription = $model->getFieldDescriptionMasterPhraseValue($field['field_id']);

            $existingType = $typeMap[$field['field_type']];
            foreach ($validFieldTypes AS $typeId => $type)
            {
                if ($typeMap[$typeId] != $existingType)
                {
                    unset($validFieldTypes[$typeId]);
                }
            }
        }

        $viewParams += array(
            'field' => $field,
            'masterTitle' => $masterTitle,
            'masterDescription' => $masterDescription,
            'masterFieldChoices' => $model->getFieldChoices($field['field_id'], $field['field_choices'], true),

            'fieldGroups' => $model->getFieldGroups(),
            'validFieldTypes' => $validFieldTypes,
            'fieldTypeMap' => $typeMap,
            'existingType' => $existingType,

            'categories' => $this->models()->category()->getAllCategories(),
            'selCategoryIds' => $selCategoryIds
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_Field_Edit', 'classifieds_field_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $data = $this->_input->filter(array(
            'display_group' => XenForo_Input::STRING,
            'display_order' => XenForo_Input::UINT,
            'field_type' => XenForo_Input::STRING,
            'match_type' => XenForo_Input::STRING,
            'match_regex' => XenForo_Input::STRING,
            'match_callback_class' => XenForo_Input::STRING,
            'match_callback_method' => XenForo_Input::STRING,
            'max_length' => XenForo_Input::UINT,
            'required' => XenForo_Input::UINT,
            'display_template' => XenForo_Input::STRING,
            'can_be_filtered' => XenForo_Input::BOOLEAN,
            'include_in_classified_list' => XenForo_Input::BOOLEAN,
            'include_in_thread_list' => XenForo_Input::BOOLEAN,
            'include_in_classified_view' => XenForo_Input::BOOLEAN,
            'include_in_classified_editor' => XenForo_Input::BOOLEAN,
            'include_in_thread_view' => XenForo_Input::BOOLEAN
        ));

        $extra = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'description' => array(XenForo_Input::STRING, 'noTrim' => true),
            'applicable_categories' => array(XenForo_Input::UINT, 'array' => true)
        ));

        $fieldId = $this->_input->filterSingle('field_id', XenForo_Input::STRING);
        $newFieldId = $this->_input->filterSingle('new_field_id', XenForo_Input::STRING);

        /** @var KomuKuYJB_DataWriter_Field $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Field');

        if ($fieldId)
        {
            $writer->setExistingData($fieldId);
        }
        else
        {
            $writer->set('field_id', $newFieldId);
        }

        $hintText = $this->getHelper('Editor')->getMessageText('hint_text', $this->_input);
        $hintText = XenForo_Helper_String::autoLinkBbCode($hintText);

        $writer->bulkSet($data);
        $writer->set('hint_text', $hintText);
        $writer->setExtraData($writer::DATA_TITLE, $extra['title']);
        $writer->setExtraData($writer::DATA_DESCRIPTION, $extra['description']);
        $writer->setExtraData($writer::DATA_CATEGORIES, $extra['applicable_categories']);

        $fieldChoices = $this->_input->filterSingle('field_choice', XenForo_Input::STRING, array('array' => true));
        $fieldChoicesText = $this->_input->filterSingle('field_choice_text', XenForo_Input::STRING, array('array' => true));
        $fieldChoicesCombined = array();

        foreach ($fieldChoices AS $key => $choice)
        {
            if (isset($fieldChoicesText[$key]))
            {
                $fieldChoicesCombined[$choice] = $fieldChoicesText[$key];
            }
        }

        $writer->setFieldChoices($fieldChoicesCombined);
        $writer->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/fields') . $this->getLastHash($writer->get('field_id'))
        );
    }

    public function actionDelete()
    {
        $field = $this->_getFieldOrError();

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Field');
            $writer->setExistingData($field, true);
            $writer->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/fields')
            );
        }

        $viewParams = array(
            'field' => $field
        );

        return $this->responseView('KomuKuYJB_ViewAdmin_Field_Delete', 'classifieds_field_delete', $viewParams);
    }

    protected function _getFieldOrError($fieldId = null)
    {
        if ($fieldId === null)
        {
            $fieldId = $this->_input->filterSingle('field_id', XenForo_Input::STRING);
        }

        $field = $this->models()->field()->getFieldById($fieldId);
        if (!$field)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_classified_field_not_found');
        }

        $this->models()->field()->prepareField($field);
        return $field;
    }
} 