<?php
/*=======================================================================*\
|| ##################################################################### ||
|| # ----------------------------------------------------------------- # ||
|| # Copyright © 2014 Jim Dudek AKA Nhawk/KomuKu                         # ||
|| # All Rights Reserved.                                              # ||
|| # This file may not be redistributed in whole or significant part.  # ||
|| ##################################################################### ||
\*=======================================================================*/

class KomuKu_OrderNodes_ControllerAdmin_Ordernodes extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('node');
	}

	public function actionIndex()
	{
		$nodeModel = XenForo_Model::create('XenForo_Model_Node');
		$nodes = $nodeModel->prepareNodesForAdmin($nodeModel->getAllNodes());
		$options = $nodeModel->getNodeOptionsArray($nodeModel->getAllNodes(), false, false);
		$viewParams = array('nodes' => $nodes, 'options' => $options);
		return $this->responseView('KomuKu_OrderNodes_ViewAdmin_Index', 'KomuKu_ordernodes', $viewParams);
	}

	public function actionSaveorder()
	{
		$nodeModel = XenForo_Model::create('XenForo_Model_Node');
		$nodes = $nodeModel->prepareNodesForAdmin($nodeModel->getAllNodes());
		$this->_assertPostOnly();

		foreach($nodes as $node)
		{
			$newposition = $this->_input->filterSingle('display' . $node['node_id'], XenForo_Input::UINT);
			$newparent = $this->_input->filterSingle('parent_node_id' . $node['node_id'], XenForo_Input::UINT);

			if($node['display_order'] !== $newposition || $node['parent_node_id'] !== $newparent)
			{
				$writerData = array('display_order' => $newposition, 'parent_node_id' => $newparent);
				$writer = XenForo_DataWriter::create('XenForo_DataWriter_Node');
				$writer->setExistingData($node['node_id']);
				$writer->bulkSet($writerData);
				$writer->save();
			}
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('ordernodes'),
			new XenForo_Phrase('KomuKu_ordernodes_saved')
		);
	}
}