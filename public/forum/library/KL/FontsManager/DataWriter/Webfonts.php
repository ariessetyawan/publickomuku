<?php

/**
 * KL_FontsManager_DataWriter_Fonts
 *
 *	@author: Nerian
 *  @last_edit:	05.09.2015
 */

class KL_FontsManager_DataWriter_Webfonts extends XenForo_DataWriter {
      	protected function _getFields() {
        return array(
			'kmk_kl_fm_webfonts' => array(
				'id'                            => array('type' => self::TYPE_UINT,     'autoIncrement' => true),
                'title'                         => array('type' => self::TYPE_STRING,   'required' => true),
                'active'                        => array('type' => self::TYPE_UINT,     'default' => 1)
            )
        );
    }
    
    /*
     * @return array
     */
	protected function _getExistingData($data) {
		if (!is_array($data) || !isset($data['id']))
            return false;

		return array('kmk_kl_fm_webfonts' => $this->_getFontModel()->getWebfontById($data['id']));
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
}