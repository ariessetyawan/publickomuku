<?php /*c1aa1381c47025626bd2cf5f2ae2390a856328fe*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_InlineMod_Classified extends XenForo_Model
{
    public $enableLogging = true;

    public function canEditClassifieds(array $classifiedIds, &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);
        return $this->canEditClassifiedsData($classifieds, $categories, $errorKey, $viewingUser);
    }

    public function canEditClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if (!$classifiedModel->canEditClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function applyClassifiedPrefix(array $classifiedIds, $prefixId, &$unchangedClassifiedIds = array(), array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $prefixModel = $this->_getPrefixModel();
        $prefixPerms = array();

        if (!$this->canEditClassifieds($classifiedIds, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifiedIds as $classifiedId)
        {
            /** @var GFNClassifieds_DataWriter_Classified $writer */
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
            if (!$writer->setExistingData($classifiedId))
            {
                continue;
            }

            $classified = $writer->getMergedData();
            $categoryId = $classified['category_id'];

            if (!isset($prefixPerms[$categoryId]))
            {
                $prefixPerms[$categoryId] = $prefixModel->verifyPrefixIsUsable($prefixId, $categoryId);
            }

            if (!$prefixPerms[$categoryId])
            {
                $unchangedClassifiedIds[] = $classifiedId;
                continue;
            }

            $writer->set('prefix_id', $prefixId);
            $writer->save();

            if ($classified['prefix_id'] != $prefixId)
            {
                if ($classified['prefix_id'])
                {
                    $phrase = new XenForo_Phrase('classifieds_prefix_' . $classified['prefix_id']);
                    $oldValue = $phrase->render();
                }
                else
                {
                    $oldValue = '-';
                }

                if ($this->enableLogging)
                {
                    XenForo_Model_Log::logModeratorAction('classified', $classified, 'prefix', array('old' => $oldValue));
                }
            }
        }

        return true;
    }

    public function featureClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canFeatureClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds as $classified)
        {
            $this->_getClassifiedModel()->featureClassified($classified);
        }

        return true;
    }

    public function canFeatureClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if (!$classifiedModel->canFeatureUnfeatureClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function unfeatureClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canUnfeatureClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds as $classified)
        {
            $this->_getClassifiedModel()->unfeatureClassified($classified);
        }

        return true;
    }

    public function canUnfeatureClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if (!$classifiedModel->canFeatureUnfeatureClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function openClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canOpenClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds as $classified)
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified');
            $writer->setExistingData($classified);
            $writer->set('classified_state', 'visible');
            $writer->save();

            XenForo_Model_Log::logModeratorAction('classified', $classified, 'close');
        }

        return true;
    }

    public function canOpenClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if (!$classifiedModel->canOpenClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function closeClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canCloseClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds as $classified)
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified');
            $writer->setExistingData($classified);
            $writer->set('classified_state', 'closed');
            $writer->save();

            XenForo_Model_Log::logModeratorAction('classified', $classified, 'open');
        }

        return true;
    }

    public function canCloseClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if (!$classifiedModel->canCloseClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function approveClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canApproveClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateClassifiedsClassifiedState($classifieds, $categories, 'visible', 'moderated');
        return true;
    }

    public function canApproveClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if ($classified['classified_state'] == 'moderated' && !$classifiedModel->canApproveClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function unapproveClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canUnapproveClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateClassifiedsClassifiedState($classifieds, $categories, 'moderated', 'visible');
        return true;
    }

    public function canUnapproveClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if ($classified['classified_state'] == 'visible' && !$classifiedModel->canUnapproveClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function deleteClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $options = array_merge(
            array(
                'deleteType' => '',
                'reason' => ''
            ), $options
        );

        if (!$options['deleteType'])
        {
            throw new XenForo_Exception('No deletion type specified.');
        }

        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canDeleteClassifiedsData($classifieds, $options['deleteType'], $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds AS $classified)
        {
            /** @var GFNClassifieds_DataWriter_Classified $writer */
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
            if (!$writer->setExistingData($classified))
            {
                continue;
            }

            if ($options['deleteType'] == 'hard')
            {
                $writer->delete();
            }
            else
            {
                $writer->setExtraData($writer::DATA_DELETE_REASON, $options['reason']);
                $writer->set('classified_state', 'deleted');
                $writer->save();
            }

            if ($this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction(
                    'classified', $classified, 'delete_' . $options['deleteType'], array('reason' => $options['reason'])
                );
            }
        }

        return true;
    }

    public function canDeleteClassifieds(array $classifiedIds, $deleteType = 'soft', &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);
        return $this->canDeleteClassifiedsData($classifieds, $deleteType, $categories, $errorKey, $viewingUser);
    }

    public function canDeleteClassifiedsData(array $classifieds, $deleteType, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if ($classified['classified_state'] != 'deleted' && !$classifiedModel->canDeleteClassified($classified, $category, $deleteType, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function undeleteClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canUndeleteClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateClassifiedsClassifiedState($classifieds, $categories, 'visible', 'deleted');
        return true;
    }

    public function canUndeleteClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if ($classified['classified_state'] == 'deleted' && !$classifiedModel->canUndeleteClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function reassignClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $options = array_merge(
            array(
                'userId' => '',
                'username' => ''
            ), $options
        );

        if (!$options['userId'] || !$options['username'])
        {
            throw new XenForo_Exception('No user ID/username specified.');
        }

        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canReassignClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds AS $classified)
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
            $writer->setExistingData($classified);

            if (!$writer->get('classified_id'))
            {
                continue;
            }

            $writer->bulkSet(array(
                'user_id' => $options['userId'],
                'username' => $options['username']
            ));

            if ($writer->save() && $this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction(
                    'classified', $classified, 'reassign', array('from' => $writer->getExisting('username'))
                );
            }
        }

        return true;
    }

    public function canReassignClassifieds(array $classifiedIds, &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);
        return $this->canReassignClassifiedsData($classifieds, $categories, $errorKey, $viewingUser);
    }

    public function canReassignClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if (!$classifiedModel->canReassignClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function moveClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $options = array_merge(
            array(
                'categoryId' => 0
            ), $options
        );

        if (!$options['categoryId'] )
        {
            throw new XenForo_Exception('No category ID specified.');
        }

        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canMoveClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds AS $classified)
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);
            $writer->setExistingData($classified);

            if (!$writer->get('classified_id'))
            {
                continue;
            }

            $writer->set('category_id', $options['categoryId']);

            if ($writer->save() && $this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction(
                    'classified', $classified, 'edit', array('category_id' => $writer->getExisting('category_id'))
                );
            }
        }

        return true;
    }

    public function canMoveClassifieds(array $classifiedIds, &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);
        return $this->canMoveClassifiedsData($classifieds, $categories, $errorKey, $viewingUser);
    }

    public function canMoveClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if (!$classifiedModel->canEditClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function bumpClassifieds(array $classifiedIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        list ($classifieds, $categories) = $this->getClassifiedsAndParentData($classifiedIds);

        if (empty($options['skipPermissions']) && !$this->canBumpClassifiedsData($classifieds, $categories, $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($classifieds as $classified)
        {
            $this->_getClassifiedModel()->bumpClassified($classified);
        }

        return true;
    }

    public function canBumpClassifiedsData(array $classifieds, array $categories, &$errorKey = '', array $viewingUser = null)
    {
        if (!$classifieds)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $classifiedModel = $this->_getClassifiedModel();

        foreach ($classifieds as $classified)
        {
            $category = $categories[$classified['category_id']];
            if ($classified['classified_state'] == 'visible' && !$classifiedModel->canBumpClassified($classified, $category, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function getClassifiedsAndParentData(array $classifiedIds)
    {
        $classifieds = $this->_getClassifiedModel()->getClassifiedsByIds($classifiedIds);
        $categoryIds = array();

        foreach ($classifieds as $classified)
        {
            $categoryIds[$classified['category_id']] = true;
        }

        $categories = $this->_getCategoryModel()->getCategoriesByIds(array_keys($categoryIds), array(
            'permissionCombinationId' => XenForo_Visitor::getInstance()->permission_combination_id
        ));

        $this->_getCategoryModel()->bulkSetCategoryPermCache(null, $categories, 'category_permission_cache');
        return array($classifieds, $categories);
    }

    protected function _updateClassifiedsClassifiedState(array $classifieds, array $categories, $newState, $expectedOldState = false)
    {
        switch ($newState)
        {
            case 'visible':
                switch (strval($expectedOldState))
                {
                    case 'visible': return;
                    case 'moderated': $logAction = 'approve'; break;
                    case 'deleted': $logAction = 'undelete'; break;
                    default: $logAction = 'undelete'; break;
                }
                break;

            case 'moderated':
                switch (strval($expectedOldState))
                {
                    case 'visible': $logAction = 'unapprove'; break;
                    case 'moderated': return;
                    case 'deleted': $logAction = 'unapprove'; break;
                    default: $logAction = 'unapprove'; break;
                }
                break;

            case 'deleted':
                switch (strval($expectedOldState))
                {
                    case 'visible': $logAction = 'delete_soft'; break;
                    case 'moderated': $logAction = 'delete_soft'; break;
                    case 'deleted': return;
                    default: $logAction = 'delete_soft'; break;
                }
                break;

            default: return;
        }

        foreach ($classifieds as $classified)
        {
            if ($expectedOldState && $classified['classified_state'] != $expectedOldState)
            {
                continue;
            }

            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified', XenForo_DataWriter::ERROR_SILENT);

            if (!$writer->setExistingData($classified))
            {
                continue;
            }

            $writer->set('classified_state', $newState);
            $writer->save();

            if ($this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction('classified', $classified, $logAction);
            }
        }
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Classified');
    }

    /**
     * @return GFNClassifieds_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Category');
    }

    /**
     * @return GFNClassifieds_Model_Prefix
     */
    protected function _getPrefixModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Prefix');
    }
}