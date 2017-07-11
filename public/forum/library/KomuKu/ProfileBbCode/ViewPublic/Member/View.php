<?php

class KomuKu_ProfileBbCode_ViewPublic_Member_View extends XFCP_KomuKu_ProfileBbCode_ViewPublic_Member_View
{
	public function renderHtml()
	{
		if ($this->_parentHasMethod('renderHtml'))
		{
			parent::renderHtml();
		}

		$this->_params['wysiwyg'] = XenForo_Application::get('options')->mr_pbbc_profileOtherSettings['wysiwyg'];

		if (isset($this->_params['wysiwyg']) AND $this->_params['wysiwyg'])
		{
			$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
				$this, 'message', '',
				array(
					'height' => false,
				)
			);
		}
	}

	// kudos xfrocks
	protected function _parentHasMethod($method)
	{
		$us = 'XFCP_' . __CLASS__;
		$usFound = false;

		foreach (class_parents($this) as $parent)
		{
			if ($parent === $us)
			{
				$usFound = true;
				continue;
			}

			if (!$usFound)
			{
				continue;
			}

			if (method_exists($parent, $method))
			{
				return true;
			}
		}

		return false;
	}

}