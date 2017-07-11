<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_ControllerAdmin_User extends XFCP_KomuKu_LikeThreads_ControllerAdmin_User
{
    /**
	 * Deletes the specified user 's thread likes that he/she has made
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionDeleteLikes()
	{
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->_getUserOrError($userId);

		if ($user['is_admin'] || $user['is_moderator'])
		{
			return $this->responseNoPermission();
		}

		if ($this->isConfirmedPost())
		{
			/** @var $model KomuKu_LikeThreads_Model_LikeThreads */
			$model = $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads');
			
			//Delete all user's thread likes
			$model->deleteLikesByUser($user['user_id']);
			
            //Redirect to user edit page
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('users/edit', $user)
			);
		}

		return $this->responseView('XenForo_ViewAdmin_User_DeleteLikes', 'th_user_thread_like_delete', array(
			'user' => $user
		));
	}
}