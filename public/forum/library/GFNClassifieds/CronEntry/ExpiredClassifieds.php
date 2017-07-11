<?php /*b9e049cf2121eaaa63c052c326fa098316bf704f*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_CronEntry_ExpiredClassifieds extends GFNCore_CronEntry_Abstract
{
    protected function _run()
    {
        /** @var GFNClassifieds_Model_Classified $classifiedModel */
        $classifiedModel = $this->_getModelFromCache('GFNClassifieds_Model_Classified');

        $classifieds = $classifiedModel->getClassifieds(array(
            'expire_date' => array('>=<', 1, XenForo_Application::$time),
            'expired' => false
        ));

        foreach ($classifieds as $classified)
        {
            /** @var GFNClassifieds_DataWriter_Classified $writer */
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
            $writer->setExistingData($classified, true);

            if ($classified['classified_state'] == 'pending')
            {
                $writer->set('classified_state', 'closed');
            }
            else
            {
                $writer->set('classified_state', 'expired');
            }

            $writer->save();
        }

        $classifieds = $classifiedModel->getClassifiedsAboutToExpire();
    }
}