<?php

class KomuKuHTML_HtmlNodeTitles_Listener_FileHealthCheck
{

    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes = array_merge($hashes,
            array(
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/DataWriter/Category.php' => '5c65d81ac1247ca9437d6fa8fee6eae8',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/DataWriter/Forum.php' => 'febdd77bcd9e7bb36618f0d67ec33281',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/DataWriter/LinkForum.php' => 'c8398a827bd122a39d4f869125df1bf5',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/DataWriter/Page.php' => '8d3db525323da377380787653f6f97d2',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Model/Node.php' => 'ce1137541dbc979865e630cbbb3ae928',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/Prefix/Categories.php' => 'b7f34b137d8a9bea48b3d65da2983308',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/Prefix/Forums.php' => '7d664e292e94eb95851d6d8f71f0de65',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/Prefix/LinkForums.php' => 'd064e24ba9725e174f4898684b382b7f',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/Prefix/Pages.php' => '55bf2d6c267a244af6c3661d1703ace7',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/PrefixAdmin/Categories.php' => 'f29df5d18d117c1b1c945ad3c7ac2796',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/PrefixAdmin/Forums.php' => '4fa28378eb9c57062a06e25c6b8f8639',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/PrefixAdmin/LinkForums.php' => '6d8ebf25d4b7007a83f0fe7ba40184a2',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/PrefixAdmin/NodePermissions.php' => 'e12861b795ca6e3d9f0d9694de4746be',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/PrefixAdmin/Nodes.php' => 'b8ab8e6f4f4fe1627704c17363381bb8',
                'library/KomuKuHTML/HtmlNodeTitles/Extend/XenForo/Route/PrefixAdmin/Pages.php' => '0d9daa40c418b2d4f5e4d744a3a480f5',
                'library/KomuKuHTML/HtmlNodeTitles/Install/Controller.php' => '14ebb2f96bfecfd96e27bb7e0631bf1c',
                'library/KomuKuHTML/HtmlNodeTitles/Listener/LoadClass.php' => 'a373075229290fcb45274bad229a91d9',
                'library/KomuKuHTML/Install.php' => '00d8b93ea3458f18752c348a09a16c50',
                'library/KomuKuHTML/Install/20140611.php' => 'c0d4af592999549895ee773f873c53a2',
                'library/KomuKuHTML/Deferred.php' => '4649953c0a44928b5e2d4a86e7d3f48a',
                'library/KomuKuHTML/Deferred/20130725.php' => '699fb7a47bd443d53cb14f524321175a',
                'library/KomuKuHTML/Listener/ControllerPreDispatch.php' => 'f51aeb4ef6c4acbce629188b04cd3643',
                'library/KomuKuHTML/Listener/ControllerPreDispatch/20140711.php' => 'ecf9225061a21f7b0cccbf97b525fdd4',
                'library/KomuKuHTML/Listener/InitDependencies.php' => '5b755bcc0e553351c40871f4181ce5b0',
                'library/KomuKuHTML/Listener/InitDependencies/20140722.php' => 'd61ea11cb14211ae3ca6a58302f61b74',
                'library/KomuKuHTML/Listener/LoadClass.php' => 'bfdfe90f8d484d81b05889037a4fb091',
                'library/KomuKuHTML/Listener/LoadClass/20140906.php' => 'dec6e44f3602973dd10819b6f1b7b71d',
            ));
    } /* END fileHealthCheck */
}