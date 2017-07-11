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
class GFNCore_Mail_Message
{
    protected $_text;
    protected $_textWithoutReply;
    protected $_html;
    protected $_attachments = array();

    /**
     * @var Zend_Mail_Message
     */
    protected $_message;

    public function __construct($raw)
    {
        $message = new Zend_Mail_Message(array('raw' => $raw));
        $this->_message = &$message;
        $this->_splitAndParsePartStructure($message);
    }

    public function getText()
    {
        if (!$this->_text && $this->_html)
        {
            $this->_text = strip_tags($this->_html);
        }

        return $this->_text;
    }

    public function getTextWithoutReply()
    {
        if ($this->_textWithoutReply === null)
        {
            $original = $this->getText();

            $EOL = GFNCore_Helper_String::detectEndOfLine($original);
            if (!$EOL)
            {
                $this->_textWithoutReply = $original;
                return $original;
            }

            $pieces = explode($EOL, $original);
            $rules = $this->_breakLineRules();
            XenForo_CodeEvent::fire('gfncore_reply_breaklines', array(&$rules));
            $return = array();

            foreach ($pieces as $line)
            {
                foreach ($rules as $rule)
                {
                    if (empty($rule['regex']))
                    {
                        $pos = utf8_strpos($line, $rule['pattern']);
                        $match = empty($rule['position']) ? ($pos !== false) : ($pos === $rule['position']);
                    }
                    else
                    {
                        $match = preg_match($rule['pattern'], $line) ? true : false;
                    }

                    if (!$match)
                    {
                        continue;
                    }

                    if (!empty($rule['postMatchCheck']) && is_callable($rule['postMatchCheck']))
                    {
                        call_user_func_array($rule['postMatchCheck'], array(&$rule, &$return));
                    }

                    if (!empty($rule['skipAbove']))
                    {
                        for ($i = 1; $i <= $rule['skipAbove']; $i++)
                        {
                            array_pop($return);
                        }
                    }

                    break 2;
                }

                $return[] = $line;
            }

            $this->_textWithoutReply = utf8_trim(implode($EOL, $return));
        }

        return $this->_textWithoutReply;
    }

    public function getHtml()
    {
        return $this->_html;
    }

    public function getObject()
    {
        return $this->_message;
    }

    protected function _splitAndParsePartStructure(Zend_Mail_Part $part)
    {
        if (!$part->headerExists('content-type'))
        {
            return;
        }

        if ($part->isMultipart())
        {
            foreach ($part as $p)
            {
                $this->_splitAndParsePartStructure($p);
            }
        }
        else
        {
            $content = $part->getHeaderField('content-type', null, 'type');

            if (isset($content['boundary']))
            {
                $structures = Zend_Mime_Decode::splitMessageStruct($part->getContent(), $content['boundary']);

                foreach ($structures as $structure)
                {
                    if (empty($structure['header']['content-type']))
                    {
                        continue;
                    }

                    $content = $this->_decodeContent($structure);
                    $this->_parseContent($content);
                }
            }
            else
            {
                $structure = array(
                    'header' => $part->getHeaders(),
                    'body' => $part->getContent()
                );

                $content = $this->_decodeContent($structure);
                $this->_parseContent($content);
            }
        }
    }

    protected function _decodeContent(array $structure)
    {
        $content = Zend_Mime_Decode::splitHeaderField($structure['header']['content-type'], null, 'type');
        $charset = isset($content['charset']) ? $content['charset'] : null;
        $body = $structure['body'];
        $encoding = isset($structure['header']['content-transfer-encoding']) ? $structure['header']['content-transfer-encoding'] : null;

        switch (strtolower($encoding))
        {
            case 'base64':
                $body = base64_decode($body);
                break;

            case 'quoted-printable':
                $body = preg_replace("/=\r?\n/", '', $body);
                $body = preg_replace_callback('/=([a-f0-9]{2})/i', function(array $match) { return chr(hexdec($match[1])); }, $body);
                break;
        }

        if (!empty($charset))
        {
            $mb = 'utf-8';
            if (strtolower($mb) != $charset)
            {
                $body = mb_convert_encoding($body, $mb, $charset);
                $body = utf8_trim($body);
            }
        }

        $disposition = isset($structure['header']['content-disposition'])
                       ? Zend_Mime_Decode::splitHeaderField($structure['header']['content-disposition'], null, 'disposition')
                       : array();

        $flagPos = utf8_strpos($body, 'X-Spam-Flag:');
        if ($flagPos !== false)
        {
            $EOL = GFNCore_Helper_String::detectEndOfLine($body);
            $body = utf8_trim(utf8_substr($body, utf8_strpos($body, $EOL, $flagPos)));
        }

        return array(
            'body' => $body,
            'charset' => $charset,
            'type' => $content['type'],
            'name' => isset($disposition['filename']) ? $disposition['filename'] : (isset($content['name']) ? $content['name'] : null),
            'disposition' => isset($disposition['disposition']) ? $disposition['disposition'] : null,
            'isText' => utf8_strpos($content['type'], 'text/') === 0
        );
    }

