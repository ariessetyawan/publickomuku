<?php

class BestAnswer_ViewPublic_Member_BestAnswers extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$output = $this->_renderer->getDefaultOutputArray(get_class($this), $this->_params, 'member_best_answers_posts');
		
		$output['lastPostId'] = $this->_params['lastPostId'];
		
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}

?>