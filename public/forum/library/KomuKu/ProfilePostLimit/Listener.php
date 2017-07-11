<?php
/**
 * Copyright (c) komuku (komuku.com) 2015 to present.
 * All rights reserved.
 * @license All use is subject to the komuku License Agreement (https://www.komuku.com/community/products/license-agreement)
 * @author: komuku Team <support@komuku.com>
 */
class komuku_ProfilePostLimit_Listener
{
    public static function extend_kmk_pp_listener($class, &$extend)
    {
        $extend[] = 'komuku_ProfilePostLimit_DataWriter_DiscussionMessage_ProfilePost';
    }

    public static function extend_kmk_ppc_listener($class, &$extend)
    {
        $extend[] = 'komuku_ProfilePostLimit_DataWriter_ProfilePostComment';
    }
}