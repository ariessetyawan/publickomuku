<?php

/**
 *
 * @see XenForo_DataWriter_LinkForum
 */
class KomuKuHTML_HtmlNodeTitles_Extend_XenForo_DataWriter_LinkForum extends XFCP_KomuKuHTML_HtmlNodeTitles_Extend_XenForo_DataWriter_LinkForum
{

    /**
     *
     * @see XenForo_DataWriter_LinkForum::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
    
        unset($fields['kmk_node']['title']['maxLength']);
    
        return $fields;
    } /* END _getFields */
}