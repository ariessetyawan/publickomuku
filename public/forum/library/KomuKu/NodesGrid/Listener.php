<?php
class KomuKu_NodesGrid_Listener
{
    public static function file_health_check(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes += KomuKu_NodesGrid_FileSums::getHashes();
    }
}
?>