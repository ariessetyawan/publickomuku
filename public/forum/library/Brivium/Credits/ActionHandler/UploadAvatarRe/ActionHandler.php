<?php

/**
 *
 * @package Brivium_Credits
 */
class Brivium_Credits_ActionHandler_UploadAvatarRe_ActionHandler extends Brivium_Credits_ActionHandler_Abstract
{
	protected $_editTemplate = 'BRC_action_edit_template_user';
	protected $_displayOrder = 121;
	protected $_extendedClasses = array(
		'load_class_datawriter' => array(
			'XenForo_DataWriter_User' => 'Brivium_Credits_ActionHandler_UploadAvatarRe_DataWriter_User'
		),
	);

 	public function getActionId()
 	{
 		return 'uploadAvatarRe';
 	}

	public function getActionTitlePhrase()
 	{
 		return 'BRC_action_uploadAvatarRe';
 	}

	public function getDescriptionPhrase()
 	{
 		return 'BRC_action_uploadAvatarRe_description';
 	}
}