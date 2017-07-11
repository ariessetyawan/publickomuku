<?php /*e99db4b9b691a8281d5c612280fd343a880e5cc4*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_Classified_Field extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        XenForo_Application::set('view', $this);
    }
}