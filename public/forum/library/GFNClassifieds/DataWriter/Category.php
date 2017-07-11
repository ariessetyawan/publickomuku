<?php /*38551b67da4cc66f3399ceb09619441c43a06826*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_Category extends XenForo_DataWriter
{
    const OPTION_REBUILD_CACHE = 'rebuildCache';

    const DATA_ADVERT_TYPES = 'advertTypes';
    const DATA_FIELDS = 'fields';
    const DATA_PREFIXES = 'prefixes';
    const DATA_PACKAGES = 'packages';

    protected $_existingDataErrorPhrase = 'requested_category_not_found';

    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_REBUILD_CACHE => true
        );
    }

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_category' => array(
                'category_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'title' => array(
                    'type' => self::TYPE_STRING,
                    'maxLength' => 100,
                    'required' => true,
                    'requiredError' => 'please_enter_valid_title'
                ),
                'description' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'parent_category_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'depth' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'lft' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'rgt' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'classified_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'last_update' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'last_classified_title' => array(
                    'type' => self::TYPE_STRING,
                    'maxLength' => 100,
                    'default' => ''
                ),
                'last_classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'category_breadcrumb' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'thread_node_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'thread_prefix_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'complete_thread_prefix_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'enable_comment' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => true
                ),
                'require_location' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => false
                ),
                'require_prefix' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => false
                ),
                'advert_type_cache' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'package_cache' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'field_cache' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'prefix_cache' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'rating_criteria_cache' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'featured_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $categoryId = $this->_getExistingPrimaryKey($data);
        $category = $this->_getCategoryModel()->getCategoryById($categoryId);

        if (!$category)
        {
            return false;
        }

        return array('kmk_classifieds_category' => $category);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'category_id = ' . $this->_db->quote($this->getExisting('category_id'));
    }

    protected function _preSave()
    {
        if ($this->isChanged('thread_node_id') || $this->isChanged('thread_prefix_id') || $this->isChanged('complete_thread_prefix_id'))
        {
            if (!$this->get('thread_node_id'))
            {
                $this->set('thread_prefix_id', 0);
                $this->set('complete_thread_prefix_id', 0);
            }
            else
            {
                $forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($this->get('thread_node_id'));
                if (!$forum)
                {
                    $this->set('thread_prefix_id', 0);
                    $this->set('complete_thread_prefix_id', 0);
                }
                else
                {
                    /** @var XenForo_Model_ThreadPrefix $threadPrefixModel */
                    $threadPrefixModel = $this->getModelFromCache('XenForo_Model_ThreadPrefix');

                    if ($this->get('thread_prefix_id'))
                    {
                        if (!$threadPrefixModel->getPrefixIfInForum($this->get('thread_prefix_id'), $forum['node_id']))
                        {
                            $this->set('thread_prefix_id', 0);
                        }
                    }

                    if ($this->get('complete_thread_prefix_id'))
                    {
                        if (!$threadPrefixModel->getPrefixIfInForum($this->get('complete_thread_prefix_id'), $forum['node_id']))
                        {
                            $this->set('complete_thread_prefix_id', 0);
                        }
                    }
                }
            }
        }
    }

    protected function _postSave()
    {
        if ($this->isInsert() || $this->isChanged('display_order') || $this->isChanged('parent_category_id') || $this->isChanged('title'))
        {
            $this->_getCategoryModel()->rebuildCategoryStructure();
        }

        $categoryId = $this->get('category_id');

        $advertTypeIds = $this->getExtraData(self::DATA_ADVERT_TYPES);
        if (is_array($advertTypeIds))
        {
            $this->_getAssociationModel()->advertType()->updateAssociationByCategory($categoryId, $advertTypeIds);
        }

        $fieldIds = $this->getExtraData(self::DATA_FIELDS);
        if (is_array($fieldIds))
        {
            $this->_getAssociationModel()->field()->updateAssociationByCategory($categoryId, $fieldIds);
        }

        $prefixIds = $this->getExtraData(self::DATA_PREFIXES);
        if (is_array($prefixIds))
        {
            $this->_getAssociationModel()->prefix()->updateAssociationByCategory($categoryId, $prefixIds);
        }

        $packageIds = $this->getExtraData(self::DATA_PACKAGES);
        if (is_array($packageIds))
        {
            $this->_getAssociationModel()->package()->updateAssociationByCategory($categoryId, $packageIds);
        }

        if ($this->getOption(self::OPTION_REBUILD_CACHE) && ($this->isInsert() || $this->isChanged('parent_category_id')))
        {
            XenForo_Application::defer('Permission', array(), 'Permission', true);
        }
    }

    protected function _postDelete()
    {
        $categoryId = $this->get('category_id');
        $db = $this->_db;

        $db->update('kmk_classifieds_category', array(
            'parent_category_id' => $this->get('parent_category_id')
        ), 'parent_category_id = ' . $db->quote($categoryId));

        $this->_getCategoryModel()->rebuildCategoryStructure();

        if ($this->getOption(self::OPTION_REBUILD_CACHE))
        {
            $this->_getAssociationModel()->advertType()->removeAssociationByCategory($categoryId);
            $this->_getAssociationModel()->field()->removeAssociationByCategory($categoryId);
            $this->_getAssociationModel()->prefix()->removeAssociationByCategory($categoryId);
            $this->_getAssociationModel()->package()->removeAssociationByCategory($categoryId);

            XenForo_Application::defer('Permission', array(), 'Permission', true);
        }
    }

    /**
     * @return GFNClassifieds_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Category');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation
     */
    protected function _getAssociationModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation');
    }

    protected function _validateParentCategoryId(&$data)
    {
        if ($this->isUpdate() && $data != 0 && $data != $this->getExisting('parent_category_id'))
        {
            $possibleParents = $this->_getCategoryModel()->getPossibleParentCategories($this->getMergedData());
            if (!isset($possibleParents[$data]))
            {
                $this->error(new XenForo_Phrase('classifieds_please_select_valid_parent_category'), 'parent_category_id');
                return false;
            }
        }

        return true;
    }

    public function classifiedUpdate(GFNClassifieds_DataWriter_Classified $classified)
    {
        if ($classified->get('classified_state') != 'visible')
        {
            return;
        }

        if ($classified->isInsert() && $classified->get('feature_date'))
        {
            $this->updateFeaturedCount();
        }

        if ($classified->isUpdate() && $classified->isChanged('category_id'))
        {
            $this->updateClassifiedCount(1);
            $this->updateFeaturedCount();

            /** @var GFNClassifieds_DataWriter_Category $oldWriter */
            $oldWriter = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Category', self::ERROR_SILENT);
            if ($oldWriter->setExistingData($classified->getExisting('category_id')))
            {
                $oldWriter->classifiedRemoved($classified);
                $oldWriter->save();
            }
        }
        elseif ($classified->isChanged('classified_state'))
        {
            $this->updateClassifiedCount(1);
        }

        if ($classified->get('last_update') >= $this->get('last_update'))
        {
            $this->set('last_update', $classified->get('last_update'));
            $this->set('last_classified_title', $classified->get('title'));
            $this->set('last_classified_id', $classified->get('classified_id'));
        }

        if ($classified->isUpdate() && $classified->isChanged('classified_state'))
        {
            $this->updateFeaturedCount();
        }
    }

    public function classifiedRemoved(GFNClassifieds_DataWriter_Classified $classified)
    {
        if ($classified->getExisting('classified_state') != 'visible')
        {
            return;
        }

        $this->updateClassifiedCount(-1);
        $this->updateFeaturedCount();

        if ($this->get('last_classified_id') == $classified->get('classified_id'))
        {
            $this->updateLastUpdate();
        }
    }

    public function updateClassifiedCount($adjust = null)
    {
        if ($adjust === null)
        {
            $this->set('classified_count', $this->_db->fetchOne(
                'SELECT COUNT(*)
                FROM kmk_classifieds_classified
                WHERE category_id = ?
                AND classified_state = ?', array($this->get('category_id'), 'visible')
            ));
        }
        else
        {
            $this->set('classified_count', $this->get('classified_count') + $adjust);
        }
    }

    public function updateFeaturedCount($adjust = null)
    {
        if ($adjust === null)
        {
            $this->set('featured_count', $this->_db->fetchOne(
                'SELECT COUNT(*)
                FROM kmk_classifieds_classified
                WHERE category_id = ?
                AND classified_state = ?
                AND feature_date > 0', array($this->get('category_id'), 'visible')
            ));
        }
        else
        {
            $this->set('featured_count', $this->get('featured_count') + $adjust);
        }
    }

    public function updateLastUpdate()
    {
        $classified = $this->_db->fetchRow($this->_db->limit(
            'SELECT *
            FROM kmk_classifieds_classified
            WHERE category_id = ?
            AND classified_state = ?
            ORDER BY last_update DESC', 1
        ), array($this->get('category_id'), 'visible'));

        if (!$classified)
        {
            $this->set('classified_count', 0);
            $this->set('last_update', 0);
            $this->set('last_classified_title', 0);
            $this->set('last_classified_id', 0);
        }
        else
        {
            $this->set('last_update', $classified['last_update']);
            $this->set('last_classified_title', $classified['title']);
            $this->set('last_classified_id', $classified['classified_id']);
        }
    }
} 