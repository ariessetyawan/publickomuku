<?php /*5510b5e38a174453773490371c2c861daa395891*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerPublic_InlineMod_Classified extends KomuKuYJB_ControllerPublic_InlineMod_Abstract
{
    public $inlineModKey = 'classifieds';

    /**
     * @return KomuKuYJB_Model_InlineMod_Classified
     */
    public function getInlineModTypeModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_InlineMod_Classified');
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

            return $this->executeInlineModAction('deleteClassifieds', $options, array('fromCookie' => false));
        }
        else
        {
            $classifiedIds = $this->getInlineModIds();
            $handler = $this->getInlineModTypeModel();

            if (!$handler->canDeleteClassifieds($classifiedIds, 'soft', $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $redirect = $this->getDynamicRedirect();

            if (!$classifiedIds)
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $redirect
                );
            }

            $viewParams = array(
                'classifiedIds' => $classifiedIds,
                'classifiedCount' => count($classifiedIds),
                'canHardDelete' => $handler->canDeleteClassifieds($classifiedIds, 'hard'),
                'redirect' => $redirect
            );

            return $this->responseView('KomuKuYJB_ViewPublic_ClassifiedInlineMod_Delete', 'inline_mod_classified_delete', $viewParams);
        }
    }

    public function actionUndelete()
    {
        return $this->executeInlineModAction('undeleteClassifieds');
    }

    public function actionApprove()
    {
        return $this->executeInlineModAction('approveClassifieds');
    }

    public function actionUnapprove()
    {
        return $this->executeInlineModAction('unapproveClassifieds');
    }

    public function actionOpen()
    {
        return $this->executeInlineModAction('openClassifieds');
    }

    public function actionClose()
    {
        return $this->executeInlineModAction('closeClassifieds');
    }

    public function actionFeature()
    {
        return $this->executeInlineModAction('featureClassifieds');
    }

    public function actionUnfeature()
    {
        return $this->executeInlineModAction('unfeatureClassifieds');
    }

    public function actionReassign()
    {
        if ($this->isConfirmedPost())
        {
            $user = $this->getModelFromCache('XenForo_Model_User')->getUserByName(
                $this->_input->filterSingle('username', XenForo_Input::STRING),
                array('join' => XenForo_Model_User::FETCH_USER_PERMISSIONS)
            );

            if (!$user)
            {
                return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
            }

            $user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);
            if (!XenForo_Permission::hasPermission($user['permissions'], 'classifieds', 'view'))
            {
                return $this->responseError(new XenForo_Phrase('you_may_only_reassign_classified_to_user_with_permission_to_view'));
            }

            $options = array(
                'userId' => $user['user_id'],
                'username' => $user['username']
            );

            return $this->executeInlineModAction('reassignClassifieds', $options, array('fromCookie' => false));
        }
        else
        {
            $classifiedIds = $this->getInlineModIds();
            $handler = $this->getInlineModTypeModel();

            if (!$handler->canReassignClassifieds($classifiedIds, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $redirect = $this->getDynamicRedirect();

            if (!$classifiedIds)
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $redirect
                );
            }

            $viewParams = array(
                'classifiedIds' => $classifiedIds,
                'classifiedCount' => count($classifiedIds),
                'redirect' => $redirect
            );

            return $this->responseView('KomuKuYJB_ViewPublic_ClassifiedInlineMod_Reassign', 'inline_mod_classified_reassign', $viewParams);
        }
    }

    public function actionMove()
    {
        if ($this->isConfirmedPost())
        {
            $id = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
            $category = $this->models()->category()->getCategoryById($id);
            if (!$category)
            {
                return $this->responseError(new XenForo_Phrase('requested_category_not_found'), 404);
            }

            $options = array(
                'categoryId' => $category['category_id']
            );

            return $this->executeInlineModAction('moveClassifieds', $options, array('fromCookie' => false));
        }
        else
        {
            $classifiedIds = $this->getInlineModIds();
            $handler = $this->getInlineModTypeModel();

            if (!$handler->canMoveClassifieds($classifiedIds, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $redirect = $this->getDynamicRedirect();

            if (!$classifiedIds)
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $redirect
                );
            }

            $viewParams = array(
                'classifiedIds' => $classifiedIds,
                'classifiedCount' => count($classifiedIds),
                'redirect' => $redirect,
                'categories' => $this->models()->category()->prepareCategories(
                    $this->models()->category()->getViewableCategories()
                )
            );

            return $this->responseView('KomuKuYJB_ViewPublic_ClassifiedInlineMod_Move', 'inline_mod_classified_move', $viewParams);
        }
    }

    public function actionPrefix()
    {
        $classifiedIds = $this->getInlineModIds(!$this->isConfirmedPost());
        $redirect = $this->getDynamicRedirect();

        if (!$classifiedIds)
        {
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $redirect
            );
        }

        if ($this->isConfirmedPost())
        {
            $prefixId = $this->_input->filterSingle('prefix_id', XenForo_Input::UINT);

            if (!$this->getInlineModTypeModel()->applyClassifiedPrefix($classifiedIds, $prefixId, $unchangedClassifiedIds, array(), $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            if ($unchangedClassifiedIds)
            {
                XenForo_Helper_Cookie::setCookie('inlinemod_' . $this->inlineModKey, implode(',', $unchangedClassifiedIds));
            }
            else
            {
                $this->clearCookie();
            }

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $redirect
            );
        }
        else
        {
            $handler = $this->getInlineModTypeModel();
            if (!$handler->canEditClassifieds($classifiedIds, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $classifiedModel = $this->models()->classified();
            $prefixModel = $this->models()->prefix();

            $classifieds = $classifiedModel->getClassifiedsByIds($classifiedIds);
            $categoryIds = $classifiedModel->getCategoryIdsFromClassifieds($classifieds);
            $prefixes = $prefixModel->getUsablePrefixesInCategories($categoryIds);

            if (empty($prefixes))
            {
                return $this->responseError(new XenForo_Phrase('no_classified_prefixes_available_for_selected_categories'));
            }

            $selectedPrefix = 0;
            $prefixCounts = array(0 => 0);

            foreach ($classifieds as $classified)
            {
                $classifiedPrefixId = $classified['prefix_id'];

                if (!isset($prefixCounts[$classifiedPrefixId]))
                {
                    $prefixCounts[$classifiedPrefixId] = 1;
                }
                else
                {
                    $prefixCounts[$classifiedPrefixId]++;
                }

                if ($prefixCounts[$classifiedPrefixId] > $prefixCounts[$selectedPrefix])
                {
                    $selectedPrefix = $classifiedPrefixId;
                }
            }

            $viewParams = array(
                'classifiedIds' => $classifiedIds,
                'classifiedCount' => count($classifiedIds),
                'classifieds' => $classifieds,
                'categoryIds' => $categoryIds,
                'categoryCount' => count($categoryIds),
                'prefixes' => $prefixes,
                'selectedPrefix' => $selectedPrefix,
                'redirect' => $redirect
            );

            return $this->responseView('KomuKuYJB_ViewPublic_ClassifiedInlineMod_Prefix', 'inline_mod_classified_prefix', $viewParams);
        }
    }

    public function actionBump()
    {
        return $this->executeInlineModAction('bumpClassifieds');
    }
}