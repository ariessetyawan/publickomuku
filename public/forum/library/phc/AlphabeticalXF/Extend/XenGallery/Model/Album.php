<?php

class phc_AlphabeticalXF_Extend_XenGallery_Model_Album extends XFCP_phc_AlphabeticalXF_Extend_XenGallery_Model_Album
{
	public function prepareAlbumConditions(array $conditions, array &$fetchOptions)
    {
        $res = parent::prepareAlbumConditions($conditions, $fetchOptions);

        $res = $this->_getAlphaModel()->getAlphaStatement('album.album_title', $res);

        return $res;
    }

    protected function _getAlphaModel()
    {
        return $this->getModelFromCache('phc_AlphabeticalXF_Model_Alpha');
    }
}
