<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Installer_Handler_Style extends GFNCore_Installer_Handler_Abstract
{
    public function handle($addOnId)
    {
        try
        {
            $addOnId = strtolower($addOnId);
            $root = XenForo_Application::getInstance()->getRootDir() . '/styles';
            if ((!$root = realpath($root)) || !is_dir($root))
            {
                return;
            }

            $source = $root . '/default/' . $addOnId;
            if ((!$source = realpath($source)) || !is_dir($source))
            {
                return;
            }

            $available = GFNCore_Helper_Directory::read($root, false);

            foreach ($available as $i => $path)
            {
                if (is_dir($path) && (basename($path) != 'default'))
                {
                    $target = $path . '/' . $addOnId;
                    GFNCore_Helper_Directory::copy($source, $target);
                }
            }
        }
        catch (Exception $e)
        {
            XenForo_Error::logException(new XenForo_Exception('Unable to copy add-on related style files to other folders. Please do it manually.', true), false);
        }
    }
}