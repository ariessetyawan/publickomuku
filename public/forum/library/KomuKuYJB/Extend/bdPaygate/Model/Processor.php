<?php /*d26ee851968b92cc4f9979e419f70a0c0eb87264*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 6
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_bdPaygate_Model_Processor extends XFCP_KomuKuYJB_Extend_bdPaygate_Model_Processor
{
    protected function _processIntegratedAction($action, $user, $data, bdPaygate_Processor_Abstract $processor, $amount, $currency)
    {
        switch ($action)
        {
            case 'classified_open':
                return $this->_processClassifiedOpen($user, $data, $processor, $amount, $currency);

            case 'classified_renew':
                return $this->_processClassifiedRenew($user, $data, $processor, $amount, $currency);

            default:
                return parent::_processIntegratedAction($action, $user, $data, $processor, $amount, $currency);
        }
    }

    protected function _processClassifiedOpen($user, $data, bdPaygate_Processor_Abstract $processor, $amount, $currency)
    {
        $classifiedId = $data[0];
        $paymentId = $data[1];

        $payment = $this->_getClassifiedPaymentModel()->getPaymentInfoById($paymentId);
        if (!$payment)
        {
            return '[ERROR] Could not find the specified payment record.';
        }

        if ($payment['amount'] != $amount || $payment['currency'] != $currency)
        {
            return '[ERROR] Payment amount and / or currency does not match.';
        }

        if ($payment['classified_id'] != $classifiedId)
        {
            return '[ERROR] Classified ID associated with the payment does not match.';
        }

        if ($payment['is_renewal'] || $payment['payment_complete'])
        {
            return '[ERROR] Invalid record ID specified.';
        }

        if ($payment['user_id'] != $user['user_id'])
        {
            return '[ERROR] User ID mismatch.';
        }

        /** @var KomuKuYJB_DataWriter_Classified $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        if (!$writer->setExistingData($classifiedId))
        {
            return '[ERROR] Could not find associated classified.';
        }

        $package = $writer->getPackageInfo();
        if ($package['always_moderate_create'])
        {
            $writer->set('classified_state', 'moderated');
        }
        else
        {
            $writer->set('classified_state', 'visible');
        }

        $payment = new KomuKuYJB_Eloquent_Payment($payment);
        $payment['payment_date'] = XenForo_Application::$time;
        $payment['payment_complete'] = 1;
        $payment['payment_refund'] = 0;

        $writer->save();
        return 'Payment successfully processed.';
    }

    protected function _processClassifiedRenew($user, $data, bdPaygate_Processor_Abstract $processor, $amount, $currency)
    {
        $classifiedId = $data[0];
        $paymentId = $data[1];

        $payment = $this->_getClassifiedPaymentModel()->getPaymentInfoById($paymentId);
        if (!$payment)
        {
            return '[ERROR] Could not find the specified payment record.';
        }

        if ($payment['amount'] != $amount || $payment['currency'] != $currency)
        {
            return '[ERROR] Payment amount and / or currency does not match.';
        }

        if ($payment['classified_id'] != $classifiedId)
        {
            return '[ERROR] Classified ID associated with the payment does not match.';
        }

        if (!$payment['is_renewal'] || $payment['payment_complete'])
        {
            return '[ERROR] Invalid record ID specified.';
        }

        if ($payment['user_id'] != $user['user_id'])
        {
            return '[ERROR] User ID mismatch.';
        }

        /** @var KomuKuYJB_DataWriter_Classified $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        if (!$writer->setExistingData($classifiedId))
        {
            return '[ERROR] Could not find associated classified.';
        }

        $package = $writer->getPackageInfo();
        if ($package['always_moderate_renewal'])
        {
            $writer->set('classified_state', 'moderated');
        }
        else
        {
            $writer->set('classified_state', 'visible');
        }

        $payment = new KomuKuYJB_Eloquent_Payment($payment);
        $payment['payment_date'] = XenForo_Application::$time;
        $payment['payment_complete'] = 1;
        $payment['payment_refund'] = 0;

        $writer->save();
        return 'Payment successfully processed.';
    }

    protected function _revertIntegratedAction($action, $user, $data, bdPaygate_Processor_Abstract $processor, $amount, $currency)
    {
        switch ($action)
        {
            case 'classified_open':
                return $this->_revertClassifiedOpen($user, $data, $processor, $amount, $currency);

            case 'classified_renew':
                return $this->_revertClassifiedRenew($user, $data, $processor, $amount, $currency);

            default:
                return parent::_revertIntegratedAction($action, $user, $data, $processor, $amount, $currency);
        }
    }

    protected function _revertClassifiedOpen($user, $data, bdPaygate_Processor_Abstract $processor, $amount, $currency)
    {
        $classifiedId = $data[0];
        $paymentId = $data[1];

        $payment = $this->_getClassifiedPaymentModel()->getPaymentInfoById($paymentId);
        if (!$payment)
        {
            return '[ERROR] Could not find the specified payment record.';
        }

        if ($payment['amount'] != $amount || $payment['currency'] != $currency)
        {
            return '[ERROR] Payment amount and / or currency does not match.';
        }

        if ($payment['classified_id'] != $classifiedId)
        {
            return '[ERROR] Classified ID associated with the payment does not match.';
        }

        if ($payment['is_renewal'] || !$payment['payment_complete'])
        {
            return '[ERROR] Invalid record ID specified.';
        }

        if ($payment['user_id'] != $user['user_id'])
        {
            return '[ERROR] User ID mismatch.';
        }

        /** @var KomuKuYJB_DataWriter_Classified $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        if (!$writer->setExistingData($classifiedId))
        {
            return '[ERROR] Could not find associated classified.';
        }

        $payment = new KomuKuYJB_Eloquent_Payment($payment);
        $payment['payment_complete'] = 0;
        $payment['payment_refund'] = 1;

        $writer->set('classified_state', 'closed');
        $writer->save();
        return 'Payment successfully reverted.';
    }

    protected function _revertClassifiedRenew($user, $data, bdPaygate_Processor_Abstract $processor, $amount, $currency)
    {
        $classifiedId = $data[0];
        $paymentId = $data[1];

        $payment = $this->_getClassifiedPaymentModel()->getPaymentInfoById($paymentId);
        if (!$payment)
        {
            return '[ERROR] Could not find the specified payment record.';
        }

        if ($payment['amount'] != $amount || $payment['currency'] != $currency)
        {
            return '[ERROR] Payment amount and / or currency does not match.';
        }

        if ($payment['classified_id'] != $classifiedId)
        {
            return '[ERROR] Classified ID associated with the payment does not match.';
        }

        if (!$payment['is_renewal'] || !$payment['payment_complete'])
        {
            return '[ERROR] Invalid record ID specified.';
        }

        if ($payment['user_id'] != $user['user_id'])
        {
            return '[ERROR] User ID mismatch.';
        }

        /** @var KomuKuYJB_DataWriter_Classified $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        if (!$writer->setExistingData($classifiedId))
        {
            return '[ERROR] Could not find associated classified.';
        }

        $payment = new KomuKuYJB_Eloquent_Payment($payment);
        $payment['payment_complete'] = 0;
        $payment['payment_refund'] = 1;

        $writer->set('classified_state', 'closed');
        $writer->save();
        return 'Payment successfully reverted.';
    }

    /**
     * @return KomuKuYJB_Model_Payment
     */
    protected function _getClassifiedPaymentModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Payment');
    }
}