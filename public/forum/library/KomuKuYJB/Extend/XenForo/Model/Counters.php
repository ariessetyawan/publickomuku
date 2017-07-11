<?php /*cfac3215f34cef450f8365db29fd211316e67e66*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Extend_XenForo_Model_Counters extends XFCP_KomuKuYJB_Extend_XenForo_Model_Counters
{
    public function rebuildBoardTotalsCounter()
    {
        parent::rebuildBoardTotalsCounter();
        $this->rebuildClassifiedTotalsCounter();
    }

    public function getClassifiedTotalsCounter()
    {
        /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $classifiedModel = $this->getModelFromCache('KomuKuYJB_Model_Classified');
        /** @var KomuKuYJB_Model_Package $packageModel */
        $packageModel = $this->getModelFromCache('KomuKuYJB_Model_Package');

        $output = $classifiedModel->getClassifiedTotalItemCounts();
        $output['defaultCurrency'] = $packageModel->getDefaultCurrency();
        return $output;
    }

    public function rebuildClassifiedTotalsCounter()
    {
        $counter = $this->getClassifiedTotalsCounter();

        GFNCore_Registry::set('classifiedTotals', $counter);
        return $counter;
    }
}