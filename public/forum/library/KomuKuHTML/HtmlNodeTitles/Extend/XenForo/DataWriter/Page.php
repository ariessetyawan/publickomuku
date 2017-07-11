<?php

/**
 *
 * @see XenForo_DataWriter_Page
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_DataWriter_Page extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_DataWriter_Page
{

    /**
     *
     * @see XenForo_DataWriter_Page::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
    
        unset($fields['kmk_node']['title']['maxLength']);
    
        return $fields;
    } /* END _getFields */
}