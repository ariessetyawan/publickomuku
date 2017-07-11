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
class GFNCore_Mail_Storage_Pipe extends Zend_Mail_Storage_Abstract
{
    protected $_raw;

    protected $_headers;

    protected $_content;

    protected $_uniqueId;

    public function countMessages()
    {
        return 1;
    }

    /**
     * Get a list of messages with number and size
     *
     * @param  int $id number of message
     * @return int|array size of given message of list with all messages as array(num => size)
     */
    public function getSize($id = 0)
    {
        if ($id === 0)
        {
            return array(1 => strlen($this->_raw));
        }

        return strlen($this->_raw);
    }

    /**
     * Get a message with headers and body
     *
     * @param  $id int number of message
     * @return Zend_Mail_Message
     */
    public function getMessage($id = 1)
    {
        return new $this->_messageClass(array('handler' => $this, 'id' => $id, 'headers' => $this->_raw,
            'noToplines' => true));
    }

    /**
     * Get raw header of message or part
     *
     * @param  int $id number of message
     * @param  null|array|string $part path to part or null for messsage header
     * @param  int $topLines include this many lines with header (after an empty line)
     * @return string raw header
     */
    public function getRawHeader($id = 1, $part = null, $topLines = 0)
    {
        if ($this->_headers === null)
        {
            $EOL = GFNCore_Helper_String::detectEndOfLine($this->_raw);
            if (strpos($this->_raw, $EOL . $EOL) !== false)
            {
                list ($this->_headers) = explode($EOL . $EOL, $this->_raw, 2);
            }
            else
            {
                $this->_headers = '';
            }
        }

        return $this->_headers;
    }

    /**
     * Get raw content of message or part
     *
     * @param  int $id number of message
     * @param  null|array|string $part path to part or null for messsage content
     * @return string raw content
     */
    public function getRawContent($id = 1, $part = null)
    {
        if ($this->_content === null)
        {
            $EOL = GFNCore_Helper_String::detectEndOfLine($this->_raw);
            if (strpos($this->_raw, $EOL . $EOL) !== false)
            {
                list (, $this->_content) = explode($EOL . $EOL, $this->_raw, 2);
            }
            else
            {
                $this->_content = '';
            }
        }

        return $this->_content;
    }

    /**
     * Create instance with parameters
     *
     * @param  array $params mail reader specific parameters
     * @throws Zend_Mail_Storage_Exception
     */
    public function __construct($params)
    {
        while (!@feof($params['handler']))
        {
            $this->_raw .= fread($params['handler'], 1024);
        }

        $this->_uniqueId = XenForo_Application::$time;
    }

    /**
     * Close resource for mail lib. If you need to control, when the resource
     * is closed. Otherwise the destructor would call this.
     *
     * @return null
     */
    public function close()
    {
        return null;
    }

    /**
     * Keep the resource alive.
     *
     * @return null
     */
    public function noop()
    {
        return null;
    }

    /**
     * delete a message from current box/folder
     *
     * @return null
     */
    public function removeMessage($id = 1)
    {
        $this->_raw = '';
    }

    /**
     * get unique id for one or all messages
     *
     * if storage does not support unique ids it's the same as the message number
     *
     * @param int|null $id message number
     * @return array|string message number for given message or all messages as array
     * @throws Zend_Mail_Storage_Exception
     */
    public function getUniqueId($id = null)
    {
        if ($id === null)
        {
            return array(1 => $this->_uniqueId);
        }

        return $this->_uniqueId;
    }

    /**
     * get a message number from a unique id
     *
     * I.e. if you have a webmailer that supports deleting messages you should use unique ids
     * as parameter and use this method to translate it to message number right before calling removeMessage()
     *
     * @param string $id unique id
     * @return int message number
     * @throws Zend_Mail_Storage_Exception
     */
    public function getNumberByUniqueId($id = null)
    {
        return 1;
    }
}