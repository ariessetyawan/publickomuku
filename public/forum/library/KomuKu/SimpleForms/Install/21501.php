<?php

class KomuKu_SimpleForms_Install_21501 extends KomuKu_SimpleForms_Install_Abstract
{
	public function install(&$db)
	{
		// add the thread_lock destination option
		$rows = $db->fetchOne("SELECT COUNT(*) FROM `kmkform__destination_option` WHERE `option_id` = ?", array('thread_lock'));
		if ($rows == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (
					`option_id`
					,`destination_id`
					,`display_order`
					,`field_type`
					,`format_params`
					,`field_choices`
					,`match_type`
					,`match_regex`
					,`match_callback_class`
					,`match_callback_method`
					,`max_length`
					,`required`
					,`evaluate_template`
				) VALUES (
					'thread_lock'
					,1
					,55
					,'checkbox'
					,''
					,'a:1:{i:1;s:4:\"Lock\";}'
					,'none'
					,''
					,''
					,''
					,0
					,0
					,0
				);
			");
		}
		
		$rows = $db->fetchOne("SELECT COUNT(*) FROM `kmkform__form_destination_option` WHERE `option_id` = ?", array('thread_lock'));
		if ($rows == 0)
		{
			$db->query('
				INSERT INTO `kmkform__form_destination_option` (
					`form_destination_id`
					,`option_id`
					,`option_value`)
				SELECT
					`form_destination_id`
					,\'thread_lock\'
					,\'\'
				FROM `kmkform__form_destination`
				WHERE `destination_id` = 1
			');
		}
		
		// add poll destination option
		$rows = $db->fetchOne("SELECT COUNT(*) FROM `kmkform__destination_option` WHERE `option_id` = ?", array('thread_poll'));
		if ($rows == 0)
		{
    		$db->query("
    			INSERT INTO `kmkform__destination_option` (
    				`option_id`
    				,`destination_id`
    				,`display_order`
    				,`field_type`
    				,`format_params`
    				,`field_choices`
    				,`match_type`
    				,`match_regex`
    				,`match_callback_class`
    				,`match_callback_method`
    				,`max_length`
    				,`required`
    				,`evaluate_template`
    			) VALUES (
    				'thread_poll'
    				,1
    				,70
    				,'callback'
    				,'s:64:\"KomuKu_SimpleForms_DestinationOption_ThreadPoll::renderOption\";'
    				,''
    				,'callback'
    				,''
    				,'KomuKu_SimpleForms_Destination_Thread'
    				,'VerifyPoll'
    				,0
    				,0
    				,0
    			);
    		");
		}
		
		// convert old poll destination options to new poll destination option
		// be sure to filter out any destinations that already have a thread_poll option
		$formDestinationOptions = $db->fetchAll("
			SELECT
				`form_destination_id`
				,`option_id`
				,`option_value`
			FROM `kmkform__form_destination_option`
			WHERE `option_id` IN ('thread_poll_question', 'thread_poll_choices', 'thread_poll_multiple', 'thread_poll_public_votes')
		        AND `form_destination_id` NOT IN (
		            SELECT `form_destination_id`
		            FROM `kmkform__form_destination_option`
		            WHERE `option_id` = 'thread_poll'
		        )
		");
		
		// group the form destination options so we can process them together
		$formDestinations = array();
		foreach ($formDestinationOptions as $formDestinationOption)
		{
			if (!array_key_exists($formDestinationOption['form_destination_id'], $formDestinations))
			{
			    $formDestinations[$formDestinationOption['form_destination_id']] = array();
			}
			$formDestinations[$formDestinationOption['form_destination_id']][$formDestinationOption['option_id']] = $formDestinationOption['option_value'];
		}

		// loop through all of the form destinations and convert them over to the new thread_poll option
		foreach ($formDestinations as $formDestinationId => $formDestinationOption)
		{
			$optionArray = array();
				 
    		$optionArray['question'] = $formDestinationOption['thread_poll_question'];
			$optionArray['responses'] = unserialize($formDestinationOption['thread_poll_choices']);
			$optionArray['max_votes'] = (unserialize($formDestinationOption['thread_poll_multiple']) !== array()) ? 0 : 1;
			$optionArray['max_votes_type'] = ($optionArray['max_votes'] == 1) ? 'single' : 'unlimited';
			$optionArray['public_votes'] = (unserialize($formDestinationOption['thread_poll_public_votes']) !== array()) ? 1 : 0;

			$db->query("
				INSERT INTO `kmkform__form_destination_option` (
					`form_destination_id`
					,`option_id`
					,`option_value`
				) VALUES (
					?
					,'thread_poll'
					,?
				);
			", array($formDestinationId, serialize($optionArray)));
		}
		
		// delete old poll destination options
		$db->query("DELETE FROM `kmkform__destination_option` WHERE `option_id` IN ('thread_poll_question', 'thread_poll_choices', 'thread_poll_multiple', 'thread_poll_public_votes')");
		
		$db->query("DELETE FROM `kmkform__form_destination_option` WHERE `option_id` IN ('thread_poll_question', 'thread_poll_choices', 'thread_poll_multiple', 'thread_poll_public_votes')");
		
			
	   	return true;
	}
}