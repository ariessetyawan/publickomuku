<?php

/**
 * KL_FontsManager_Helpers
 *
 * @author: Nerian
 * @last_edit:    05.07.2016
 */
class KL_FontsManager_Helpers 
{
    /*
     * @return string
     *  @last_edit:	05.07.2016
     */
    public static function helperFonts()
    {
        $fontModel = XenForo_Model::create('KL_FontsManager_Model_Fonts');
        $fonts = $fontModel->getActiveFonts();
		
		foreach($fonts as $key => $font) {
			if($font['position'] == 0)
				unset($fonts[$key]);
		}

        return json_encode($fonts);
    }

    /*
     * @return string
     *  @last_edit:	05.07.2016
     */
    public static function helperAdditionalFonts($returnJson = false, $tinymce = false)
    {
        $fontModel = XenForo_Model::create('KL_FontsManager_Model_Fonts');
        $fonts = $fontModel->getActiveFonts();
        $return = '';
		$returnMCE = array('styles' => array(), 'links' => array());

        $googleFonts = array();
        $fontStack = array();

        foreach ($fonts as $font) {
            if ($font['type'] === 'google') {
				$googleFonts[$font['id']] = $font['family'];
				if($font['additional_data']) {
					$googleFonts[$font['id']] .= ':'.implode(',',json_decode($font['additional_data']));
				}
            } else if ($font['type'] != 'default') {
                $fontStack[] = $font;
            }
        }

        $return .= '<style type="text/css">';

        foreach($fontStack as $font) {
            if($font['type'] === 'custom') {
				$value = "font[face='".$font['title']."']{font-family: ".$font['family'].";}";
                $return .= $value."\r\n";
				$returnMCE['styles'][] = $value;
            }
            else {
                $path = self::_getRelativePath(
                    XenForo_Application::getInstance()->getRootDir(),
                    XenForo_Helper_File::getExternalDataPath() . '/fonts/'.str_replace('\'','',$font['family']).".woff"
                );
				
				$value = '@font-face {font-family: \''
                            .trim($font['family'],"'")
                            .'\';src: url(\''.$path
                            ."') format('woff');}";
                $return .= $value."\r\n";
				$returnMCE['styles'][] = $value;
				
				$value = '@font-face {font-family: \''
                    .$font['title']
                    .'\';src: url(\''.$path
                    ."') format('woff');}";
                $return .= $value."\r\n";
				$returnMCE['styles'][] = $value;
				
				$value = "font[face='".$font['title']."']{font-family: '".trim($font['family'],"'")."';}";
                $return .= $value."\r\n";
				$returnMCE['styles'][] = $value;
            }
        }

        $return .= '</style>';

        if(!empty($googleFonts)) {
			$link = 'https://fonts.googleapis.com/css?family='
            . str_replace( //Turn Whitespace to Plus-Sign
                '%20',
                '+',
                urlencode( //URLENCODE
                    str_replace( //Strip ' from String
                        '\'', '',
                        implode( //Generate Font List
                            '|',
                            $googleFonts
                        )
                    )
                )
            );
			
            $return .= '<link href="'.$link.'" rel="stylesheet" type="text/css">';
			$returnMCE['links'][] = $link;
        }

		
        if ($returnJson) {
            return json_encode($tinymce ? $returnMCE : $return);
        }

        return $tinymce ? $returnMCE : $return;
    }


    private static function _getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}