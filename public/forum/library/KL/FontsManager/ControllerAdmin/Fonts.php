<?php

/**
 * KL_FontsManager_ControllerAdmin_Fonts
 *
 * @author: Nerian
 * @last_edit:    05.07.2016
 */
class KL_FontsManager_ControllerAdmin_Fonts extends XenForo_ControllerAdmin_Abstract
{

    /* 
     * @last_edit:	01.09.2015
     * @params: templates
     * @return: responseView
     */
    public function actionList()
    {
        $fontModel = $this->_getFontModel();
        $fonts = $fontModel->getFonts();

        foreach ($fonts as &$font) {
            $font['type_string'] = new XenForo_Phrase('kl_fm_type_' . $font['type']);
			$font['additional_data'] = !empty($font['additional_data']) ? implode(',',json_decode($font['additional_data'])) : null;
        }

        $params = array(
            'fonts' => $fonts
        );

        return $this->responseView('KL_FontsManager_ViewAdmin_Fonts_List', 'kl_fm_list', $params);
    }

    /* 
     * @last_edit:	02.10.2015
     * @params: templates
     * @return: responseView
     */
    public function actionUpdate()
    {
        $fonts = $this->_input->filterSingle('update', XenForo_Input::ARRAY_SIMPLE);
        $delete = $this->_input->filterSingle('delete', XenForo_Input::ARRAY_SIMPLE);
		$newFonts = $this->_input->filterSingle('new', XenForo_Input::ARRAY_SIMPLE);
		
		/* Add new fonts */
		foreach($newFonts as $newFont) {
			if(isset($newFont['title']) && !empty($newFont['title'])) {
				$font = array(
					'position' => $newFont['position'],
					'type' => $newFont['type'],
					'active' => $newFont['active'],
					'title' => $newFont['title']
				);

				switch($newFont['type']) {
					case 'google' :
						$font['additional_data'] = $newFont['additionalOptions'];
					case 'custom' :
						$font['family'] = str_replace('font-family:', '', $newFont['family']);
						break;	
					case 'local' :
						$font['family'] = str_replace('.woff', '', $newFont['filename']);
						break;
					default:
						return $this->responseNoPermission();
				}
			}
			/* Remove the trash */
			else if($newFont['type'] == 'local' && !empty($newfont['filename']) && file_exists(XenForo_Helper_File::getExternalDataPath().'/fonts/'.$newFont['filename'])) {
				unlink(XenForo_Helper_File::getExternalDataPath().'/fonts/'.$newFont['filename']);
			}
			
			if(isset($font['title']) && !empty($font['family'])) {
				$dw = XenForo_DataWriter::create('KL_FontsManager_DataWriter_Fonts');
				$dw->bulkSet($font);
				$dw->save();
			}
		}
		
		/* Update existing fonts */
        foreach ($fonts as $font) {
            $dw = XenForo_DataWriter::create('KL_FontsManager_DataWriter_Fonts');

            $dw->setExistingData($font);
            // DELETE
            if (in_array($font['id'], $delete)) {
                $dw->delete();
            } // UPDATE
            else {
                if (!isset($font['active'])) {
                    $font['active'] = 0;
                }
                $dw->bulkSet($font);
            }

            $dw->save();
        }
        
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('kl-fm/list'),
            new XenForo_Phrase('changes_saved')
        );
    }
    
    
        
    /* 
     * @last_edit: 02.10.2015
     * @params: templates
     * @return: responseView
     */
    public function actionUpload() {
		// You need to add server side validation and better error handling here

		$data = array();
		$uploaddir = XenForo_Helper_File::getExternalDataPath().'/fonts/';
		
		if(isset($_FILES) && !empty($_FILES))
		{  
			$error = false;
			$files = array();
			foreach($_FILES as $file)
			{
				$temp = explode(".", $file['name']);
			 	$file['name'] = 'font'.round(microtime(true)) . '.' . end($temp);
				if(move_uploaded_file($file['tmp_name'], $uploaddir . basename($file['name'])))
				{
					$files[] = $file['name'];
				}
				else
				{
					$error = true;
				}
			}
			$data = ($error) ? array('error' => 'There was an error uploading your files') : array('files' => $files);
		}
		else
		{
			$data = array('success' => 'Form was submitted', 'formData' => $_FILES);
		}
		
		if(isset($_POST['FileToDelete']) && !empty($_POST['FileToDelete']) && file_exists($uploaddir . $_POST['FileToDelete'])) {
			unlink($uploaddir . $_POST['FileToDelete']);
		}

		return $this->responseView('','kl_fm_upload_response',array('data'=>json_encode($data)));
    }
    
    /*
     * @last_edit:	17.09.2015
     * @params: templates
     * @return: responseView
     */
    public function actionWebfonts() {
        $fontModel = $this->_getFontModel();
        $fonts = $fontModel->getWebfonts();

        $params = array(
            'fonts' => $fonts
        );

        return $this->responseView('KL_FontsManager_ViewAdmin_Fonts_Webfonts', 'kl_fm_webfonts', $params);
    }
    
    /*
     * @last_edit:	17.09.2015
     * @params: templates
     * @return: responseView
     */
    public function actionWebfontsUpdate() {
        $fonts = $this->_input->filterSingle('update', XenForo_Input::ARRAY_SIMPLE);
        $delete = $this->_input->filterSingle('delete', XenForo_Input::ARRAY_SIMPLE);
        $new   = $this->_input->filterSingle('new', XenForo_Input::ARRAY_SIMPLE);
        $mode = $this->_input->filterSingle('mode', XenForo_Input::UINT);

		$fontList = array();
		/* Update Existing Entries */
        foreach ($fonts as $font) {
            $dw = XenForo_DataWriter::create('KL_FontsManager_DataWriter_Webfonts');

            $dw->setExistingData($font);
            // DELETE
            if (in_array($font['id'], $delete)) {
                $dw->delete();
                $dw->save();
            } // UPDATE
            else {
                if (!isset($font['active'])) {
                    $font['active'] = 0;
                }
				$fontList[] = $font['title'];
                $dw->bulkSet($font);
                $dw->save();
            }
        }
        
		/* Add New Entries */
        foreach($new as $font) {
			if(!in_array($font['title'], $fontList)) {
            	$dw = XenForo_DataWriter::create('KL_FontsManager_DataWriter_Webfonts');
            	$dw->bulkSet($font);
            	$dw->save();
			}
        }
        
		/* Save Blacklist/Whitelist Mode */
        $modeDw = XenForo_DataWriter::create('XenForo_DataWriter_Option');
        $modeDw->setExistingData(array('option_id' => 'kl_fm_mode'));
        $modeDw->set('option_value',$mode);
        $modeDw->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('kl-fm/webfonts')
        );
    }

    /* 
     * TYPE: HELPER 
     * @return KL_EditorPostTemplates_Model_Editor
     */
    protected function _getFontModel()
    {
        return $this->getModelFromCache('KL_FontsManager_Model_Fonts');
    }
}