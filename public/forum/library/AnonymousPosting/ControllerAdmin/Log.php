<?php

class AnonymousPosting_ControllerAdmin_Log extends XenForo_ControllerAdmin_Abstract
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
            'logs' => $this->_getLogModel()->getLogs(array(
                'page' => $page,
                'perPage' => $perPage
            )),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $this->_getLogModel()->countLogs(),
        );
        return $this->responseView(
            'AnonymousPosting_ViewAdmin_Log_List',
            'anonymous_posting_log',
            $viewParams
        );
    }

    public function actionDelete()
    {
        $logId = $this->_input->filterSingle('log_id', XenForo_Input::UINT);
        $log = $this->_getLogModel()->getLogById($logId);
        if (empty($log)) {
            return $this->responseNoPermission();
        }

        if ($this->isConfirmedPost()) {
            $this->_getLogModel()->delete($log);

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('anonymous-posting')
            );
        } else {
            $viewParams = array('log' => $log);

            return $this->responseView(
                'AnonymousPosting_ViewAdmin_Log_Delete',
                'anonymous_posting_log_delete',
                $viewParams
            );
        }
    }

    public function actionDeleteAll()
    {
        if ($this->isConfirmedPost()) {
            $this->_getLogModel()->deleteAll();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('anonymous-posting')
            );
        } else {
            $viewParams = array();

            return $this->responseView(
                'AnonymousPosting_ViewAdmin_Log_DeleteAll',
                'anonymous_posting_log_delete_all',
                $viewParams
            );
        }
    }

    /**
     * @return AnonymousPosting_Model_Log
     */
    protected function _getLogModel()
    {
        return $this->getModelFromCache('AnonymousPosting_Model_Log');
    }
}
