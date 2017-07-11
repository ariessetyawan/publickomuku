<?php

/**
 *
 * @see XenForo_Route_PrefixAdmin_Pages
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_PrefixAdmin_Pages extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_PrefixAdmin_Pages
{

    /**
     *
     * @see XenForo_Route_PrefixAdmin_Pages::buildLink()
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $data['title'] = strip_tags($data['title']);
        
        return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
    } /* END buildLink */
}