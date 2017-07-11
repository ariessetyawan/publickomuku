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
final class GFNCore_Encryption
{
    const MCRYPT_CYPHER	    = MCRYPT_RIJNDAEL_256;
    const MCRYPT_MODE       = MCRYPT_MODE_CBC;

    protected $_private;

    protected $_public;

    protected $_hostname;

    protected $baseChars = 's3WgLkaPYENOGzZlJxFdTwtoh8qMC6u0R4KiVDQ2UjcmyfnvS9Ip1HAr5Be7Xb';

    public function __construct($private, $public, $hostname = null)
    {
        $this->_private = $private;
        $this->_public = $public;

        if ($hostname === null)
        {
            $hostname = @parse_url(XenForo_Application::getOptions()->get('boardUrl'), PHP_URL_HOST);
        }

        $this->_hostname = $hostname;
        $cryptSalt = serialize(array($hostname, $private));

        for ($n = 0; $n < strlen($this->baseChars); $n++)
        {
            $i[] = substr($this->baseChars, $n, 1);
        }

        $hash = hash('sha256', $cryptSalt);
        $hash = strlen($hash) < strlen($this->baseChars) ? hash('sha512', $cryptSalt) : $hash;

        for ($n = 0; $n < strlen($this->baseChars); $n++)
        {
            $p[] = substr($hash, $n, 1);
        }

        array_multisort($p, SORT_DESC, $i);
        $this->baseChars = implode('', $i);
    }

    public function encrypt($data)
    {
        $data = serialize($data);
        $td = mcrypt_module_open(self::MCRYPT_CYPHER, null, self::MCRYPT_MODE, null);
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, hash('md5', $this->baseChars), $iv);
        $encrypted = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        return base64_encode($iv . $encrypted);
    }

    public function decrypt($data)
    {
        $enc = base64_decode($data);
        $td = mcrypt_module_open(self::MCRYPT_CYPHER, null, self::MCRYPT_MODE, null);
        $ivSize = mcrypt_enc_get_iv_size($td);
        $iv = substr($enc, 0, $ivSize);
        $enc = substr($enc, $ivSize);

        if($iv)
        {
            mcrypt_generic_init($td, hash('md5', $this->baseChars), $iv);
            $decrypted = mdecrypt_generic($td, $enc);
            mcrypt_generic_deinit($td);
        }

        if(!empty($decrypted))
        {
            return unserialize(trim($decrypted));
        }

        return null;
    }

    public function getPublicKey()
    {
        return $this->_public;
    }

    public function getPrivateKey()
    {
        return $this->_private;
    }
} 