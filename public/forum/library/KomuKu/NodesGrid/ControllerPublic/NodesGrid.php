<?php
class KomuKu_NodesGrid_ControllerPublic_NodesGrid extends XenForo_ControllerPublic_Abstract
{
	protected function _preDispatch($action)
	{
		if (!XenForo_Visitor::getInstance()->hasAdminPermission('node'))
		{
			throw $this->responseException($this->responseNoPermission());
		}
	}

	protected function _getNodeOrError($nodeId)
	{
		$visitor = XenForo_Visitor::getInstance();
		$fetchOptions = array('permissionCombinationId' => $visitor['permission_combination_id']);

		$node = $this->_getNodeModel()->getNodeById($nodeId, $fetchOptions);
		if (!$node)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_node_not_found')));
		}

		return $node;
	}

	public function actionToggle()
	{
		$this->_assertPostOnly();

		$nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		if(!$nodeId)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_node_not_found')));
		}

		$node = $this->_getNodeOrError($nodeId);

		$dw = XenForo_DataWriter::create('XenForo_DataWriter_Node');
		$dw->setExistingData($node, true);
		$dw->set('grid_column', !$node['grid_column']);
		$dw->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('forums')
		);
	}

	protected function _getNodeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Node');
	}
}
?>