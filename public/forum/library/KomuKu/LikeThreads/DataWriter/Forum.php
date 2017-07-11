<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_DataWriter_Forum extends XFCP_KomuKu_LikeThreads_DataWriter_Forum
{
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['kmk_forum']['default_sort_order']['allowedValues'][] = 'like_count';

        return $fields;
    } 
}