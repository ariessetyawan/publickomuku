<?php

class PostComments_ControllerAdmin_Forum extends XFCP_PostComments_ControllerAdmin_Forum
{
	public function actionSave()
    {
        $response = parent::actionSave();
		
        if($response->redirectType == XenForo_ControllerResponse_Redirect::SUCCESS) 
		{
            $writerData = $this->_input->filter(array(
				'comment_count' => XenForo_Input::UINT
			));

            if(empty($writerData['node_id'])) 
			{
                $writerData['node_id'] = $this->_getLastSavedForumNodeId();
            }

            $writer = $this->_getNodeDataWriter();

			if($writerData['node_id']) 
			{
				$writer->setExistingData($writerData['node_id']);
			}

			$writer->bulkSet($writerData);
			$writer->save();
        }
		
        return $response;
    }

    protected function _getLastSavedForumNodeId()
	{
		return XenForo_Application::get('db')->fetchOne('
			SELECT node_id
			FROM kmk_forum
			ORDER BY node_id
			DESC
		');
	}
}