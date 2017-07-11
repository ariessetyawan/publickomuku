<?php

/**
 *
 * @see XenForo_Route_Prefix_LinkForums
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_Prefix_LinkForums extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_Route_Prefix_LinkForums
{

    /**
     *
     * @see XenForo_Route_Prefix_LinkForums::buildLink()
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