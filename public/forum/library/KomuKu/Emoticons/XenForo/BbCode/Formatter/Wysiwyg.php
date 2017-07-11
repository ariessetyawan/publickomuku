<?php

class KomuKu_Emoticons_XenForo_BbCode_Formatter_Wysiwyg extends XFCP_KomuKu_Emoticons_XenForo_BbCode_Formatter_Wysiwyg
{
    use KomuKu_Emoticons_Traits_BbCodeBase;

    public function renderTagUnparsed(array $tag, array $rendererStates)
	{
		if(isset($tag['tag']) && $tag['tag'] == 'quote' && !empty($tag['option']))
		{
			$parts = array_map('trim', explode(',', $tag['option']));
			$parts = end($parts);

			if(!$parts)
			{
				return parent::renderTagQuote($tag, $rendererStates);
			}

			$parts = array_map('trim', explode(' ', $parts));
			$userId = end($parts);

			$rendererStates['extraUserId'] = $userId;
		}

		return parent::renderTagUnparsed($tag, $rendererStates);
	}
}
