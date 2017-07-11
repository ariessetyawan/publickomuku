<?php

class KL_FontsManager_BbCode_Formatter_Font extends XFCP_KL_FontsManager_BbCode_Formatter_Font {
    protected $_fonts = null;
    
    public function getTags() {
        $tags = parent::getTags();
        
        if(isset($tags['font'])) {
            $tags['font'] = array(
                'hasOption' => true,
                //'optionRegex' => '/^[. \-]+$/i', // regex matched to HTML->BB code regex
				'callback' => array($this, 'renderTagFamily'),
            );
        }
        
        return $tags;
    }
    
    public function getFonts()
	{
		if ($this->_fonts == null)
		{
            $fontModel = XenForo_Model::create('KL_FontsManager_Model_Fonts');
            $this->_fonts = $fontModel->getFontData();
		}

        return $this->_fonts;
    }
    
    public function renderTagFamily(array $tag, array $rendererStates) {
		$text = $this->renderSubTree($tag['children'], $rendererStates);
        
		if (trim($text) === '') {return $text;}

		$fonts = $this->getFonts();
		$options = XenForo_Application::get('options');
		$family = $this->_searchForFont($tag['option']);
		if (!empty($family)) {
			return $this->_wrapInHtml('<span style="font-family: ' . htmlspecialchars($family) . '">', '</span>', $text);
		}
		else if(in_array($tag['option'], $fonts['webfont_keys']) == $options->kl_fm_mode) {
			return $this->_wrapInHtml('<script>loadWebfont(\'' . addslashes(str_replace(' ', '+', $tag['option'])) . '\');</script><span style="font-family: \'' . htmlspecialchars(addslashes($tag['option'])) . '\'">', '</span>', $text);
		}
		else return $text;
    }
	
	private function _searchForFont($id) {
		foreach ($this->getFonts()['fonts'] as $val) {
		   if (strtolower($val['title']) === strtolower($id)) {
			   return $val['family'];
		   }
		}
		return '';
	}
}