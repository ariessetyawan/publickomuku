<?php

/**
 *
 * @see XenForo_Route_PrefixAdmin_LinkForums
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_PrefixAdmin_LinkForums extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_PrefixAdmin_LinkForums
{

    /**
     *
     * @see XenForo_Route_PrefixAdmin_LinkForums::buildLink()
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $data['title'] = strip_tags($data['title']);

        return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
    } /* END buildLink */
}