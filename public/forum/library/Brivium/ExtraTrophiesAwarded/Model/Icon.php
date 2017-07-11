<?php
class Brivium_ExtraTrophiesAwarded_Model_Icon extends XenForo_Model
{	
	public static $imageQuality = 85;
	
	public function uploadIcon(XenForo_Upload $upload, $trophyId)
	{	
		$options = XenForo_Application::get('options');
		
		$size = $options->BRETA_iconSize;
		$iconsProcessed = self::_applyIcon($upload ,$size);
		
		$this->_writeIcon($trophyId, $iconsProcessed);
	}
	protected function _writeIcon($trophyId, $tempFile)
	{
		$filePath = $this->getIconFilePath($trophyId);
		$directory = dirname($filePath);
		if (XenForo_Helper_File::createDirectory($directory, true) && is_writable($directory))
		{
			if (file_exists($filePath))
			{
				@unlink($filePath);
			}

			return XenForo_Helper_File::safeRename($tempFile, $filePath);
		}
		else
		{
			return false;
		}
	}
	public function getIconFilePath($trophyId, $externalDataPath = null)
	{
		if ($externalDataPath === null)
		{
			$externalDataPath = XenForo_Helper_File::getExternalDataPath();
		}

		return sprintf('%s/trophyIcon/'.$trophyId.'.jpg', $externalDataPath);
	}
	
	protected static function _applyIcon($upload, $size) 
	{			
		if (!$upload->isValid()) {
			throw new XenForo_Exception($upload->getErrors(), true);
		}

		if (!$upload->isImage()) {
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
		};

		$imageType = $upload->getImageInfoField('type');
		if (!in_array($imageType, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
		}

		$outputFiles = array();
		$fileName = $upload->getTempFile();
		$imageType = $upload->getImageInfoField('type');
		$outputType = $imageType;
		$width = $upload->getImageInfoField('width');
		$height = $upload->getImageInfoField('height');
		
		$newTempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xfa');
		$image = XenForo_Image_Abstract::createFromFile($fileName, $imageType);
		if (!$image) {
			continue;
		}

		if ($size > 0) {
			$image->thumbnailFixedShorterSide($size);

			if ($image->getOrientation() != XenForo_Image_Abstract::ORIENTATION_SQUARE) {
				$x = floor(($image->getWidth() - $size) / 2);
				$y = floor(($image->getHeight() - $size) / 2);
				$image->crop($x, $y, $size, $size);
			}
		}

		$image->output($outputType, $newTempFile, self::$imageQuality);
		unset($image);

		$icons = $newTempFile;
			
		return $icons;
	}
}