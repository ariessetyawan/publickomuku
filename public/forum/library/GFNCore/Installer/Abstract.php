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
abstract class GFNCore_Installer_Abstract
{
    public static function install()
    {
        self::initiate('install', func_get_args());
    }

    public static function uninstall()
    {
        self::initiate('uninstall', func_get_args());
    }

    public static function initiate($method, array $args)
    {
        if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50307)
        {
            throw new GFNCore_Exception('This add-on does not support PHP versions lesser than 5.3.7', true);
        }

        if ($method == 'install' && !empty($args[0]))
        {
            $job = 'upgrade';
        }
        else
        {
            $job = $method;
        }

        /** @var GFNCore_Installer_Abstract $obj */
        $obj = new static();
        $obj->setJob($job);

        if ($method == 'install')
        {
            $obj->setNewData($args[1]);
            $obj->setXml($args[2]);
        }

        if (in_array($job, array('upgrade', 'uninstall')))
        {
            $obj->setExistingData($args[0]);
        }

        $class = 'GFNCore_Installer_Controller_' . ucfirst($job);

        XenForo_Db::beginTransaction();

        try
        {
            /** @var GFNCore_Installer_Controller_Abstract $controller */
            $controller = new $class($obj);
            $controller->execute();
        }
        catch (Exception $e)
        {
            XenForo_Db::rollback();
            throw $e;
        }

        XenForo_Db::commit();

        $style = new GFNCore_Installer_Handler_Style();
        $style->handle($obj->getData()->addon_id);
    }

    protected $_job;

    public function setJob($job)
    {
        $this->_job = $job;
    }

    public function getJob()
    {
        return $this->_job;
    }

    public function isInstall()
    {
        return ($this->_job === 'install');
    }

    public function isUpgrade()
    {
        return ($this->_job === 'upgrade');
    }

    public function isUninstall()
    {
        return ($this->_job === 'uninstall');
    }

    protected $_existing;

    public function setExistingData($data)
    {
        $obj = new stdClass();
        $obj->addon_id = $data['addon_id'];
        $obj->title = $data['title'];
        $obj->version_string = $data['version_string'];
        $obj->version_id = $data['version_id'];
        $this->_existing = $obj;
    }

    public function getExistingData()
    {
        return $this->_existing;
    }

    protected $_new;

    public function setNewData($data)
    {
        $obj = new stdClass();
        $obj->addon_id = $data['addon_id'];
        $obj->title = $data['title'];
        $obj->version_string = $data['version_string'];
        $obj->version_id = $data['version_id'];
        $this->_new = $obj;
    }

    public function getNewData()
    {
        return $this->_new;
    }

    public function getData()
    {
        return $this->isUninstall() ? $this->getExistingData() : $this->getNewData();
    }

    protected $_xml;

    public function setXml(SimpleXMLElement $xml)
    {
        $this->_xml = $xml;
    }

    public function getXml()
    {
        return $this->_xml;
    }

    abstract public function getVersionId();

    public function getSqlDataPath()
    {
        return realpath(XenForo_Autoloader::getInstance()->getRootDir() . '/' . $this->getData()->addon_id . '/Installer/Data');
    }

    public function getSqlDataClassPrefix()
    {
        return $this->getData()->addon_id . '_Installer_Data_';
    }

    public function getDependencies()
    {
        return array();
    }

    public function listen(GFNCore_Installer_Controller_Abstract $controller) { }
} 