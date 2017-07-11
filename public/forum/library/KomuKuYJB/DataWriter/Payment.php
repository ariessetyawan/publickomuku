<?php /*1abf2e1dc3159aab1a26280f2a9fa87c6622d236*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_DataWriter_Payment extends XenForo_DataWriter
{
    protected function _getFields()
    {
        return array(
            'kmk_classifieds_payment' => array(
                'payment_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'payment_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'package_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'amount' => array(
                    'type' => self::TYPE_FLOAT,
                    'required' => true
                ),
                'currency' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 3
                ),
                'is_renewal' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'payment_complete' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'payment_refund' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $paymentId = $this->_getExistingPrimaryKey($data);
        if (!$paymentId)
        {
            return false;
        }

        $payment = $this->_getPaymentModel()->getPaymentInfoById($paymentId);
        if (!$payment)
        {
            return false;
        }

        return array('kmk_classifieds_payment' => $payment);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'payment_id = ' . $this->_db->quote($this->getExisting('payment_id'));
    }

    /**
     * @return KomuKuYJB_Model_Payment
     */
    protected function _getPaymentModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Payment');
    }
}