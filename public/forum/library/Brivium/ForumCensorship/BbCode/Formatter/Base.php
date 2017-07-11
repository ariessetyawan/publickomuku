<?php

class Brivium_ForumCensorship_BbCode_Formatter_Base extends XFCP_Brivium_ForumCensorship_BbCode_Formatter_Base
{
    public function filterString($string, array $rendererStates)
    {
        $parent = parent::filterString($string, $rendererStates);
            
        if (empty($rendererStates['stopSmilies']))
        {
            $string = $this->replaceSmiliesInText($string, 'htmlspecialchars');
        }
        else
        {
            $string = htmlspecialchars($string);
        }

        if (empty($rendererStates['stopLineBreakConversion']))
        {
            $string = nl2br($string);
        }

        $options = XenForo_Application::get('options');
        $forums = $options->ForumCensorship_excluded;
		
        $nodeId = !empty($GLOBALS['BRFC_forumCensorship'])?$GLOBALS['BRFC_forumCensorship']:0;
 
        if ($nodeId && in_array($nodeId, $forums))
        {
            return $string;
        }
        else 
        {
            return $parent;
        }
    }

}