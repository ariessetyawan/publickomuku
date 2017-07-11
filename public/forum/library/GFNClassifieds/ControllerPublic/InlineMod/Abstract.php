<?php /*47f5791bf19afb5e6361ba5ab6f0ccb9e7bc2e0c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class GFNClassifieds_ControllerPublic_InlineMod_Abstract extends XenForo_ControllerPublic_InlineMod_Abstract
{
    /**
     * @return GFNClassifieds_ControllerHelper_Model
     */
    public function models()
    {
        return $this->getHelper('GFNClassifieds_ControllerHelper_Model');
    }
}