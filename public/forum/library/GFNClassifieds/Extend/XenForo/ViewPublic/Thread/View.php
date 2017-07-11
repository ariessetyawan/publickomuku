<?php /*155aec93cdfb294e38fbf0dc2f8b705dbff0a70c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_ViewPublic_Thread_View extends XFCP_GFNClassifieds_Extend_XenForo_ViewPublic_Thread_View
{
    public function renderHtml()
    {
        XenForo_Application::set('view', $this);
        parent::renderHtml();
    }
}