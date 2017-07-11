<?php

class BestAnswer_Installer
{
	public static function install($existingAddOn, $addOnData, $xml)
	{
		$db = XenForo_Application::get('db');
		
		if (!$db->fetchRow("SHOW columns FROM kmk_thread WHERE Field = 'best_answer_id'"))
		{
			$db->query("ALTER TABLE kmk_thread ADD COLUMN best_answer_id INT(10) UNSIGNED NOT NULL DEFAULT '0'");
		}
		
		if (!$db->fetchRow("SHOW columns FROM kmk_user WHERE Field = 'best_answer_count'"))
		{
			$db->query("ALTER TABLE kmk_user ADD COLUMN best_answer_count INT(10) UNSIGNED NOT NULL DEFAULT '0'");
		}
		
		$db->query("
			CREATE TABLE IF NOT EXISTS kmk_best_answer_vote (
				vote_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				thread_id INT(10) UNSIGNED NOT NULL,
				post_id INT(10) UNSIGNED NOT NULL,
				user_id INT(10) UNSIGNED NOT NULL,
				vote_date INT(10) UNSIGNED NOT NULL,
				PRIMARY KEY (vote_id),
				UNIQUE KEY user_thread (thread_id,user_id)
			)
		");
		
		if (!$db->fetchRow("SHOW columns FROM kmk_best_answer_vote WHERE Field = 'power'"))
		{
			$db->query("ALTER TABLE kmk_best_answer_vote ADD power INT(10) UNSIGNED NOT NULL DEFAULT '1'");
		}
		
		if (!$db->fetchRow("SHOW columns FROM kmk_thread WHERE Field = 'unanswered_prefix_id'"))
		{
			$db->query("ALTER TABLE kmk_thread ADD unanswered_prefix_id INT(10) UNSIGNED NOT NULL DEFAULT '0'");
		}
		
		if (!$db->fetchRow("SHOW columns FROM kmk_thread WHERE Field = 'alternative_best_answers'"))
		{
			$db->query("ALTER TABLE kmk_thread ADD alternative_best_answers MEDIUMTEXT NOT NULL");
		}
		
		if (!$db->fetchRow("SHOW columns FROM kmk_post WHERE Field = 'best_answer_points'"))
		{
			$db->query("ALTER TABLE kmk_post ADD best_answer_points INT(10) UNSIGNED NOT NULL DEFAULT '0'");
		}
		
		if ($existingAddOn['version_id'] < 19)
		{
			if ($db->fetchRow("SHOW columns FROM kmk_forum WHERE Field = 'allow_best_answer'"))
			{
				$enabledForums = $db->fetchAll('
					SELECT forum.node_id, forum.allow_best_answer
					FROM kmk_forum AS forum
					WHERE forum.allow_best_answer != 0
				');
				
				$enabledForumIds = array();
				foreach ($enabledForums AS $forum)
				{
					$enabledForumIds[] = $forum['node_id'];
				}
				
				$options = $xml->optiongroups->option;
				foreach ($options AS $option)
				{
					if ($option->attributes()->option_id == 'bestAnswerEnabledForums')
					{
						$option->default_value = serialize($enabledForumIds);
						break;
					}
				}
			}
		}
	}
	
	public static function uninstall()
	{
		$db = XenForo_Application::get('db');
		
		if ($db->fetchRow("SHOW columns FROM kmk_forum WHERE Field = 'allow_best_answer'"))
		{
			$db->query("ALTER TABLE kmk_forum DROP allow_best_answer");
		}
		
		if ($db->fetchRow("SHOW columns FROM kmk_thread WHERE Field = 'best_answer_id'"))
		{
			$db->query("ALTER TABLE kmk_thread DROP best_answer_id");
		}
		
		if ($db->fetchRow("SHOW columns FROM kmk_user WHERE Field = 'best_answer_count'"))
		{
			$db->query("ALTER TABLE kmk_user DROP best_answer_count");
		}
		
		if ($db->fetchRow("SHOW columns FROM kmk_thread WHERE Field = 'unanswered_prefix_id'"))
		{
			$db->query("ALTER TABLE kmk_thread DROP unanswered_prefix_id");
		}
		
		if ($db->fetchRow("SHOW columns FROM kmk_thread WHERE Field = 'alternative_best_answers'"))
		{
			$db->query("ALTER TABLE kmk_thread DROP alternative_best_answers");
		}
		
		if ($db->fetchRow("SHOW columns FROM kmk_post WHERE Field = 'best_answer_points'"))
		{
			$db->query("ALTER TABLE kmk_post DROP best_answer_points");
		}
		
		$db->query("DROP TABLE IF EXISTS kmk_best_answer_vote");
	}
}