<?php

class KomuKu_featuredmembers_XenForo_Model_User extends XFCP_KomuKu_featuredmembers_XenForo_Model_User
{
    public function prepareUserConditions(array $conditions, array &$fetchOptions)
    {
        $parent = parent::prepareUserConditions($conditions, $fetchOptions);

        if(isset($conditions['dad_fm_is_featured']) && $conditions['dad_fm_is_featured'])
        {
            $parent .= ' AND dad_fm_is_featured = 1';
        }

        if(isset($conditions['dad_fm_is_verified']) && $conditions['dad_fm_is_verified'])
        {
            $parent .= ' AND dad_fm_is_verified = 1';
        }

        return $parent;
    }
}
