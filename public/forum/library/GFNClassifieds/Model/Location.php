<?php /*fe329110746c6d6cf617ad6d92495467dcd1471f*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_Location extends XenForo_Model
{
    public function getClassifiedIdsWithin(array $pt1, array $pt2)
    {
        $distance = GFNClassifieds_Helper_GeoLocation::getDistance($pt1, $pt2);
        $db = $this->_getDb();

        return $db->fetchAll(
            'SELECT classified_id
            FROM kmk_classifieds_classified_location
            WHERE (' . GFNClassifieds_Helper_GeoLocation::getDistanceMySqlStatement($db, 'latitude', 'longitude', $pt1) . ') <= ?
            AND (' . GFNClassifieds_Helper_GeoLocation::getDistanceMySqlStatement($db, 'latitude', 'longitude', $pt2) . ') <= ?', array($distance, $distance)
        );
    }
}