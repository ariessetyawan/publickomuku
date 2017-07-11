<?php

class KomuKuHTML_HtmlNodeTitles_Listener_LoadClass extends KomuKuHTML_Listener_LoadClass
{
    protected function _getExtendedClasses()
    {
        return array(
            'KomuKuHTML_HtmlNodeTitles' => array(
                'model' => array(
                    'XenForo_Model_Node',
                ), /* END 'model' */
                'route_prefix' => array(
                    'XenForo_Route_Prefix_Categories',
                    'XenForo_Route_Prefix_Forums',
                    'XenForo_Route_Prefix_LinkForums',
                    'XenForo_Route_Prefix_Pages',
                    'XenForo_Route_PrefixAdmin_Categories',
                    'XenForo_Route_PrefixAdmin_Forums',
                    'XenForo_Route_PrefixAdmin_LinkForums',
                    'XenForo_Route_PrefixAdmin_Nodes',
                    'XenForo_Route_PrefixAdmin_NodePermissions',
                    'XenForo_Route_PrefixAdmin_Pages',
                ), /* END 'route_prefix' */
                'datawriter' => array(
                    'XenForo_DataWriter_Category',
                    'XenForo_DataWriter_Forum',
                    'XenForo_DataWriter_LinkForum',
                    'XenForo_DataWriter_Page',
                ), /* END 'datawriter' */
            ), /* END 'KomuKuHTML_HtmlNodeTitles' */
        );
    } /* END _getExtendedClasses */

    public static function loadClassModel($class, array &$extend)
    {
        $loadClassModel = new KomuKuHTML_HtmlNodeTitles_Listener_LoadClass($class, $extend, 'model');
        $extend = $loadClassModel->run();
    } /* END loadClassModel */

    public static function loadClassRoutePrefix($class, array &$extend)
    {
        $loadClassRoutePrefix = new KomuKuHTML_HtmlNodeTitles_Listener_LoadClass($class, $extend, 'route_prefix');
        $extend = $loadClassRoutePrefix->run();
    } /* END loadClassRoutePrefix */

    public static function loadClassDataWriter($class, array &$extend)
    {
        $loadClassDataWriter = new KomuKuHTML_HtmlNodeTitles_Listener_LoadClass($class, $extend, 'datawriter');
        $extend = $loadClassDataWriter->run();
    } /* END loadClassDataWriter */
}