<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_ControllerPublic_Account extends XFCP_KomuKu_ForumExtras_ControllerPublic_Account
{
   public function actionPersonalDetailsSave()
   {
	    $settings = $this->_input->filter(array(
		'gender'  => XenForo_Input::STRING,
		));
		
		//Initiliase the variables
		$writer = XenForo_DataWriter::create('XenForo_DataWriter_User');
		$visitor = XenForo_Visitor::getInstance();
		$writer->setExistingData(XenForo_Visitor::getUserId());
		$writer->bulkSet($settings);
		$writer->preSave();
		$gender = $writer->get('gender');
		
		//Exlude group/s from the extra forum view settings restrictions
		if($visitor->hasPermission('forum', 'exlude_extra_forums'))
		{
		   return parent::actionPersonalDetailsSave();
		}
		
		//Here we will prevent users to change genders so they can't sneak in the wrong forum :D
		if($visitor['gender'] AND $writer->isChanged('gender'))
		{
           throw new XenForo_Exception(new XenForo_Phrase('no_gender_changes', array('username' => $visitor['username'])), true);
        }

		return parent::actionPersonalDetailsSave();
	}
}
