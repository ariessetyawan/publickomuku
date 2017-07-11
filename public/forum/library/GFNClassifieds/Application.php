<?php /*2e70fb9600de0113b4229cd7b4125eb4ecd5a5ce*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Application
{
    public static $version = '1.0.0 RC 6';
    public static $versionId = 1000056; // abbccde = a.b.c d (alpha: 1, beta: 3, RC: 5, stable: 7, PL: 9) e

    public static $contentTypes = array('classified', 'classified_comment', 'classified_trader_rating');

    public static function init(GFNCore_Application $core)
    {
        $core->setTemplateHelper('classifiedPrefix', 'GFNClassifieds_Template_Helper', 'getPrefixTitle');
        $core->setTemplateHelper('classifiedPrefixGroup', 'GFNClassifieds_Template_Helper', 'getPrefixGroupTitle');

        $core->setTemplateHelper('classifiedFieldTitle', 'GFNClassifieds_Template_Helper', 'getFieldTitle');
        $core->setTemplateHelper('classifiedFieldValue', 'GFNClassifieds_Template_Helper', 'getFieldValue');
        $core->setTemplateHelper('classifiedFieldIsHint', 'GFNClassifieds_Template_Helper', 'fieldIsHint');

        $core->setTemplateHelper('classifiedExpiresIn', 'GFNClassifieds_Template_Helper', 'getExpiresInPhrase');
        $core->setTemplateHelper('classifiedFeaturedImage',  'GFNClassifieds_Template_Helper', 'getFeaturedImage');

        $core->setTemplateHelper('classifiedPrice', 'GFNClassifieds_Template_Helper', 'getPrice');
        $core->setTemplateHelper('classifiedAdvertTypeBadge', 'GFNClassifieds_Template_Helper', 'getAdvertTypeBadge');
        $core->setTemplateHelper('classifiedPriceByAdvertType', 'GFNClassifieds_Template_Helper', 'getPriceByAdvertType');

        $core->setTemplateHelper('classifiedTraderRatingCriteriaTitle', 'GFNClassifieds_Template_Helper', 'getTraderRatingCriteriaTitle');
        $core->setTemplateHelper('classifiedTraderRatingCriteriaFeedback', 'GFNClassifieds_Template_Helper', 'getTraderRatingCriteriaFeedback');

        $core->setTemplateHelper('classifiedConversationResponseTime', 'GFNClassifieds_Template_Helper', 'getConversationResponseTime');

        $core->preloadRegistry('classifiedPrefixes', 'classifiedFields', 'classifiedAdvertTypes', 'classifiedTotals');

        XenForo_Model_Attachment::$dataColumns .= ', data.slide_width, data.slide_height';
    }

    public static function arrayFilterKeys(array $data, array $keys)
    {
        // this version will not warn on undefined indexes: return array_intersect_key($data, array_flip($keys));

        $array = array();

        foreach ($keys AS $key)
        {
            if (!isset($data[$key]))
            {
                if (substr($key, -1) === '*')
                {
                    $key = substr($key, 0, -1);

                    foreach ($data as $k => $v)
                    {
                        if (strpos($k, $key) === 0)
                        {
                            $array[$k] = $v;
                        }
                    }
                }
                else
                {
                    $array[$key] = null;
                }
            }
            else
            {
                $array[$key] = $data[$key];
            }
        }

        return $array;
    }

    public static function getInstalledVersionId()
    {
        return GFNCore_Application::getInstalledVersion('GFNClassifieds');
    }
} 