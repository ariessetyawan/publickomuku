<?php /*e2bed52202641484bd23b0b8167fd22a2d0d3120*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Route_PrefixAdmin_Traders_Ratings_Criteria implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action  = $router->resolveActionWithStringParam($routePath, $request, 'criteria_id');
        return $router->getRouteMatch('GFNClassifieds_ControllerAdmin_TraderRatingCriteria', $action, 'classifiedTraderRatingCri');
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'criteria_id');
    }
}