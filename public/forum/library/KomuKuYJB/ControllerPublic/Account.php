<?php /*6bd7aebb5427d57be19e2e02a0fed3d4f16ed82c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerPublic_Account extends KomuKuYJB_ControllerPublic_Abstract
{
    protected function _preDispatch($action)
    {
        $this->_assertRegistrationRequired();
    }

    public function actionIndex()
    {
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
            $this->_buildLink('classifieds/account/classifieds')
        );
    }

    public function actionClassifieds()
    {
        $classifiedModel = $this->models()->classified();
        $advertTypeModel = $this->models()->advertType();

        $criteria = array('user_id' => XenForo_Visitor::getUserId());
        $criteria += $classifiedModel->getPermissionBasedFetchConditions();
        $totalClassifieds = $classifiedModel->countClassifieds($criteria);

        if (!$totalClassifieds)
        {
            return $this->responseError(new XenForo_Phrase('requested_user_has_no_classifieds'));
        }

        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = KomuKuYJB_Options::getInstance()->get('classifiedsPerPage');

        $this->canonicalizePageNumber($page, $perPage, $totalClassifieds, 'classifieds/account/classifieds');
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/account/classifieds', null, array('page' => $page)));

        $fetchOptions = $this->_getClassifiedListFetchOptions();

        if ($criteria['deleted'])
        {
            $fetchOptions['join'] |= $classifiedModel::FETCH_DELETION_LOG;
        }

        $fetchOptions += array(
            'perPage' => $perPage,
            'page' => $page,
            'order' => 'item_date',
            'direction' => 'desc'
        );

        $classifieds = $classifiedModel->getClassifieds($criteria, $fetchOptions);
        $classifieds = $classifiedModel->filterUnviewableClassifieds($classifieds);
        $classifieds = $classifiedModel->prepareClassifieds($classifieds);
        $inlineModOptions = $classifiedModel->getInlineModOptionsForClassifieds($classifieds);

        foreach ($classifieds as $classified)
        {
            if ($classifiedModel->canBumpClassified($classified, $classified))
            {
                $inlineModOptions['bump'] = true;
                break;
            }
        }

        $advertTypes = $advertTypeModel->getAllAdvertTypes();
        $advertTypeModel->prepareAdvertTypes($advertTypes);

        $viewParams = array(
            'classifieds' => $classifieds,
            'inlineModOptions' => $inlineModOptions,
            'advertTypes' => $advertTypes,

            'totalClassifieds' => $totalClassifieds,

            'page' => $page,
            'perPage' => $perPage
        );

        return $this->_getWrapper('classified', 'list', $this->responseView(
            'KomuKuYJB_ViewPublic_Account_Classified', 'classifieds_account_classified', $viewParams
        ));
    }

    protected function _getWrapper($selectedGroup, $selectedLink, XenForo_ControllerResponse_View $subView)
    {
        $this->_routeMatch->setSections('account');
        return XenForo_ControllerHelper_Account::wrap($this, $selectedGroup, $selectedLink, $subView);
    }
}