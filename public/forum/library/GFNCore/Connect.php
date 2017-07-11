<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 * @include    ./Connect/
 */

use GFNCore_Connect_Request as Request;
use GFNCore_Connect_Response as Response;

final class GFNCore_Connect
{
    const USER_AGENT    = 'GFNCore rx/1a.4b';
    const API_VERSION   = 1.0;
    const API_SERVER    = '178.62.98.123';

    protected $_client;

    protected $_encryption;

    protected $_encrypt;

    protected $_secure = false;

    protected static $_instance;

    public static function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $adapter = new Zend_Http_Client_Adapter_Socket();

        if (in_array('https', stream_get_wrappers()))
        {
            $this->_secure = true;

            $adapter->setStreamContext(array(
                'ssl' => array(
                    'verify_peer' => true,
                    'cafile' => realpath(dirname(__FILE__) . '/Connect/Certificate.crt'),
                    'SNI_enabled' => true,
                    'ciphers' => 'ALL:!aNULL:!ADH:!eNULL:!LOW:!EXP:!RC4:+HIGH:+MEDIUM'
                )
            ));
        }

        $client = new Zend_Http_Client();
        $client->setAdapter($adapter);
        $client->setCookieJar(true);

        $client->setConfig(array(
            'maxredirects' => 2,
            'strictredirects' => true,
            'useragent' => self::USER_AGENT,
            'timeout' => 5,
            'keepalive' => false
        ));

        $client->setHeaders(array(
            'Token' => $this->getPublicKey(),
            'Client' => @parse_url(XenForo_Application::getOptions()->get('boardUrl'), PHP_URL_HOST),
            'Host' => 'api.gfnlabs.com'
        ));

        $this->_client = $client;
    }

    public function getClient()
    {
        return clone $this->_client;
    }

    public function head($api, $request = null, $response = null, $throw = false)
    {
        $this->_callback($api, Request::HEAD, $request, $response, $throw);
    }

    public function get($api, $request = null, $response = null, $throw = false)
    {
        $this->_callback($api, Request::GET, $request, $response, $throw);
    }

    public function post($api, $request = null, $response = null, $throw = false)
    {
        $this->_callback($api, Request::POST, $request, $response, $throw);
    }

    public function delete($api, $request = null, $response = null, $throw = false)
    {
        $this->_callback($api, Request::DELETE, $request, $response, $throw);
    }

    protected function _callback($api, $method, $_request, $_response, $throw)
    {
        try
        {
            $request = new Request($this);
            $request->setApi($api);

            if (is_callable($_request))
            {
                call_user_func($_request, $request);
            }

            $response = $request->process($method);
            $response = new Response($this, $response);

            if (is_callable($_response))
            {
                call_user_func($_response, $response);
            }
        }
        catch (GFNCore_Connect_Exception $e)
        {
            if ($throw)
            {
                throw $e;
            }
            else
            {
                XenForo_Error::logException($e);
            }
        }
    }

    public function getPublicKey()
    {
        return GFNCore_Options::getInstance()->get('publicKey');
    }

    public function getPrivateKey()
    {
        return GFNCore_Options::getInstance()->get('privateKey');
    }

    public function secureConn()
    {
        return $this->_secure;
    }
} 