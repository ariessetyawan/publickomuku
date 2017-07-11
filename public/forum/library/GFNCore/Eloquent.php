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
abstract class GFNCore_Eloquent extends XenForo_Model implements ArrayAccess, IteratorAggregate, Serializable
{
    /**
     * @var XenForo_DataWriter
     */
    protected $_writer;

    protected $_fields = array();

    /**
     * Designed to be overwritten by child classes.
     * @var string
     */
    protected $_writerClass;

    protected $_errorHandler = XenForo_DataWriter::ERROR_ARRAY;

    protected $_saved = false;

    public function __construct($existingData = null)
    {
        if (!$this->_writerClass)
        {
            throw new GFNCore_Exception('No data writer specified.');
        }

        $this->_writer = $this->_createWriter();
        if ($existingData !== null)
        {
            $this->load($existingData);
        }

        foreach ($this->writer()->getFields() as $table)
        {
            $this->_fields = XenForo_Application::mapMerge($this->_fields, $table);
        }
    }

    public function offsetExists($offset)
    {
        return ($this->get($offset));
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    public function unserialize($serialized)
    {
        $unserialized = @unserialize($serialized);
        if (!is_array($unserialized))
        {
            throw new GFNCore_Exception('Unable to initialize writer.');
        }

        $this->_writer = $this->_createWriter();
        $this->writer()->setExistingData($unserialized, true);

        foreach ($this->writer()->getFields() as $table)
        {
            $this->_fields = XenForo_Application::mapMerge($this->_fields, $table);
        }
    }

    public function get($field)
    {
        if (!isset($this->_fields[$field]))
        {
            return null;
        }

        $fieldType = $this->_fields[$field]['type'];
        $value = $this->writer()->get($field);

        switch ($fieldType)
        {
            case XenForo_DataWriter::TYPE_SERIALIZED:
                $value = unserialize($value);
                break;

            case XenForo_DataWriter::TYPE_JSON:
                $value = json_decode($value, true);
                break;
        }

        return $value;
    }

    public function __get($field)
    {
        return $this->get($field);
    }

    public function bulkSet(array $fields)
    {
        $this->writer()->bulkSet($fields, array('setAfterPreSave' => true, 'ignoreInvalidFields' => true));
    }

    public function set($field, $value)
    {
        $this->writer()->set($field, $value, '', array('setAfterPreSave' => true, 'ignoreInvalidFields' => true));
    }

    public function __set($field, $value)
    {
        $this->set($field, $value);
    }

    public function save()
    {
        if ($this->writer()->hasChanges() && !$this->_saved)
        {
            $this->writer()->save();
            $this->_saved = true;
        }
    }

    public function __destruct()
    {
        $this->save();
    }

    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }

    protected function _createWriter()
    {
        return XenForo_DataWriter::create($this->_writerClass, $this->_errorHandler);
    }

    public function load($id)
    {
        $this->writer()->setExistingData($id);
    }

    public function toArray()
    {
        return $this->writer()->getMergedData();
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function increment($field)
    {
        $this->set($field, $this->get($field) + 1);
    }

    public function decrement($field, $unsigned = true)
    {
        if (!$unsigned || $this->get($field) > 0)
        {
            $this->set($field, $this->get($field) - 1);
        }
    }

    /**
     * For implementing the JsonSerializable interface.
     * Requires >= PHP 5.4.0.
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function writer()
    {
        return $this->_writer;
    }
}