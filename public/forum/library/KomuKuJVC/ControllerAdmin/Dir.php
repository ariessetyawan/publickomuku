<?php

class KomuKuJVC_ControllerAdmin_Dir extends KomuKuJVC_ControllerAdmin_DirectoryAbstract
{
	/**
	 * Name of the DataWriter that will handle this node type
	 *
	 * @var string
	 */
	protected $_nodeDataWriterName = 'KomuKuJVC_DataWriter_Dir';

	public function actionIndex()
	{
		return $this->responseReroute('KomuKuJVC_ControllerAdmin_Directory', 'index');
	}

	public function actionAdd()
	{
		return $this->responseReroute('KomuKuJVC_ControllerAdmin_Dir', 'edit');
	}

	public function actionEdit()
	{
		$forumModel = $this->_getForumModel(); // Dir
		$nodeModel = $this->_getNodeModel(); // Directory
		if ($nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT))
		{
			// if a node ID was specified, we should be editing, so make sure a forum exists
			
			

			$forum = $forumModel->getForumById($nodeId);
			
			if (!$forum)
			{
				
				return $this->responseError(new XenForo_Phrase('requested_forum_not_found'), 404);
			}
		}
		else
		{
			// add a new forum
			$forum = array(
				'parent_node_id' => $this->_input->filterSingle('parent_node_id', XenForo_Input::UINT),
				'display_order' => 1,
				'display_in_list' => 1,
				'allow_posting' => 1
			);
		}

		
		// there is an issue after this point, the nodeParetOPtions are not returning all of the nodes!
		$viewParams = array(
			'directory' => $forum,
			'nodeParentOptions' => $nodeModel->getNodeOptionsArray(
				$nodeModel->getPossibleParentNodes($forum), $forum['parent_node_id'], true
			),
			'styles' => $this->_getStyleModel()->getAllStylesAsFlattenedTree(),
		);
		
	
		return $this->responseView('KomuKuJVC_ViewAdmin_Dir_Edit', 'sfdir_edit', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		if ($this->_input->filterSingle('delete', XenForo_Input::STRING))
		{
			return $this->responseReroute('KomuKuJVC_ControllerAdmin_Dir', 'deleteConfirm');
		}

		$nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);	
		
		$writerData = $this->_input->filter(array(
			'title' => XenForo_Input::STRING,
			'node_name' => XenForo_Input::STRING,
			'node_type_id' => XenForo_Input::STRING,
			'parent_node_id' => XenForo_Input::UINT,
			'display_order' => XenForo_Input::UINT,
			'display_in_list' => XenForo_Input::UINT,
			'description' => XenForo_Input::STRING,
			'style_id' => XenForo_Input::UINT,
			'moderate_messages' => XenForo_Input::UINT,
			'allow_posting' => XenForo_Input::UINT
		));
		if (!$this->_input->filterSingle('style_override', XenForo_Input::UINT))
		{
			$writerData['style_id'] = 0;
		}

		$writer = $this->_getNodeDataWriter();

		if ($nodeId)
		{
			$writer->setExistingData($nodeId);
		}

		$writer->bulkSet($writerData);
		

		$writer->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('dirs') . $this->getLastHash($writer->get('node_id'))
		);
	}

	public function actionDeleteConfirm()
	{
		$forumModel = $this->_getForumModel();
		$nodeModel = $this->_getNodeModel();

		$forum = $forumModel->getForumById($this->_input->filterSingle('node_id', XenForo_Input::UINT));
		if (!$forum)
		{
			return $this->responseError(new XenForo_Phrase('requested_forum_not_found'), 404);
		}

		$childNodes = $nodeModel->getChildNodes($forum);

		$viewParams = array(
			'directory' => $forum,
			'childNodes' => $childNodes,
			'nodeParentOptions' => $nodeModel->getNodeOptionsArray(
				$nodeModel->getPossibleParentNodes($forum), $forum['parent_node_id'], true
			)
		);

		return $this->responseView('KomuKuJVC_ViewAdmin_Dir_Delete', 'sfdir_delete', $viewParams);
	}

	/**
	 * @return XenForo_Model_Forum
	 */
	protected function _getForumModel()
	{
		return $this->getModelFromCache('KomuKuJVC_Model_Dir');
	}

	/**
	 * @return XenForo_DataWriter_Forum
	 */
	protected function _getNodeDataWriter()
	{
		return XenForo_DataWriter::create($this->_nodeDataWriterName);
	}
}