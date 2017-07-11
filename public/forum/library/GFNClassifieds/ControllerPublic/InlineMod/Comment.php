<?php /*fbdb3543556e6c2a0483fa848168d7896020577c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerPublic_InlineMod_Comment extends GFNClassifieds_ControllerPublic_InlineMod_Abstract
{
    public $inlineModKey = 'comments';

    /**
     * @return GFNClassifieds_Model_InlineMod_Comment
     */
    public function getInlineModTypeModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_InlineMod_Comment');
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

            return $this->executeInlineModAction('deleteComments', $options, array('fromCookie' => false));
        }
        else
        {
            $commentIds = $this->getInlineModIds();
            $handler = $this->getInlineModTypeModel();

            if (!$handler->canDeleteComments($commentIds, 'soft', $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $redirect = $this->getDynamicRedirect();

            if (!$commentIds)
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $redirect
                );
            }

            $viewParams = array(
                'commentIds' => $commentIds,
                'commentCount' => count($commentIds),
                'canHardDelete' => $handler->canDeleteComments($commentIds, 'hard'),
                'redirect' => $redirect
            );

            return $this->responseView('GFNClassifieds_ViewPublic_CommentInlineMod_Delete', 'inline_mod_classified_comment_delete', $viewParams);
        }
    }

    public function actionUndelete()
    {
        return $this->executeInlineModAction('undeleteComments');
    }

    public function actionApprove()
    {
        return $this->executeInlineModAction('approveComments');
    }

    public function actionUnapprove()
    {
        return $this->executeInlineModAction('unapproveComments');
    }
}