<?php

class KomuKuHTML_HtmlNodeTitles_Install_Controller extends KomuKuHTML_Install
{

    protected $_resourceManagerUrl = 'http://xenforo.com/community/resources/html-in-node-titles-by-KomuKuHTML.2191/';

    protected $_minVersionId = 1020000;

    protected $_minVersionString = '1.2.0';

    protected function _getFieldChangesOnInstall()
    {
        return array(
            'kmk_node' => array(
                'title' => '`title` text NOT NULL'
            )
        );
    } /* END _getFieldChangesOnInstall */

    protected function _getFieldChangesOnUninstall()
    {
        return array(
            'kmk_node' => array(
                'title' => '`title` VARCHAR(50) NOT NULL'
            )
        );
    } /* END _getFieldChangesOnUninstall */
}