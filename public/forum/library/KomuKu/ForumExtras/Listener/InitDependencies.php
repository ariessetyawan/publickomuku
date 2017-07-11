<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_Listener_InitDependencies
{
	//Initialise the code
	public static function init(XenForo_Dependencies_Abstract $dependencies, array $data) 
	{
		new self($dependencies, $data);
	}

	//Construct&execute code
	protected function __construct(XenForo_Dependencies_Abstract $dependencies, array $data) 
	{
		$forumextras = $this->_loadForumextrasDataRegistry();

		if (!is_array($forumextras))
		{
			$forumextras = $this->_rebuildForumextrasCache();
		}
		
		XenForo_Application::set('forumextras', $forumextras);
	}

    //Load Extra Forums View Settings from the data registry
	protected function _loadForumextrasDataRegistry()
	{
		return XenForo_Model::create('XenForo_Model_DataRegistry')->get('forumextras');
	}

	//Return the Extra Forum View Settings model
	protected function _getForumextraModel()
	{
		return XenForo_Model::create('KomuKu_ForumExtras_Model_ForumExtras');
	}

	//Get the Extra Forum View Settings cache model
	protected function _rebuildForumextrasCache()
	{
		return $this->_getForumextraModel()->rebuildForumextrasCache();
	}
}
		