    protected function _parseContent(array $content)
    {
        if ($content['isText'] && $content['disposition'] != 'attachment')
        {
            if ($content['type'] == 'text/plain' && $this->_text === null)
            {
                $this->_text = $content['body'];
            }
            elseif ($content['type'] == 'text/html' && $this->_html === null)
            {
                $this->_html = $content['body'];
            }
        }
        else
        {
            if (empty($content['name']))
            {
                $extension = GFNCore_Helper_MimeType::getExtension($content['type']);
                if ($extension === null)
                {
                    $extension = 'dat';
                }

                $content['name'] = sprintf('%s.%s', md5(uniqid('', true)), $extension);
            }
            elseif (strpos($content['name'], '/') !== false || strpos($content['name'], '\\') !== false)
            {
                $content['name'] = basename($content['name']);
            }

            $this->_attachments[] = $content;
        }
    }

    public function hasAttachments()
    {
        return count($this->_attachments) > 0;
    }

    public function getAttachments()
    {
        return $this->_attachments;
    }

    public function getHeaders()
    {
        return $this->_message->getHeaders();
    }

    public function getSubject()
    {
        return $this->_message->getHeader('subject');
    }

    public function getRecipients()
    {
        $headers = $this->getHeaders();
        $to = isset($headers['to']) ? $headers['to'] : (isset($headers['resent-to']) ? $headers['resent-to'] : '');
        $cc = isset($headers['cc']) ? $headers['cc'] : '';
        $bcc = isset($headers['bcc']) ? $headers['bcc'] : '';

        $return = $this->_parseRecipients($to);
        $return += $this->_parseRecipients($cc);
        $return += $this->_parseRecipients($bcc);
        return array_unique($return);
    }

    protected function _parseRecipients($recipients)
    {
        $return = array();

        if (!is_array($recipients))
        {
            $recipients = explode(',', $recipients);
        }

        foreach ($recipients as $to)
        {
            if (strpos($to, '<') !== false)
            {
                $return[] = preg_replace('/(.*)<(.*)>/', '\2', $to);
            }
            else
            {
                $return[] = trim($to);
            }
        }

        return $return;
    }

    public function getSender()
    {
        $from = $this->_message->getHeader('from');
        if (strpos($from, '<') !== false)
        {
            preg_match('/(.*)<(.*)>/', $from, $matches);
            $name = $matches[1];
            $address = $matches[2];
        }
        else
        {
            $name = null;
            $address = trim($from);
        }

        return array('name' => $name, 'address' => $address);
    }

    public function getReplyTo()
    {
        if ($this->_message->headerExists('reply-to'))
        {
            return $this->_message->getHeader('reply-to');
        }

        $sender = $this->getSender();
        return $sender['address'];
    }

    protected function _breakLineRules()
    {
        return array(
            array(
                'pattern' => '>',
                'regex' => false,
                'position' => 0,
                'skipAbove' => 1,
                'postMatchCheck' => function(&$rule, &$lines)
                {
                    $last = trim(end($lines));
                    while (empty($last))
                    {
                        $rule['skipAbove']++;
                        $last = trim(prev($lines));
                    }
                }
            ),
            array(
                'pattern' => '-----Original Message-----',
                'regex' => false,
                'skipAbove' => 0
            ),
            array(
                'pattern' => '----- Original Message -----',
                'regex' => false,
                'skipAbove' => 0
            ),
            array(
                'pattern' => '<!-- Break Line -->',
                'regex' => false,
                'skipAbove' => 0
            ),
            array(
                'pattern' => '====== Please reply above this line ======',
                'regex' => false,
                'skipAbove' => 0
            ),
            array(
                'pattern' => '_____',
                'regex' => false,
                'skipAbove' => 0
            )
        );
    }
}