<?php /*a3487b0aa9242857acec429af65fb15a56b1dc0d*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class GFNClassifieds_ControllerAdmin_Abstract extends XenForo_ControllerAdmin_Abstract
{
    abstract protected function _getAdminPermission();

    protected function _preDispatch($action)
    {
        if ($this->_getAdminPermission())
        {
            $this->assertAdminPermission($this->_getAdminPermission());
        }
    }

    public function actionIndex()
    {
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
            $this->_buildLink(trim($this->_input->filterSingle('_origRoutePath', XenForo_Input::STRING), '/') . '/list')
        );
    }

    /**
     * @return GFNClassifieds_ControllerHelper_Model
     */
    public function models()
    {
        return $this->getHelper('GFNClassifieds_ControllerHelper_Model');
    }
}