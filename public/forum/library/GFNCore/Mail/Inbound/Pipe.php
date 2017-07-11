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
class GFNCore_Mail_Inbound_Pipe extends GFNCore_Mail_Inbound_Abstract
{
    protected function _createConnection(array $config)
    {
        $this->_connection = new GFNCore_Mail_Storage_Pipe($config);
    }

    protected function _assertValidConfig(array $config)
    {
        $return = array();

        if (isset($config['handler']) && is_resource($config['handler']))
        {
            $return['handler'] = $config['handler'];
        }
        else
        {
            throw new GFNCore_Exception('No STDIO handler specified for e-mail piping.');
        }

        return $return;
    }
}