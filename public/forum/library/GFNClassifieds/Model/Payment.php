<?php /*d1b183dcbbd36471f3dd97e3beaa72a6259d7c6d*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_Payment extends XenForo_Model
{
    public function getPaymentInfoById($paymentId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_payment
            WHERE payment_id = ?', $paymentId
        );
    }

    public function getLatestPaymentInfoByClassified($classifiedId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_payment
            WHERE classified_id = ?
            ORDER BY payment_id DESC
            LIMIT 1', $classifiedId
        );
    }
}