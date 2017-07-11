<?php /*0bcb3a5223106242d68a1b52c0bad1f72dd140e7*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_TraderRatingCriteria extends GFNClassifieds_ControllerAdmin_Abstract
{
    protected function _getAdminPermission()
    {
        return 'classifiedTraderRatingCri';
    }

    public function actionList()
    {
        $criterias = $this->models()->ratingCriteria()->getAllRatingCriterias();
        $criterias = $this->models()->ratingCriteria()->prepareRatingCriterias($criterias);

        $viewParams = array(
            'criterias' => $criterias
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_RatingCriteria_List', 'classifieds_rating_criteria_list', $viewParams);
    }

    public function actionAdd()
    {
        return $this->_getAddEditResponse(array(
            'criteria_id' => null,
            'display_order' => 1,
            'required' => false,
            'is_global' => false,
            'show_message' => true,
            'require_message' => false
        ));
    }

    public function actionEdit()
    {
        return $this->_getAddEditResponse($this->_getRatingCriteriaOrError());
    }

    protected function _getAddEditResponse(array $criteria, array $viewParams = array())
    {
        $model = $this->models()->ratingCriteria();

        if (empty($criteria['criteria_id']))
        {
            $selCategoryIds = array();
            $masterTitle = '';
            $masterDescription = '';
        }
        else
        {
            $selCategoryIds = $this->models()->association()->ratingCriteria()->getAssociationByRatingCriteria($criteria['criteria_id']);

            $masterTitle = $model->getRatingCriteriaMasterTitlePhraseValue($criteria['criteria_id']);
            $masterDescription = $model->getRatingCriteriaMasterDescriptionPhraseValue($criteria['criteria_id']);
        }

        $viewParams += array(
            'criteria' => $criteria,
            'masterTitle' => $masterTitle,
            'masterDescription' => $masterDescription,

            'categories' => $this->models()->category()->getAllCategories(),
            'selCategoryIds' => $selCategoryIds
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_RatingCriteria_Edit', 'classifieds_rating_criteria_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $data = $this->_input->filter(array(
            'display_order' => XenForo_Input::UINT,
            'required' => XenForo_Input::BOOLEAN,
            'is_global' => XenForo_Input::BOOLEAN,
            'show_message' => XenForo_Input::BOOLEAN,
            'require_message' => XenForo_Input::BOOLEAN
        ));

        $extra = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'description' => array(XenForo_Input::STRING, 'noTrim' => true),
            'applicable_categories' => array(XenForo_Input::UINT, 'array' => true)
        ));

        $criteriaId = $this->_input->filterSingle('criteria_id', XenForo_Input::STRING);
        $newCriteriaId = $this->_input->filterSingle('new_criteria_id', XenForo_Input::STRING);

        /** @var GFNClassifieds_DataWriter_TraderRatingCriteria $writer */
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRatingCriteria');

        if ($criteriaId)
        {
            $writer->setExistingData($criteriaId);
        }
        else
        {
            $writer->set('criteria_id', $newCriteriaId);
        }

        $writer->bulkSet($data);

        $writer->setExtraData($writer::DATA_TITLE, $extra['title']);
        $writer->setExtraData($writer::DATA_DESCRIPTION, $extra['description']);
        $writer->setExtraData($writer::DATA_CATEGORIES, $extra['applicable_categories']);

        $writer->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/traders/ratings/criteria') . $this->getLastHash($writer->get('criteria_id'))
        );
    }

    public function actionDelete()
    {
        $criteria = $this->_getRatingCriteriaOrError();

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRatingCriteria');
            $writer->setExistingData($criteria, true);
            $writer->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/traders/ratings/criteria')
            );
        }
        else
        {
            $viewParams = array(
                'criteria' => $criteria,
                'masterTitle' => $this->models()->ratingCriteria()->getRatingCriteriaMasterTitlePhraseValue($criteria['criteria_id'])
            );

            return $this->responseView('GFNClassifieds_ViewAdmin_RatingCriteria_Delete', 'classifieds_rating_criteria_delete', $viewParams);
        }
    }

    protected function _getRatingCriteriaOrError($criteriaId = null)
    {
        if ($criteriaId === null)
        {
            $criteriaId = $this->_input->filterSingle('criteria_id', XenForo_Input::STRING);
        }

        $criteria = $this->models()->ratingCriteria()->getRatingCriteriaById($criteriaId);
        if (!$criteria)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_rating_criteria_not_found');
        }

        return $criteria;
    }
}