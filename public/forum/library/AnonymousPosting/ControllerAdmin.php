<?php

class AnonymousPosting_ControllerAdmin extends XenForo_ControllerAdmin_Abstract
{
    protected function _preDispatch($action)
    {
        $this->assertAdminPermission('addOn');
    }

    public function actionIndex()
    {
        $page = $this->_input->filterSingle('page', XenForo_Input::UINT);
        $perPage = 20;

        $viewParams = array(
            'posts' => $this->_getPostModel()->getAnonymousPosts(array(
                'page' => $page,
                'perPage' => $perPage
            )),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $this->_getPostModel()->countAnonymousPosts(),
        );
        return $this->responseView(
            'AnonymousPosting_ViewAdmin',
            'anonymous_posting_log',
            $viewParams
        );
    }

    public function actionDeleteAll()
    {
        if ($this->isConfirmedPost()) {
            $this->_getPostModel()->deleteAnonymousLog();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('anonymous-posting')
            );
        } else {
            $viewParams = array();

            return $this->responseView(
                'AnonymousPosting_ViewAdmin_DeleteAll',
                'anonymous_posting_log_delete_all',
                $viewParams
            );
        }
    }

    /**
     * @return AnonymousPosting_XenForo_Model_Post
     */
    protected function _getPostModel()
    {
        return $this->getModelFromCache('XenForo_Model_Post');
    }

}
