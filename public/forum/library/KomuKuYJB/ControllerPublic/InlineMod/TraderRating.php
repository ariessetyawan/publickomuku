<?php /*18d3adcc8322e5a881d5a958a9d930da236151d4*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerPublic_InlineMod_TraderRating extends KomuKuYJB_ControllerPublic_InlineMod_Abstract
{
    public $inlineModKey = 'trader_ratings';

    /**
     * @return KomuKuYJB_Model_InlineMod_TraderRating
     */
    public function getInlineModTypeModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_InlineMod_TraderRating');
    }

    public function actionDelete()
    {
        if ($this->isConfirmedPost())
        {
            $hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::STRING);
            $options = array(
                'deleteType' => ($hardDelete ? 'hard' : 'soft'),
                'reason' => $this->_input->filterSingle('reason', XenForo_Input::STRING)
            );

            return $this->executeInlineModAction('deleteTraderRatings', $options, array('fromCookie' => false));
        }
        else
        {
            $feedbackIds = $this->getInlineModIds();
            $handler = $this->getInlineModTypeModel();

            if (!$handler->canDeleteTraderRatings($feedbackIds, 'soft', $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $redirect = $this->getDynamicRedirect();

            if (!$feedbackIds)
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $redirect
                );
            }

            $viewParams = array(
                'feedbackIds' => $feedbackIds,
                'ratingCount' => count($feedbackIds),
                'canHardDelete' => $handler->canDeleteTraderRatings($feedbackIds, 'hard'),
                'redirect' => $redirect
            );

            return $this->responseView('KomuKuYJB_ViewPublic_TraderRatingInlineMod_Delete', 'inline_mod_classified_trader_rating_delete', $viewParams);
        }
    }

    public function actionUndelete()
    {
        return $this->executeInlineModAction('undeleteTraderRatings');
    }

    public function actionApprove()
    {
        return $this->executeInlineModAction('approveTraderRatings');
    }

    public function actionUnapprove()
    {
        return $this->executeInlineModAction('unapproveTraderRatings');
    }
}