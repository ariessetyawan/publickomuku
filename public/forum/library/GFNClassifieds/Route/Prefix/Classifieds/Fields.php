<?php /*622756ced3f17baf9d5643442b319f058a81a529*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Route_Prefix_Classifieds_Fields implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerOrStringParam($routePath, $request, 'field', 'field');
        return $router->getRouteMatch('GFNClassifieds_ControllerPublic_Classified', 'field-' . $action);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (isset($extraParams['fieldId']))
        {
            $data['field_id'] = $extraParams['fieldId'];
            unset ($extraParams['fieldId']);
        }
        elseif (isset($extraParams['field']))
        {
            $data['field_id'] = $extraParams['field'];
            unset ($extraParams['field']);
        }

        return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'field_id');
    }
}