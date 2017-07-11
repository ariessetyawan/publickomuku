<?php

class KomuKu_SimpleForms_DataWriter_Page extends XenForo_DataWriter
{
    /**
     * Returns all kmkform__page fields
     */
    protected function _getFields()
    {
        return array(
            'kmkform__page' => array(
                'page_id'       => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'form_id'       => array('type' => self::TYPE_UINT),
                'page_number'   => array('type' => self::TYPE_UINT),
                'title'         => array('type' => self::TYPE_STRING, 'maxLength' => 50),
                'description'	=> array('type' => self::TYPE_STRING, 'default' => '')
            )
        );
    }
    
    /**
     * Gets the actual existing data out of data that was passed in. See parent for explanation.
     *
     * @param mixed
     *
     * @return array|false
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'page_id'))
        {
            return false;
        }
    
        return array('kmkform__page' => $this->_getPageModel()->getPageById($id));
    }
    
    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'page_id = ' . $this->_db->quote($this->getExisting('page_id'));
    }
    
    /**
     * @return KomuKu_SimpleForms_Model_Page
     */
    protected function _getPageModel()
    {
        return XenForo_Model::create('KomuKu_SimpleForms_Model_Page');
    }
}