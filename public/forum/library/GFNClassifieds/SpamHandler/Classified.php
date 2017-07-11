<?php /*e349acf6fdf3012bd107edc471de2183decfc1a2*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_SpamHandler_Classified extends XenForo_SpamHandler_Abstract
{
    public function cleanUpConditionCheck(array $user, array $options)
    {
        return !empty($options['action_threads']);
    }

    public function cleanUp(array $user, array &$log, &$errorKey)
    {
        /** @var GFNClassifieds_Model_Classified $classifiedModel */
        $classifiedModel = $this->getModelFromCache('GFNClassifieds_Model_Classified');

        $classifieds = $classifiedModel->getClassifieds(array(
            'user_id' => $user['user_id'],
            'moderated' => true,
            'deleted' => true,
            'pending' => true
        ));

        if ($classifieds)
        {
            $classifiedIds = array_keys($classifieds);
            $deleteType = (XenForo_Application::get('options')->spamMessageAction == 'delete' ? 'hard' : 'soft');

            $log['classified'] = array(
                'deleteType' => $deleteType,
                'classifiedIds' => $classifiedIds
            );

            /** @var GFNClassifieds_Model_InlineMod_Classified $inlineModModel */
            $inlineModModel = $this->getModelFromCache('GFNClassifieds_Model_InlineMod_Classified');
            $inlineModModel->enableLogging = false;

            return $inlineModModel->deleteClassifieds(
                $classifiedIds, array('deleteType' => $deleteType, 'skipPermissions' => true), $errorKey
            );
        }

        return true;
    }

    public function restore(array $log, &$errorKey = '')
    {
        if ($log['deleteType'] == 'soft')
        {
            /** @var GFNClassifieds_Model_InlineMod_Classified $inlineModModel */
            $inlineModModel = $this->getModelFromCache('GFNClassifieds_Model_InlineMod_Classified');
            $inlineModModel->enableLogging = false;

            return $inlineModModel->undeleteClassifieds(
                $log['classifiedIds'], array('skipPermissions' => true), $errorKey
            );
        }

        return true;
    }
}