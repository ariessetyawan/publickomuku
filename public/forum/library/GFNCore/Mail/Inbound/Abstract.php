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
abstract class GFNCore_Mail_Inbound_Abstract
{
    /**
     * @var Zend_Mail_Storage_Abstract
     */
    protected $_connection;

    public function __construct(array $config)
    {
        $config = $this->_assertValidConfig($config);
        $this->_createConnection($config);
    }

    abstract protected function _createConnection(array $config);

    public function getConnection()
    {
        return $this->_connection;
    }

    public function countMessages()
    {
        return $this->_connection->countMessages();
    }

    public function getMessage($id)
    {
        $connection = $this->_connection;
        if ($id > $connection->countMessages())
        {
            throw new GFNCore_Exception('Out of bound index.');
        }

        return new GFNCore_Mail_Message($connection->getRawHeader($id) . "\r\n\r\n" . $connection->getRawContent($id));
    }

    protected function _assertValidConfig(array $config)
    {
        $return = array();

        if (isset($config['host']))
        {
            $return['host'] = strval($config['host']);
        }
        elseif (isset($config['hostname']))
        {
            $return['host'] = strval($config['hostname']);
        }
        else
        {
            throw new GFNCore_Exception('No hostname specified.');
        }

        if (isset($config['username']))
        {
            $return['user'] = strval($config['username']);
        }
        elseif (isset($config['user']))
        {
            $return['user'] = strval($config['user']);
        }
        else
        {
            throw new GFNCore_Exception('No username specified.');
        }

        if (isset($config['password']))
        {
            $return['password'] = strval($config['password']);
        }
        elseif (isset($config['pass']))
        {
            $return['password'] = strval($config['pass']);
        }
        else
        {
            throw new GFNCore_Exception('Not using any password is a serious security risk.');
        }

        if (!empty($config['encryption']))
        {
            $return['ssl'] = strtoupper($config['encryption']);
        }
        elseif (!empty($config['ssl']))
        {
            $return['ssl'] = strtoupper($config['ssl']);
        }

        if (!empty($config['port']))
        {
            $return['port'] = intval($config['port']);
        }

        return $return;
    }

    public function close()
    {
        $this->_connection->close();
    }

    public function __destruct()
    {
        $this->close();
    }
}