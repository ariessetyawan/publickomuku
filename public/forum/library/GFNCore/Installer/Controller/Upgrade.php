<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Installer_Controller_Upgrade extends GFNCore_Installer_Controller_Abstract
{
    public function execute()
    {
        $installer = $this->_installer;
        $this->callHook('pre_upgrade');

        $this->checkXenForoVersion();
        $this->checkDependencies();

        if (!$this->hasSqlData())
        {
            $this->callHook('post_upgrade');
            return;
        }

        if ($this->doMasterRebuild())
        {
            $class = $installer->getSqlDataClassPrefix() . $installer->getExistingData()->version_id;
            if (XenForo_Application::autoload($class))
            {
                /** @var GFNCore_Installer_Data_Abstract $obj */
                $obj = new $class();
                $obj->rebuild();
            }
        }
        else
        {
            $versions = $this->getAvailableSqlVersions();

            foreach ($versions as $version)
            {
                $class = $installer->getSqlDataClassPrefix() . $version;

                /** @var GFNCore_Installer_Data_Abstract $obj */
                $obj = new $class();
                $this->callHook('upgrade_sql_pre_' . $version, array($obj));
                $obj->install($installer->getExistingData()->version_id);
                $this->callHook('upgrade_sql_post_' . $version, array($obj));
            }
        }

        $this->callHook('post_upgrade');
    }

    public function doMasterRebuild()
    {
        return ($this->_installer->getExistingData()->version_id == $this->_installer->getNewData()->version_id);
    }
}