<?php /*12d37a39badd96a7de2ae70a6c318c2e1df91491*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Classified_Location extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        XenForo_Application::set('view', $this);
    }
}