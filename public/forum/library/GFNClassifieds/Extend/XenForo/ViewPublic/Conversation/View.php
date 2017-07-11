<?php /*23b4befe213bbc6bee5ee95fea15a07c0e358861*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_ViewPublic_Conversation_View extends XFCP_GFNClassifieds_Extend_XenForo_ViewPublic_Conversation_View
{
    public function renderHtml()
    {
        XenForo_Application::set('view', $this);
        parent::renderHtml();
    }
}