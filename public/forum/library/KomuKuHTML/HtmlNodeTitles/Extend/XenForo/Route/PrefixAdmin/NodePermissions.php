<?php

/**
 *
 * @see XenForo_Route_PrefixAdmin_NodePermissions
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_PrefixAdmin_NodePermissions extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_PrefixAdmin_NodePermissions
{

    /**
     *
     * @see XenForo_Route_PrefixAdmin_NodePermissions::buildLink()
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $data['title'] = strip_tags($data['title']);
        
        return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
    } /* END buildLink */
}