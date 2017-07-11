<?php /*f038f6938be353ed066c7485f0cae27069a373e9*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Installer extends GFNCore_Installer_Abstract
{
    public function getVersionId()
    {
        return GFNClassifieds_Application::$versionId;
    }

    public function listen(GFNCore_Installer_Controller_Abstract $controller)
    {
        $controller->setMinXenForoVersion(1050400);

        $controller->setHook('pre_install', array($this, 'processDependencies'));
        $controller->setHook('pre_upgrade', array($this, 'processDependencies'));
    }

    public function processDependencies()
    {
        $handler = new GFNCore_Installer_Handler_Dependency();
        $handler->install('GFNCore');
    }
} 