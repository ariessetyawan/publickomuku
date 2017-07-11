<?php
 /*************************************************************************
 * XenForo New Thread On Index - Xen Factory (c) 2015
 * All Rights Reserved.
 * Created by Clement Letonnelier aka. MtoR
 **************************************************************************
 * This file is subject to the terms and conditions defined in the Licence
 * Agreement available at http://xen-factory.com/pages/license-agreement/.
  *************************************************************************/

class KomuKu_NewThreadOnIndex_Proxy
{
    public static function extendsXenForoControllerPublicForum($class, array &$extend)
    {
        $extend[] = 'KomuKu_NewThreadOnIndex_XenForo_ControllerPublic_Forum';
    }
    
    public static function extendsXenForoControllerPublicMisc($class, array &$extend)
    {
        $extend[] = 'KomuKu_NewThreadOnIndex_XenForo_ControllerPublic_Misc';
    }
}