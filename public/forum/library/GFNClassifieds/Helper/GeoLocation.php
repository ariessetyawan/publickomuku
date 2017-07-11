<?php /*62a128cb7a88bcb75600234cde850b56582791e9*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Helper_GeoLocation
{
    protected static $_earthRadius = 6371;

    public static function getDistance(array $pt1, array $pt2)
    {
        $pt1 = self::getGeoLocation($pt1);
        $pt2 = self::getGeoLocation($pt2);

        $lat = deg2rad($pt1['lat'] - $pt2['lat']);
        $lng = deg2rad($pt1['lng'] - $pt2['lng']);

        return self::$_earthRadius * 2 * asin(sqrt(pow(sin($lat / 2), 2) + cos(deg2rad($pt1['lat'])) * cos(deg2rad($pt2['lat'])) * pow(sin($lng / 2), 2)));
    }

    public static function getDistanceMySqlStatement(Zend_Db_Adapter_Abstract $db, $latField, $lngField, array $point)
    {
        $point = self::getGeoLocation($point);
        return strval(self::$_earthRadius * 2) . ' * ASIN(SQRT(POW(SIN(RADIANS(' . $latField . ' - ' . $db->quote($point['lat']) . ') / 2), 2) + COS(RADIANS(' . $latField . ')) * COS(RADIANS(' . $db->quote($point['lat']) . ')) * POW(SIN(RADIANS(' . $lngField . ' - ' . $db->quote($point['lng']) . ') / 2), 2)))';
    }

    public static function getGeoLocation(array $location)
    {
        $return = array('lat' => 0, 'lng' => 0);

        if (isset($location['lat']))
        {
            $return['lat'] = $location['lat'];
        }
        elseif (isset($location['latitude']))
        {
            $return['lat'] = $location['latitude'];
        }

        if (isset($location['lng']))
        {
            $return['lng'] = $location['lng'];
        }
        elseif (isset($location['longitude']))
        {
            $return['lng'] = $location['longitude'];
        }
        elseif (isset($location['long']))
        {
            $return['lng'] = $location['long'];
        }

        if (empty($return['lat']) && empty($return['lng']))
        {
            if (count($location) != 2)
            {
                throw new XenForo_Exception(new XenForo_Phrase('unable_to_determine_geolocation'), true);
            }

            $location = array_values($location);
            $return['lat'] = $location[0];
            $return['lng'] = $location[1];
        }
        elseif (empty($return['lat']) || empty($return['lng']))
        {
            throw new XenForo_Exception(new XenForo_Phrase('unable_to_determine_geolocation'), true);
        }

        return $return;
    }
}