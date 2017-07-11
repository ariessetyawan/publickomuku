<?php /*9e533ff1518764d5582d23e0b25a618801efbf95*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ModerationQueueHandler_Classified extends XenForo_ModerationQueueHandler_Abstract
{
    public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
    {
        /** @var KomuKuYJB_Model_Classified $classifiedModel */
        $classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');
        $classifieds = $classifiedModel->getClassifiedsByIds($contentIds, array(
            'join' => $classifiedModel::FETCH_CATEGORY | $classifiedModel::FETCH_USER,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        $return = array();

        foreach ($classifieds as $classified)
        {
            $classified['permissions'] = XenForo_Permission::unserializePermissions($classified['category_permission_cache']);
            $canManage = true;

            if (!$classifiedModel->canViewClassifiedAndContainer($classified, $classified, $null, $viewingUser, $classified['permissions']))
            {
                $canManage = false;
            }
            elseif (!XenForo_Permission::hasContentPermission($classified['permissions'], 'editAny')
                || !XenForo_Permission::hasContentPermission($classified['permissions'], 'deleteAny')
            )
            {
                $canManage = false;
            }

            if ($canManage)
            {
                $return[$classified['classified_id']] = array(
                    'message' => $classified['description'],
                    'user' => array(
                        'user_id' => $classified['user_id'],
                        'username' => $classified['username']
                    ),
                    'title' => $classified['title'],
                    'link' => XenForo_Link::buildPublicLink('classifieds', $classified),
                    'contentTypeTitle' => new XenForo_Phrase('classified'),
                    'titleEdit' => true
                );
            }
        }

        return $return;
    }

    public function approveModerationQueueEntry($contentId, $description, $title)
    {
        $description = XenForo_Helper_String::autoLinkBbCode($description);

        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
        $writer->setExistingData($contentId);
        $writer->set('classified_state', 'visible');
        $writer->set('title', $title);
        $writer->set('description', $description);

        if ($writer->save())
        {
            XenForo_Model_Alert::alert(
                $writer->get('user_id'), $writer->get('user_id'), $writer->get('username'),
                'classified', $writer->get('classified_id'), 'approve'
            );

            XenForo_Model_Log::logModeratorAction('classified', $writer->getMergedData(), 'approve');
            return true;
        }

        return false;
    }

    public function deleteModerationQueueEntry($contentId)
    {
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
        $writer->setExistingData($contentId);
        $writer->set('classified_state', 'deleted');

        if ($writer->save())
        {
            XenForo_Model_Log::logModeratorAction('classified', $writer->getMergedData(), 'delete_soft', array('reason' => ''));
            return true;
        }

        return false;
    }
}