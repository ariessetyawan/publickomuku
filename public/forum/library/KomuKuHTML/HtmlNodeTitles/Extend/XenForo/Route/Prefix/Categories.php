<?php

/**
 *
 * @see XenForo_Route_Prefix_Categories
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_Prefix_Categories extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_Prefix_Categories
{

    /**
     *
     * @see XenForo_Route_Prefix_Categories::buildLink()
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (isset($data['node_title'])) {
            $data['node_title'] = strip_tags($data['node_title']);
        } else {
            $data['title'] = strip_tags($data['title']);
        }
    
        return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
    } /* END buildLink */
}