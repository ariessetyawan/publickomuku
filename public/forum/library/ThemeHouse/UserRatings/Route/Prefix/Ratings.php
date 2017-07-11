<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_Route_Prefix_Ratings implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'rating_id');

        return $router->getRouteMatch('ThemeHouse_UserRatings_ControllerPublic_Ratings', $action, 'ratings');
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'rating_id');
    }
}
