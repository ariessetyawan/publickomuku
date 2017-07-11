<?php

class KomuKu_ProfileBbCode_Template_Helper_ProfileBbCode
{
	protected static $_bbCodeParser = null;
	protected static $_enableBbCodes = null;
	protected static $_autoLinking = null;
	protected static $_allowedBbCodes = null;
	protected static $_extraStates = array();
	protected static $_hookedTemplates = array(
			'message_simple' => 'profile',
			'profile_post_comment' => 'comment',
			'profile_post_list_item_simple' => 'list',
			'member_recent_activity' => 'list',
			'news_feed_item_profile_post_insert' => 'list', 
			'member_view' => 'status',
			'member_card' => 'status',
	);

	public static function helperProfileBbCode($template, $text)
	{
		if (!self::$_bbCodeParser)
		{
			$formatter = XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_Base');
			self::$_bbCodeParser = XenForo_BbCode_Parser::create($formatter);
		}
		$parser = self::$_bbCodeParser;

		if (self::$_enableBbCodes === null)
		{
			$options = XenForo_Application::get('options');

			self::$_enableBbCodes = $options->mr_pbbc_profileBbCodes;

			if (self::$_enableBbCodes == 'whitelist')
			{
				self::$_allowedBbCodes['status'] = array_flip(preg_split('/\s+/', strtolower(trim($options->mr_pbbc_allowedStatusBbCodes)), NULL, PREG_SPLIT_NO_EMPTY));
				self::$_allowedBbCodes['profile'] = array_flip(preg_split('/\s+/', strtolower(trim($options->mr_pbbc_allowedProfileBbCodes)), NULL, PREG_SPLIT_NO_EMPTY));
				self::$_allowedBbCodes['comment'] = array_flip(preg_split('/\s+/', strtolower(trim($options->mr_pbbc_allowedCommentBbCodes)), NULL, PREG_SPLIT_NO_EMPTY));
				self::$_allowedBbCodes['list'] = array_flip(preg_split('/\s+/', strtolower(trim($options->mr_pbbc_allowedListBbCodes)), NULL, PREG_SPLIT_NO_EMPTY));
			}

			self::$_autoLinking = $options->mr_pbbc_profileOtherSettings['auto_linking'];
			self::$_extraStates['stopSmilies'] = $options->mr_pbbc_profileSmilies;
			self::$_extraStates['stripOut'] = $options->mr_pbbc_profileOtherSettings['strip_out'];
		}

		if (self::$_autoLinking)
		{
			$text = XenForo_Helper_String::autoLinkBbCode($text);
		}

		self::$_extraStates['template'] = $template;

		$text = self::wrapTaggedPlainText($text);
		$text = $parser->render($text, self::$_extraStates);
		$text = XenForo_Helper_String::linkTaggedPlainText($text);

		return $text;
	}

	public static function helperSnippetExt($string, $maxLength = 0, array $options = array())
	{
		// workaround
		// since XF doesn't allow for both options, stripPlainTag (for profile user tags) AND bbCodeStrip, we need to do that here first
		$string = XenForo_Helper_String::bbCodeStrip($string, $options['stripQuote']);
		return XenForo_Template_Helper_Core::callHelper('snippet', array($string, $maxLength, $options));
	}

	public static function isAllowed($template, $tag)
	{
		// always allow plain tag. We use it to wrap profile post usertags
		if ($tag === 'plain')
		{
			return true;
		}

		switch (self::$_enableBbCodes)
		{
			case 'allow':
				return true;
			case 'whitelist':
				return (isset(self::$_hookedTemplates[$template]) && isset(self::$_allowedBbCodes[self::$_hookedTemplates[$template]][$tag]));
			default:
				return false;
		}
	}

	public static function wrapTaggedPlainText($string)
	{
		$string = preg_replace_callback(
			'#(?<=^|\s|[\](,]|--|@)@\[(\d+):(\'|"|&quot;|)(.*)\\2\]#iU',
			array('self', '_wrapTaggedPlainTextCallback'),
			$string
		);

		return $string;
	}

	protected static function _wrapTaggedPlainTextCallback(array $match)
	{
		return '[plain]' . $match[0] . '[/plain]';
	}

}