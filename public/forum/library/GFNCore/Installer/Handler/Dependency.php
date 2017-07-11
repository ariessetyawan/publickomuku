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
class GFNCore_Installer_Handler_Dependency extends GFNCore_Installer_Handler_Abstract
{
    public function install($addOnId)
    {
        $fileName = XenForo_Autoloader::getInstance()->getRootDir() . "/{$addOnId}/addon-{$addOnId}.xml";
        if (!file_exists($fileName))
        {
            throw new GFNCore_Exception("The required XML file '{$fileName}' does not exist.", true);
        }

        if (!is_readable($fileName))
        {
            throw new GFNCore_Exception("The required XML file '{$fileName}' is not readable", true);
        }

        try
        {
            $xml = XenForo_Helper_DevelopmentXml::scanFile($fileName);
        }
        catch (Exception $e)
        {
            throw new GFNCore_Exception(new XenForo_Phrase('provided_file_was_not_valid_xml_file'), true);
        }

        if ($xml->getName() != 'addon')
        {
            throw new GFNCore_Exception(new XenForo_Phrase('provided_file_is_not_an_add_on_xml_file'), true);
        }

        $addOnData = array(
            'addon_id' => (string)$xml['addon_id'],
            'title' => (string)$xml['title'],
            'version_string' => (string)$xml['version_string'],
            'version_id' => (int)$xml['version_id'],
            'install_callback_class' => (string)$xml['install_callback_class'],
            'install_callback_method' => (string)$xml['install_callback_method'],
            'uninstall_callback_class' => (string)$xml['uninstall_callback_class'],
            'uninstall_callback_method' => (string)$xml['uninstall_callback_method'],
            'url' => (string)$xml['url'],
        );

        /** @var XenForo_Model_AddOn $model */
        $model = XenForo_Model::create('XenForo_Model_AddOn');
        $upgradeAddOnId = false;

        if ($existing = $model->getAddOnById($addOnData['addon_id']))
        {
            $upgradeAddOnId = $existing['addon_id'];

            if ($existing['version_id'] > $addOnData['version_id'])
            {
                return;
            }
        }

        unset ($existing);
        $existingAddOn = $model->verifyAddOnIsInstallable($addOnData, $upgradeAddOnId);

        $db = XenForo_Application::getDb();
        XenForo_Db::beginTransaction($db);

        $callback = array($addOnData['install_callback_class'], $addOnData['install_callback_method']);
        if ($callback[0] && XenForo_Application::autoload($callback[0]) && @is_callable($callback))
        {
            call_user_func_array($callback, array($existingAddOn, $addOnData, $xml));
        }

        $writer = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');

        if ($existingAddOn)
        {
            $writer->setExistingData($existingAddOn, true);
        }

        $writer->bulkSet($addOnData);
        $writer->save();

        $model->importAddOnExtraDataFromXml($xml, $addOnData['addon_id']);
        XenForo_Db::commit($db);
    }
}