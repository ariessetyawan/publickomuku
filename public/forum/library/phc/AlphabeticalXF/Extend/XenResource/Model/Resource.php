<?php

// Team NullXF
class phc_AlphabeticalXF_Extend_XenResource_Model_Resource extends XFCP_phc_AlphabeticalXF_Extend_XenResource_Model_Resource
{
    public function prepareResourceConditions(array $conditions, array &$fetchOptions)
    {
        $res = parent::prepareResourceConditions($conditions, $fetchOptions);

        $res = $this->_getAlphaModel()->getAlphaStatement('resource.title', $res);

        return $res;
    }

    protected function _getAlphaModel()
    {
        return $this->getModelFromCache('phc_AlphabeticalXF_Model_Alpha');
    }
}