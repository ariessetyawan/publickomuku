<?php /*db4917168c6a5ad97bfdbaad0f016b59bfa75f31*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Route_PrefixAdmin extends GFNCore_Route_PrefixBackbone
{
    protected $_major = 'classifieds';

    protected $_copyrightPhrase = 'classifieds_copyright';

    protected function _getRouteClasses()
    {
        return array(
            'index' => 'controller:KomuKuYJB_ControllerAdmin_Home->index',
            'options' => 'KomuKuYJB_Route_PrefixAdmin_Options',
            'categories' => 'KomuKuYJB_Route_PrefixAdmin_Categories',
            'prefixes' => array(
                'default' => 'KomuKuYJB_Route_PrefixAdmin_Prefixes',
                'groups' => 'KomuKuYJB_Route_PrefixAdmin_PrefixGroups'
            ),
            'fields' => 'KomuKuYJB_Route_PrefixAdmin_Fields',
            'packages' => 'KomuKuYJB_Route_PrefixAdmin_Packages',
            'advert-types' => 'KomuKuYJB_Route_PrefixAdmin_AdvertTypes',
            'traders' => array(
                'fields' => 'KomuKuYJB_Route_PrefixAdmin_Traders_Fields',
                'ratings' => array(
                    'criteria' => 'KomuKuYJB_Route_PrefixAdmin_Traders_Ratings_Criteria'
                )
            ),
            'rating' => array(
                'criteria' => 'KomuKuYJB_Route_PrefixAdmin_Rating_Criteria'
            )
        );
    }
} 