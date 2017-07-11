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
class GFNClassifieds_Extend_XenForo_Model_Counters extends XFCP_GFNClassifieds_Extend_XenForo_Model_Counters
{
    public function rebuildBoardTotalsCounter()
    {
        parent::rebuildBoardTotalsCounter();
        $this->rebuildClassifiedTotalsCounter();
    }

    public function getClassifiedTotalsCounter()
    {
        /** @var GFNClassifieds_Model_Classified $classifiedModel */
        $classifiedModel = $this->getModelFromCache('GFNClassifieds_Model_Classified');
        /** @var GFNClassifieds_Model_Package $packageModel */
        $packageModel = $this->getModelFromCache('GFNClassifieds_Model_Package');

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