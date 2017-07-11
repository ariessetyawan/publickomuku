<?php

/**
 * KL_FontsManager_DataWriter_Fonts
 *
 *	@author: Nerian
 *  @last_edit:	05.07.2016
 */

class KL_FontsManager_DataWriter_Fonts extends XenForo_DataWriter {
      	protected function _getFields() {
        return array(
			'kmk_kl_fm_fonts' => array(
				'id'                            => array('type' => self::TYPE_UINT,     'autoIncrement' => true),
                'title'                         => array('type' => self::TYPE_STRING,   'required' => true),
                'position'                      => array('type' => self::TYPE_UINT,     'default' => 10),
                'active'                        => array('type' => self::TYPE_UINT,     'default' => 1),
                'type'                          => array('type' => self::TYPE_STRING,   'default' => ''),
                'family'                        => array('type' => self::TYPE_STRING,   'default' => ''),
				'additional_data'				=> array('type' => self::TYPE_STRING,	'default' => '')
            )
        );
    }
    
    /*
     * @return array
     */
	protected function _getExistingData($data) {
		if (!is_array($data) || !isset($data['id']))
            return false;
		return array('kmk_kl_fm_fonts' => $this->_getFontModel()->getFontById($data['id']));
	}
    
    /*
     * @return string
     */
    protected function _getUpdateCondition($tableName) {
        return 'id = '.$this->_db->quote($this->getExisting('id'));
    }
        
    /* 
     * TYPE: HELPER 
     * @return KL_EditorPostTemplates_Model_Editor
     */
    protected function _getFontModel() {
        return $this->getModelFromCache('KL_FontsManager_Model_Fonts');
    } 
	
	/* Cache Update */
	protected function _postDelete() {
		$this->_getFontModel()->rebuildCache();
	}
	
	/* Cache Update */
	protected function _postSave() {
		$this->_getFontModel()->rebuildCache();
	}
}