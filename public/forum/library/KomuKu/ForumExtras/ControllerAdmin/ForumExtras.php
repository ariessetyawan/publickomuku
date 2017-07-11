<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_ControllerAdmin_ForumExtras extends XenForo_ControllerAdmin_Abstract
{
     //Set admin permissions
    protected function _preDispatch($action)
	{
		$this->assertAdminPermission('forumextras');
	}
	
	//Displays the list with all the created Extra Forum View Settings
	public function actionIndex()
	{
		$forumextras = $this->_getForumextraModel()->getAllForumextras();
		
		$viewParams = array(
			'forumextras' => $forumextras
		);

		return $this->responseView('KomuKu_ForumExtras_ViewAdmin_ForumExtra_List', 'KomuKu_forumextra_admin_list', $viewParams);
	}

    //Add Extra Forum View Settings form
	public function actionAdd()
	{
		$viewParams = array(
			'forumextra' => array()
		);
		
		return $this->responseView('KomuKu_ForumExtras_ViewAdmin_ForumExtra_Edit', 'KomuKu_forumextra_admin_edit', $viewParams);
	}

	//Edit Extra Forum View Settings form
	public function actionEdit()
	{
		$forumextraId = $this->_input->filterSingle('id', XenForo_Input::UINT);
		
		$forumextra = $this->_getForumextraOrError($forumextraId);

		$viewParams = array(
			'forumextra' => $forumextra
		);
		
		return $this->responseView('KomuKu_ForumExtras_ViewAdmin_ForumExtra_Edit', 'KomuKu_forumextra_admin_edit', $viewParams);
	}

	//Add a new Extra Forum View Setting or edit an already created Extra Forum View Setting
	public function actionSave()
	{
		$this->_assertPostOnly();

		$forumextraId = $this->_input->filterSingle('id', XenForo_Input::UINT);
		
		$data = $this->_input->filter(array(
		    'node_id' => XenForo_Input::UINT,
			'message_count' => XenForo_Input::UINT,
			'daily_posts' => XenForo_Input::UINT,
			'register_date' => XenForo_Input::UINT,
			'user_age' => XenForo_Input::UINT,
			'user_gender' => XenForo_Input::STRING,
			'ban' => XenForo_Input::STRING,
		));

		$dw = XenForo_DataWriter::create('KomuKu_ForumExtras_DataWriter_ForumExtras');
		
		if ($forumextraId)
		{
			$dw->setExistingData($forumextraId);
		}
		
		$dw->bulkSet($data);
		$dw->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('forumextras')
		);
	}

	//Validate the current Extra Forum View Setting field
	public function actionValidateField()
	{
		$this->_assertPostOnly();

		return $this->_validateField('KomuKu_ForumExtras_DataWriter_ForumExtras', array(
			'existingDataKey' => $this->_input->filterSingle('id', XenForo_Input::UINT)
		));
	}

	//Delete the Extra Forum View Setting
	public function actionDelete()
	{
		if ($this->isConfirmedPost())
		{
			return $this->_deleteData(
				'KomuKu_ForumExtras_DataWriter_ForumExtras', 'id',
				XenForo_Link::buildAdminLink('forumextras')
			);
		}
		else
		{
			$forumextraId = $this->_input->filterSingle('id', XenForo_Input::UINT);
			
			$forumextra = $this->_getForumextraOrError($forumextraId);

			$viewParams = array(
				'forumextra' => $forumextra
			);
			
			return $this->responseView('KomuKu_ForumExtras_ViewAdmin_ForumExtra_Delete', 'KomuKu_forumextra_admin_delete', $viewParams);
		}
	}

	//Get the Extra Forum View Settings or otherwise throw an error if the requested Extra Forum View Settings is not found
	protected function _getForumextraOrError($forumextraId)
	{
		$info = $this->_getForumextraModel()->getForumextraById($forumextraId);
		
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_extraforum_not_found'), 404));
		}

		return $info;
	}

	//Return the Extra Forum View Settings model
	protected function _getForumextraModel()
	{
		return $this->getModelFromCache('KomuKu_ForumExtras_Model_ForumExtras');
	}
}