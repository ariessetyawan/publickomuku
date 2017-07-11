<?php /*3dff9f62d0e003c668822bdf63ff7db33a1e1461*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_DataWriter_AttachmentData extends XFCP_KomuKuYJB_Extend_XenForo_DataWriter_AttachmentData
{
    const DATA_TEMP_SLIDE_FILE  = 'tempSlideFile';
    const DATA_SLIDE_DATA       = 'tempSlideData';

    protected function _getFields()
    {
        return XenForo_Application::mapMerge(parent::_getFields(), array(
            'kmk_attachment_data' => array(
                'slide_width'  => array('type' => self::TYPE_UINT, 'default' => 0),
                'slide_height'  => array('type' => self::TYPE_UINT, 'default' => 0)
            )
        ));
    }

    protected function _postSave()
    {
        parent::_postSave();
        $data = $this->getMergedData();

        if ($tempFile = $this->getExtraData(self::DATA_TEMP_SLIDE_FILE))
        {
            if (!$this->_writeSlideImageFile($tempFile, $data))
            {
                throw new XenForo_Exception('Failed to write the attachment file.');
            }
        }
        elseif ($tempData = $this->getExtraData(self::DATA_SLIDE_DATA))
        {
            if (!$this->_writeSlideImageFileData($tempData, $data))
            {
                throw new XenForo_Exception('Failed to write the attachment file data.');
            }

            unset ($tempData);
        }
    }

    protected function _postDelete()
    {
        parent::_postDelete();
        $data = $this->getMergedData();

        $file = $this->_getClassifiedModel()->getGallerySlideImagePath($data);
        if (file_exists($file) && is_writable($file))
        {
            @unlink($file);
        }
    }

    protected function _writeSlideImageFile($tempFile, array $data)
    {
        if ($tempFile && is_readable($tempFile))
        {
            $classifiedModel = $this->_getClassifiedModel();
            $filePath = $classifiedModel->getGallerySlideImagePath($data);
            $directory = dirname($filePath);

            if (XenForo_Helper_File::createDirectory($directory, true))
            {
                return $this->_moveFile($tempFile, $filePath);
            }
        }

        return false;
    }

    protected function _writeSlideImageFileData($fileData, array $data)
    {
        $classifiedModel = $this->_getClassifiedModel();
        $filePath = $classifiedModel->getGallerySlideImagePath($data);
        $directory = dirname($filePath);

        if (XenForo_Helper_File::createDirectory($directory, true))
        {
            return @file_put_contents($filePath, $fileData);
        }

        return false;
    }

    /**
     * @return KomuKuYJB_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Classified');
    }
}