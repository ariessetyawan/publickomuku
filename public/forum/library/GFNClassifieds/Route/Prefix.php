<?php /*b85da71c8073212d30ba72b98743b9e856315537*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Route_Prefix extends GFNCore_Route_PrefixBackbone
{
    protected $_major = 'classifieds';

    protected $_copyrightPhrase = 'classifieds_copyright';

    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $this->resolveActionAsPageNumber($routePath, $request);
        return parent::match($action, $request, $router);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $page = XenForo_Link::getPageNumberAsAction('', $extraParams);

        if (empty($action))
        {
            $action = $page;
        }
        else
        {
            $action = rtrim($action, '/') . '/' . $page;
        }

        $return = parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);

        if ($return === false && $action)
        {
            $return = XenForo_Link::buildBasicLink($outputPrefix, $action, $extension);
        }

        return $return;
    }

    protected function _getRouteClasses()
    {
        return array(
            'index' => 'GFNClassifieds_Route_Prefix_Index',
            'featured' => 'controller:GFNClassifieds_ControllerPublic_Home::featured',
            'create' => 'controller:GFNClassifieds_ControllerPublic_Home::create',
            'add' => 'controller:GFNClassifieds_ControllerPublic_Home::create',
            'inline-mod' => array(
                'default' => 'controller:GFNClassifieds_ControllerPublic_InlineMod_Classified',
                'comments' => 'controller:GFNClassifieds_ControllerPublic_InlineMod_Comment',
                'trader-ratings' => 'controller:GFNClassifieds_ControllerPublic_InlineMod_TraderRating'
            ),
            'categories' => 'GFNClassifieds_Route_Prefix_Categories',
            'default' => 'GFNClassifieds_Route_Prefix_Classifieds',
            'package-info' => 'controller:GFNClassifieds_ControllerPublic_Home::package-info',
            'fetch-location' => 'controller:GFNClassifieds_ControllerPublic_Home::fetch-location',
            'items' => 'GFNClassifieds_Route_Prefix_Classifieds',
            'traders' => 'GFNClassifieds_Route_Prefix_Traders',
            'account' => 'controller:GFNClassifieds_ControllerPublic_Account',
            'activity' => 'controller:GFNClassifieds_ControllerPublic_Activity->index',
            'search' => 'controller:GFNClassifieds_ControllerPublic_Search',
            'watched' => array(
                'default' => 'controller:GFNClassifieds_ControllerPublic_ClassifiedWatch',
                'categories' => 'controller:GFNClassifieds_ControllerPublic_CategoryWatch'
            ),
            'filter-menu' => 'controller:GFNClassifieds_ControllerPublic_Home::filter-menu'
        );
    }

    public function resolveActionAsPageNumber($action, Zend_Controller_Request_Http $request)
    {
        if (preg_match('#page-(\d+)$#i', $action, $match))
        {
            $action = str_replace($match[0], '', $action);
            $request->setParam('page', $match[1]);
        }

        return $action;
    }
}