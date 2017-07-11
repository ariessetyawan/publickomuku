<?php /*aa11eafaaf02f0e07fc9a50897b91f2cc455be0a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 8
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_DataWriter_Location extends XenForo_DataWriter
{
    protected function _getFields()
    {
        return array(
            'kmk_classifieds_classified_location' => array(
                'classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'location_private' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'latitude' => array(
                    'type' => self::TYPE_FLOAT,
                    'default' => 0
                ),
                'longitude' => array(
                    'type' => self::TYPE_FLOAT,
                    'default' => 0
                ),
                'route' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'neighborhood' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'sublocality_level_1' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'locality' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'administrative_area_level_2' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'administrative_area_level_1' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'country' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $classifiedId = $this->_getExistingPrimaryKey($data, 'classified_id');
        if (!$classifiedId)
        {
            return false;
        }

        $location = $this->_getClassifiedModel()->getLocationById($classifiedId);
        if (!$location)
        {
            return false;
        }

        return array('kmk_classifieds_classified_location' => $location);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'classified_id = ' . $this->_db->quote($this->getExisting('classified_id'));
    }

    protected function _preSave()
    {
        if (!$this->get('latitude') || !$this->get('longitude') || !$this->get('country'))
        {
            $this->error(new XenForo_Phrase('invalid_location_data_specified'));
        }
    }

    /**
     * @return KomuKuYJB_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Classified');
    }
}