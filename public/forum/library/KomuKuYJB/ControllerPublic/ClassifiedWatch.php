<?php /*56b8638fd5dfb7fca29cf4cce0d0ef8c3d6e75e1*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerPublic_ClassifiedWatch extends KomuKuYJB_ControllerPublic_Abstract
{
    protected function _preDispatch($action)
    {
        $this->_assertRegistrationRequired();
    }

    public function actionIndex()
    {
        $classifiedModel = $this->models()->classified();
        $watchModel = $this->models()->classifiedWatch();

        $page = $this->_input->filterSingle('page', XenForo_Input::UINT);
        $perPage = KomuKuYJB_Options::getInstance()->get('classifiedsPerPage');

        $classifieds = $watchModel->getClassifiedsWatchedByUser(XenForo_Visitor::getUserId(), array_merge(
            $this->_getClassifiedListFetchOptions(),
            array(
                'permissionCombinationId' => XenForo_Visitor::getInstance()->get('permission_combination_id'),
                'page' => $page,
                'perPage' => $perPage
            )
        ));

        $classifieds = $classifiedModel->filterUnviewableClassifieds($classifieds);
        $totalClassifieds = $watchModel->countClassifiedsWatchedByUser(XenForo_Visitor::getUserId());

        $this->canonicalizePageNumber($page, $perPage, $totalClassifieds, 'classifieds/watched');
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/watched', null, array('page' => $page)));

        $viewParams = array(
            'classifieds' => $classifieds,
            'totalClassifieds' => $totalClassifieds,
            'page' => $page,
            'perPage' => $perPage
        );

        return $this->responseView('KomuKuYJB_ViewPublic_WatchedClassifieds', 'classifieds_watched', $viewParams);
    }

    public function actionUpdate()
    {
        $this->_assertPostOnly();

        $input = $this->_input->filter(array(
            'classified_ids' => array(XenForo_Input::UINT, 'array' => true),
            'do' => XenForo_Input::STRING
        ));

        $watch = $this->models()->classifiedWatch()->getUserClassifiedWatchByClassifiedIds(XenForo_Visitor::getUserId(), $input['classified_ids']);
        foreach ($watch as $classifiedWatch)
        {
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_ClassifiedWatch');
            $writer->setExistingData($classifiedWatch, true);

            switch ($input['do'])
            {
                case 'stop':
                    $writer->delete();
                    break;

                case 'email':
                    $writer->set('email_subscribe', 1);
                    $writer->save();
                    break;

                case 'no_email':
                    $writer->set('email_subscribe', 0);
                    $writer->save();
                    break;
            }
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->getDynamicRedirect(XenForo_Link::buildPublicLink('classifieds/watched'))
        );
    }
}