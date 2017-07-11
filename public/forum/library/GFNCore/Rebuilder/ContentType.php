<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Rebuilder_ContentType extends GFNCore_Rebuilder
{
    /**
     * @var XenForo_Model_ContentType
     */
    protected $_model;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    protected $_contentTypes = array();

    protected function _construct()
    {
        $this->_model = XenForo_Model::create('XenForo_Model_ContentType');
        $this->_db = XenForo_Application::getDb();
    }

    public function add($contentType, $addOnId)
    {
        $this->_contentTypes[$addOnId][] = $contentType;
    }

    public function clear($addOnId)
    {
        unset ($this->_contentTypes[$addOnId]);
    }

    protected function _destruct()
    {
        if ($this->_contentTypes)
        {
            $db = $this->_db;
            $insert = new GFNCore_Db_Schema_Insert('kmk_content_type', true);

            foreach ($this->_contentTypes as $addOnId => $contentTypes)
            {
                $contentTypes = array_unique($contentTypes);
                $available = $db->fetchCol('SELECT content_type FROM kmk_content_type_field WHERE content_type IN (' . $db->quote($contentTypes) . ')');

                foreach ($available as $contentType)
                {
                    $insert->row(array('content_type' => $contentType, 'addon_id' => $addOnId, 'fields' => ''));
                }
            }

            if ($insert->rows)
            {
                $insert->execute();
            }
        }

        $this->_model->rebuildContentTypeCache();
    }
}