<?php /*1397a3e8380616e0a966d2ab4d6f533352b29361*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ViewPublic_Classified_Pay extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;
        $processors = $params['processors'];
        $payment = $params['payment'];

        if ($processors)
        {
            $params['forms'] = bdPaygate_Processor_Abstract::prepareForms(
                $processors, $payment['amount'], $payment['currency'],
                (string) new XenForo_Phrase('payment_for_classified_x', array('classified' => $params['classified']['title'], 'user' => $params['classified']['username'])),
                $params['itemId'], false, false, array(
                    bdPaygate_Processor_Abstract::EXTRA_RETURN_URL => XenForo_Link::buildPublicLink('canonical:classifieds/payment/completed', $params['classified'])
                )
            );
        }
    }
}