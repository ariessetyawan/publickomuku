<?php /*056552bf8e499cad9e6c2f162623355548898010*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_Classified extends XenForo_DataWriter
{
    const DATA_ATTACHMENT_HASH          = 'attachmentHash';
    const DATA_CLASSIFIED_ICON_HASH     = 'classifiedIconHash';
    const DATA_GALLERY_HASH             = 'galleryHash';
    const DATA_THREAD_WATCH_DEFAULT     = 'threadWatchDefault';
    const DATA_DELETE_REASON            = 'deleteReason';

    const OPTION_IS_RENEWAL             = 'isRenewal';
    const OPTION_USER_EDITING           = 'userEditing';
    const OPTION_SET_IP_ADDRESS         = 'setIpAddress';
    const OPTION_INDEX_FOR_SEARCH       = 'indexForSearch';
    const OPTION_PUBLISH_FEED           = 'publishFeed';

    const OPTION_DELETE_THREAD_ACTION   = 'deleteThreadAction';
    const OPTION_DELETE_THREAD_TITLE_TEMPLATE = 'deleteThreadTitleTemplate';
    const OPTION_DELETE_ADD_POST = 'deleteAddPost';

    const OPTION_CLOSE_THREAD_ACTION   = 'closeThreadAction';
    const OPTION_CLOSE_THREAD_TITLE_TEMPLATE = 'closeThreadTitleTemplate';
    const OPTION_CLOSE_ADD_POST = 'closeAddPost';

    const OPTION_OPEN_ADD_POST = 'openAddPost';

    const OPTION_COMPLETE_THREAD_ACTION   = 'completeThreadAction';
    const OPTION_COMPLETE_THREAD_TITLE_TEMPLATE = 'completeThreadTitleTemplate';
    const OPTION_COMPLETE_ADD_POST = 'completeAddPost';

    protected $_isFirstVisible = false;

    protected $_requiresPayment = false;

    /**
     * @var false|GFNClassifieds_Eloquent_Payment
     */
    protected $_payment;

    protected function _getDefaultOptions()
    {
        $options = GFNClassifieds_Options::getInstance();

        return array(
            self::OPTION_IS_RENEWAL => false,
            self::OPTION_USER_EDITING => false,
            self::OPTION_SET_IP_ADDRESS => true,
            self::OPTION_INDEX_FOR_SEARCH => true,
            self::OPTION_PUBLISH_FEED => true,

            self::OPTION_DELETE_THREAD_ACTION => $options->get('classifiedDeleteThreadAction', 'action'),
            self::OPTION_DELETE_THREAD_TITLE_TEMPLATE => $options->get('classifiedDeleteThreadAction', 'update_title') ? $options->get('classifiedDeleteThreadAction', 'title_template') : '',
            self::OPTION_DELETE_ADD_POST => $options->get('classifiedDeleteThreadAction', 'add_post'),

            self::OPTION_CLOSE_THREAD_ACTION => $options->get('classifiedCloseThreadAction', 'action'),
            self::OPTION_CLOSE_THREAD_TITLE_TEMPLATE => $options->get('classifiedCloseThreadAction', 'update_title') ? $options->get('classifiedCloseThreadAction', 'title_template') : '',
            self::OPTION_CLOSE_ADD_POST => $options->get('classifiedCloseThreadAction', 'add_post'),
            self::OPTION_OPEN_ADD_POST => $options->get('classifiedCloseThreadAction', 'open_add_post'),

            self::OPTION_COMPLETE_THREAD_ACTION => $options->get('classifiedCompleteThreadAction', 'action'),
            self::OPTION_COMPLETE_THREAD_TITLE_TEMPLATE => $options->get('classifiedCompleteThreadAction', 'update_title') ? $options->get('classifiedCompleteThreadAction', 'title_template') : '',
            self::OPTION_COMPLETE_ADD_POST => $options->get('classifiedCompleteThreadAction', 'add_post')
        );
    }

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_classified' => array(
                'classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'title' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 100,
                    'requiredError' => 'please_enter_valid_title'
                ),
                'tag_line' => array(
                    'type' => self::TYPE_STRING,
                    'required' => GFNClassifieds_Options::getInstance()->get('tagLineRequired'),
                    'maxLength' => 100,
                    'requiredError' => 'please_enter_valid_tag_line'
                ),
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'username' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 50,
                    'requiredError' => 'please_enter_valid_name'
                ),
                'classified_state' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'allowedValues' => array('visible', 'moderated', 'pending', 'deleted', 'closed', 'completed', 'expired', 'on_hold'),
                    'default' => 'visible'
                ),
                'feature_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'classified_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => XenForo_Application::$time
                ),
                'complete_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'expire_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'description' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'requiredError' => 'please_enter_valid_description'
                ),
                'category_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true,
                    'verification' => array('$this', '_validateCategoryId')
                ),
                'advert_type_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'package_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'discussion_thread_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'prefix_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'price' => array(
                    'type' => self::TYPE_FLOAT,
                    'default' => 0
                ),
                'price_base_currency' => array(
                    'type' => self::TYPE_FLOAT,
                    'default' => 0
                ),
                'currency' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'requiredError' => 'please_select_valid_currency'
                ),
                'likes' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'like_users' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'custom_classified_fields' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'ip_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'warning_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'warning_message' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'update_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'comment_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'renewal_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'attach_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'gallery_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'view_count' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'last_update' => array(
                    'type' => self::TYPE_UINT,
                    'default' => XenForo_Application::$time
                ),
                'last_comment_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'last_comment_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'last_comment_user_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'last_comment_username' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'complete_user_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'complete_username' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'featured_image_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'featured_image_attachment_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'had_first_visible' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'last_bump_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => XenForo_Application::$time
                ),
                'tags' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $classifiedId = $this->_getExistingPrimaryKey($data);
        if (!$classifiedId)
        {
            return false;
        }

        $classified = $this->_getClassifiedModel()->getClassifiedById($classifiedId);
        if (!$classified)
        {
            return false;
        }

        return array('kmk_classifieds_classified' => $classified);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'classified_id = ' . $this->getExisting('classified_id');
    }

    protected function _preSave()
    {
        if ($this->isUpdate() && !$this->isChanged('update_count'))
        {
            $this->set('update_count', $this->get('update_count') + 1);
        }

        if ($this->get('classified_state') === null)
        {
            $this->set('classified_state', 'visible');
        }

        if (!$this->get('had_first_visible') && $this->get('classified_state') == 'visible')
        {
            $this->set('had_first_visible', 1);
            $this->_isFirstVisible = true;

            if (!$this->isChanged('classified_date'))
            {
                $this->set('classified_date', XenForo_Application::$time);
                $this->set('last_update', XenForo_Application::$time);
            }
        }

        $category = $this->getCategory();

        if ($this->isChanged('advert_type_id') || $this->isChanged('category_id'))
        {
            $advertTypeCache = $category['advert_type_cache'];

            if (!in_array($this->get('advert_type_id'), $advertTypeCache))
            {
                $this->error(new XenForo_Phrase('please_select_valid_advert_type'), 'advert_type_id');
            }
        }

        if ($this->isChanged('package_id') || $this->isChanged('category_id'))
        {
            $packageCache = $category['package_cache'];

            if (!in_array($this->get('package_id'), $packageCache))
            {
                $this->error(new XenForo_Phrase('please_select_valid_package'), 'package_id');
            }
        }

        if ($this->isChanged('prefix_id') || $this->isChanged('category_id'))
        {
            $prefixCache = $category['prefix_cache'];
            if (!$prefixCache)
            {
                $prefixCache = array();
            }

            $exists = false;

            foreach ($prefixCache as $group)
            {
                if (in_array($this->get('prefix_id'), $group))
                {
                    $exists = true;
                    break;
                }
            }

            if (!$exists)
            {
                $this->set('prefix_id', 0);
            }
        }

        if ($this->isChanged('category_id'))
        {
            if ($this->isUpdate() && !is_array($this->_updateCustomFields))
            {
                $fieldModel = $this->_getFieldModel();

                $this->_updateCustomFields = $this->_filterValidFields(
                    $fieldModel->getFieldValues($this->get('classified_id')),
                    $fieldModel->getFieldsForEdit($this->get('category_id'))
                );

                $this->set('custom_classified_fields', $this->_updateCustomFields);
            }
        }

        $this->set('last_update', XenForo_Application::$time);

        if ($this->isUpdate() && $this->getOption(self::OPTION_IS_RENEWAL))
        {
            $this->set('renewal_count', $this->get('renewal_count') + 1);
        }

        if ($this->isUpdate() && $this->getOption(self::OPTION_USER_EDITING) && $this->isChanged('description'))
        {
            $this->set('update_count', $this->get('update_count') + 1);
        }

        $package = $this->getPackageInfo();
        if (!$package)
        {
            $this->error(new XenForo_Phrase('please_select_valid_package'), 'package_id');
            return;
        }

        if ($this->isInsert() && $package['auto_feature_item'])
        {
            $this->set('feature_date', XenForo_Application::$time);
        }

        if ($this->isInsert() || $this->getOption(self::OPTION_IS_RENEWAL))
        {
            $this->set('last_bump_date', XenForo_Application::$time);

            if ($package['advert_duration'] == 0)
            {
                $this->set('expire_date', 0);
            }
            else
            {
                $expireDate = $this->get('expire_date');
                if ($expireDate < XenForo_Application::$time)
                {
                    $expireDate = XenForo_Application::$time;
                }

                $this->set('expire_date', $expireDate + ($package['advert_duration'] * 86400));
            }

            if ($this->getOption(self::OPTION_IS_RENEWAL))
            {
                if ($package['always_moderate_renewal'])
                {
                    $this->set('classified_state', 'moderated');
                }
                else
                {
                    $this->set('classified_state', 'visible');
                }
            }

            $this->_calculatePayment($package);
        }

        if ($this->isChanged('currency') || $this->isChanged('price'))
        {
            $this->set('price_base_currency', $this->get('price'));
        }

        if ($this->_locationWriter)
        {
            $this->getLocationWriter()->preSave();

            if ($this->getLocationWriter()->hasErrors())
            {
                $this->_errors += $this->getLocationWriter()->getErrors();
            }
        }
    }

    protected function _postSave()
    {
        $postSaveChanges = array();

        $this->updateCustomFields();

        if ($this->isInsert() && $this->getOption(self::OPTION_SET_IP_ADDRESS) && !$this->get('ip_id'))
        {
            $postSaveChanges['ip_id'] = XenForo_Model_Ip::log($this->get('user_id'), 'classified', $this->get('classified_id'), 'insert');
        }

        if ($this->isUpdate() && $this->isChanged('title'))
        {
            $thread = $this->getDiscussionThread();
            if ($thread)
            {
                $threadTitle = $this->_stripTemplateComponents($thread['title'], $this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE));
                $threadTitle = $this->_stripTemplateComponents($threadTitle, $this->getOption(self::OPTION_CLOSE_THREAD_TITLE_TEMPLATE));
                $threadTitle = $this->_stripTemplateComponents($threadTitle, $this->getOption(self::OPTION_COMPLETE_THREAD_TITLE_TEMPLATE));
                if ($threadTitle == $this->getExisting('title'))
                {
                    $thread['title'] = $this->_getThreadTitle();
                }
            }
        }

        $category = $this->getCategory();

        if ($this->isUpdate() && $this->isChanged('category_id') && $this->get('discussion_thread_id'))
        {
            $nodeId = $category['thread_node_id'];
            $prefixId = $category['thread_prefix_id'];

            $thread = $this->getDiscussionThread();
            if ($thread)
            {
                if ($nodeId)
                {
                    $thread['node_id'] = $nodeId;
                    $thread['prefix_id'] = $prefixId;

                    if ($thread['discussion_state'] == 'deleted')
                    {
                        $thread['discussion_state'] = 'visible';
                    }
                }
                else
                {
                    $thread['discussion_state'] = 'deleted';
                }
            }
        }

        if ($this->isUpdate() && $this->isChanged('advert_type_id') && $this->get('classified_complete') && $this->get('discussion_thread_id'))
        {
            $thread = $this->getDiscussionThread();
            if ($thread)
            {
                $thread['title'] = $this->_getThreadTitle();
            }
        }

        if ($thread = $this->getDiscussionThread())
        {
            $thread->save();
        }

        $attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);
        if ($attachmentHash)
        {
            $rows = $this->_associateAttachments($attachmentHash, 'classified');
            if ($rows)
            {
                $this->set('attach_count', $this->get('attach_count') + $rows, '', array('setAfterPreSave' => true));
                $postSaveChanges['attach_count'] = $this->get('attach_count');
            }
        }

        /*$classifiedIconHash = $this->getExtraData(self::DATA_CLASSIFIED_ICON_HASH);
        if ($classifiedIconHash && $this->_associateAttachments($classifiedIconHash, 'classified_icon'))
        {
            $postSaveChanges['featured_image_date'] = XenForo_Application::$time;
            $this->set('featured_image_date', $postSaveChanges['featured_image_date'], '', array('setAfterPreSave' => true));
            $this->_copyFeaturedImageToExternalDirectory();
        }*/

        if ($this->isChanged('featured_image_attachment_id'))
        {
            if ($this->get('featured_image_attachment_id'))
            {
                $postSaveChanges['featured_image_date'] = XenForo_Application::$time;
                $this->set('featured_image_date', $postSaveChanges['featured_image_date'], '', array('setAfterPreSave' => true));
                $this->_associateFeaturedImage();
            }
            else
            {
                $postSaveChanges['featured_image_date'] = 0;
                $this->set('featured_image_date', 0, '', array('setAfterPreSave' => true));
                $this->_unassociateFeaturedImage();
            }
        }

        $galleryHash = $this->getExtraData(self::DATA_GALLERY_HASH);
        if ($galleryHash)
        {
            $rows = $this->_associateAttachments($galleryHash, 'classified_gallery');
            if ($rows)
            {
                $this->set('gallery_count', $this->get('gallery_count') + $rows, '', array('setAfterPreSave' => true));
                $postSaveChanges['gallery_count'] = $this->get('gallery_count');
            }
        }

        if ($this->get('classified_state') == 'visible' && !$this->get('discussion_thread_id') && $category['thread_node_id'])
        {
            $nodeId = $category['thread_node_id'];
            $prefixId = $category['thread_prefix_id'];

            if ($threadId = $this->_createDiscussionThread($nodeId, $prefixId))
            {
                $postSaveChanges['discussion_thread_id'] = $threadId;
            }
        }

        $removed = false;
        if ($this->isChanged('classified_state'))
        {
            if ($this->get('classified_state') == 'visible')
            {
                $this->_classifiedMadeVisible($postSaveChanges);
            }
            elseif ($this->isUpdate())
            {
                $this->_classifiedRemoved();
                $removed = true;
            }

            $this->_updateDeletionLog();
            $this->_updateModerationQueue();
        }

        if (!$removed)
        {
            $category->writer()->classifiedUpdate($this);
        }

        if ($this->_locationWriter)
        {
            $this->getLocationWriter()->set('classified_id', $this->get('classified_id'), '', array('setAfterPreSave' => true));
            $this->getLocationWriter()->save();
        }

        if ($this->isChanged('category_id')
            || $this->isChanged('title')
            || $this->isChanged('tag_line')
            || $this->isChanged('prefix_id')
            || $this->isChanged('description')
        )
        {
            $indexer = new XenForo_Search_Indexer();
            $dataHandler = XenForo_Search_DataHandler_Abstract::create('GFNClassifieds_Search_DataHandler_Classified');

            $data = $this->getMergedData();

            if ($this->_locationWriter)
            {
                $data = array_merge($data, $this->getLocationWriter()->getMergedData());
            }

            $dataHandler->insertIntoIndex($indexer, $data);
        }

        if ($this->isUpdate() && $this->isChanged('user_id'))
        {
            if ($this->get('user_id') && $this->get('classified_state') == 'visible' && !$this->isChanged('classified_state'))
            {
                $this->_db->query(
                    'UPDATE kmk_classifieds_trader
                    SET classified_count = classified_count + 1
                    WHERE user_id = ?', $this->get('user_id')
                );
            }

            if ($this->getExisting('user_id') && $this->getExisting('classified_state') == 'visible')
            {
                $this->_db->query(
                    'UPDATE kmk_classifieds_trader
                    SET classified_count = IF(classified_count > 0, classified_count - 1, 0)
                    WHERE user_id = ?', $this->get('user_id')
                );
            }
        }

        if ($this->_isFirstVisible && $this->getOption(self::OPTION_PUBLISH_FEED))
        {
            GFNClassifieds_Model_NewsFeed::publish('classified', $this->getMergedData());
        }

        if ($postSaveChanges)
        {
            $this->bulkSet($postSaveChanges, array('setAfterPreSave' => true));
            $this->_db->update('kmk_classifieds_classified', $postSaveChanges, 'classified_id = ' . $this->_db->quote($this->get('classified_id')));
        }

        if ($this->_payment instanceof GFNClassifieds_Eloquent_Payment)
        {
            $this->_payment['classified_id'] = $this->get('classified_id');
            $this->_payment->save();
        }

        if ($this->isUpdate() && $this->isChanged('description'))
        {
            $this->_insertEditHistory();
        }
    }

    protected function _insertEditHistory()
    {
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_EditHistory', XenForo_DataWriter::ERROR_SILENT);

        $writer->bulkSet(array(
            'content_type' => 'classified',
            'content_id' => $this->get('classified_id'),
            'edit_user_id' => XenForo_Visitor::getUserId(),
            'old_text' => $this->getExisting('description')
        ));

        $writer->save();
    }

    protected function _calculatePayment(array $package)
    {
        $price = $this->get('price_base_currency');
        list ($listing, $renewal) = $this->_getClassifiedModel()->calculatePayment($price, $package);

        if ($this->isInsert())
        {
            if (empty($listing))
            {
                return;
            }

            $payment = new GFNClassifieds_Eloquent_Payment();
            $payment['amount'] = $listing;
            $payment['is_renewal'] = false;
        }
        elseif ($this->getOption(self::OPTION_IS_RENEWAL))
        {
            if (empty($renewal))
            {
                return;
            }

            $payment = new GFNClassifieds_Eloquent_Payment();
            $payment['amount'] = $renewal;
            $payment['is_renewal'] = true;
        }
        elseif ($this->isChanged('price_base_currency'))
        {
            list ($oldListing) = $this->_getClassifiedModel()->calculatePayment($this->getExisting('price_base_currency'), $package);
            $diff = $listing - $oldListing;

            if ($diff > 0)
            {
                $payment = new GFNClassifieds_Eloquent_Payment();
                $payment['amount'] = $diff;
                $payment['is_renewal'] = false;
            }
            else
            {
                $payment = false;
            }
        }
        else
        {
            $payment = false;
        }

        if ($payment instanceof GFNClassifieds_Eloquent_Payment)
        {
            $this->set('classified_state', 'pending');
            $payment['user_id'] = $this->get('user_id');
            $payment['package_id'] = $package['package_id'];
            $payment['currency'] = $this->get('currency'); // TODO

            $this->_payment = $payment;
            $this->_requiresPayment = true;
            XenForo_Application::getSession()->set('classifiedAutoLinkTrigger', '#ClassifiedActivateLink');
        }
    }

    protected function _postSaveAfterTransaction()
    {
        if ($this->_isFirstVisible)
        {
            $classified = $this->getMergedData();
            //$this->_getTraderModel()->optedInTradersWatchClassified($classified);
            $done = $this->_getCategoryWatchModel()->sendNotificationToWatchUsersOnCreate($classified);

            if (empty($done))
            {
                $done = array('alerted' => array(), 'emailed' => array());
            }
            else
            {
                if (empty($done['alerted']))
                {
                    $done['alerted'] = array();
                }

                if (empty($done['emailed']))
                {
                    $done['emailed'] = array();
                }
            }

            $this->_getClassifiedWatchModel()->sendNotificationToWatchUsersOnCreate($classified, $done['alerted'], $done['emailed']);
        }
    }

    protected function _postDelete()
    {
        $indexer = new XenForo_Search_Indexer();
        $indexer->deleteFromIndex('classified', $this->get('classified_id'));

        if ($this->getExisting('classified_state') != 'deleted')
        {
            $this->_classifiedRemoved();
        }

        $this->_updateDeletionLog(true);
        $this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
            'classified', $this->get('classified_id')
        );

        $this->_deleteAttachments();
    }

    protected function _classifiedRemoved()
    {
        if ($this->get('discussion_thread_id'))
        {
            $threadWriter = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
            if ($threadWriter->setExistingData($this->get('discussion_thread_id')) && $threadWriter->get('discussion_type') == 'classified')
            {
                switch ($this->get('classified_state'))
                {
                    case 'closed':
                        $threadAction = $this->getOption(self::OPTION_CLOSE_THREAD_ACTION);
                        $addPost = $this->getOption(self::OPTION_CLOSE_ADD_POST);
                        $postPhraseTitle = 'classified_message_close_classified';
                        break;

                    case 'deleted':
                        $threadAction = $this->getOption(self::OPTION_DELETE_THREAD_ACTION);
                        $addPost = $this->getOption(self::OPTION_DELETE_ADD_POST);
                        $postPhraseTitle = 'classified_message_delete_classified';
                        break;

                    case 'completed':
                        $threadAction = $this->getOption(self::OPTION_COMPLETE_THREAD_ACTION);
                        $addPost = $this->getOption(self::OPTION_COMPLETE_ADD_POST);
                        $postPhraseTitle = 'classified_message_complete_classified';
                        break;

                    default:
                        $threadAction = false;
                        $addPost = false;
                        $postPhraseTitle = false;
                }

                switch ($threadAction)
                {
                    case 'delete':
                        $threadWriter->set('discussion_state', 'deleted');
                        break;

                    case 'close':
                        $threadWriter->set('discussion_open', 0);
                        break;
                }

                $threadWriter->set('title', $this->_getThreadTitle());
                $threadWriter->save();

                if ($addPost)
                {
                    /** @var XenForo_Model_Forum $forumModel */ /** @var XenForo_Model_Post $postModel */
                    $forumModel = $this->getModelFromCache('XenForo_Model_Forum');
                    $postModel = $this->getModelFromCache('XenForo_Model_Post');

                    $forum = $forumModel->getForumById($threadWriter->get('node_id'));
                    if ($forum)
                    {
                        $messageState = $postModel->getPostInsertMessageState($threadWriter->getMergedData(), $forum);
                    }
                    else
                    {
                        $messageState = 'visible';
                    }

                    $user = $this->_getUserModel()->getUserById($this->get('user_id'));
                    if ($user)
                    {
                        $this->set('username', $user['username'], '', array('setAfterPreSave' => true));
                    }

                    $message = new XenForo_Phrase($postPhraseTitle, array(
                        'title' => $this->get('title'),
                        'username' => $this->get('username'),
                        'userId' => $this->get('user_id'),
                        'classifiedLink' => XenForo_Link::buildPublicLink('canonical:classifieds', $this->getMergedData()),
                        'tagLine' => $this->get('tag_line'),
                        'complete' => utf8_strtolower(new XenForo_Phrase(
                            $this->_getAdvertTypeModel()->getCompleteTextPhraseName(
                                $this->get('advert_type_id')
                            ))
                        )
                    ), false);

                    /** @var XenForo_DataWriter_DiscussionMessage_Post $postWriter */
                    $postWriter = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
                    $postWriter->set('message', $message);
                    $postWriter->setOption($postWriter::OPTION_IS_AUTOMATED, true);

                    $postWriter->bulkSet(array(
                        'user_id' => $this->get('user_id'),
                        'username' => $this->get('username'),
                        'message_state' => $messageState,
                        'thread_id' => $threadWriter->get('thread_id'),
                        'message' => strval($message)
                    ));

                    $postWriter->save();
                }
            }
        }

        if ($this->get('user_id') && $this->getExisting('classified_state') == 'visible')
        {
            $this->_db->query(
                'UPDATE kmk_classifieds_trader
                SET classified_count = IF(classified_count > 0, classified_count - 1, 0)
                WHERE user_id = ?', $this->get('user_id')
            );
        }

        $category = $this->getCategory();
        $category->writer()->classifiedRemoved($this);
    }

    protected function _classifiedMadeVisible(array &$postSaveChanges)
    {
        if (!$this->get('discussion_thread_id'))
        {
            $category = $this->getCategory();

            $nodeId = $category['thread_node_id'];
            $prefixId = $category['thread_prefix_id'];

            $threadId = $this->_createDiscussionThread($nodeId, $prefixId);
            if ($threadId)
            {
                $postSaveChanges['discussion_thread_id'] = $threadId;
            }
        }
        else
        {
            $threadWriter = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
            if ($threadWriter->setExistingData($this->get('discussion_thread_id')) && $threadWriter->get('discussion_type') == 'classified')
            {
                switch ($this->getExisting('classified_state'))
                {
                    case 'closed':
                        $threadAction = $this->getOption(self::OPTION_CLOSE_THREAD_ACTION);
                        $addPost = $this->getOption(self::OPTION_OPEN_ADD_POST);
                        $postPhraseTitle = 'classified_message_open_classified';
                        $threadTitleTemplate = $this->getOption(self::OPTION_CLOSE_THREAD_TITLE_TEMPLATE);
                        break;

                    case 'deleted':
                        $threadAction = $this->getOption(self::OPTION_DELETE_THREAD_ACTION);
                        $addPost = false;
                        $postPhraseTitle = false;
                        $threadTitleTemplate = $this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE);
                        break;

                    default:
                        $threadAction = false;
                        $addPost = false;
                        $postPhraseTitle = false;
                        $threadTitleTemplate = '';
                }

                switch ($threadAction)
                {
                    case 'delete':
                        $threadWriter->set('discussion_state', 'visible');
                        break;

                    case 'close':
                        $threadWriter->set('discussion_open', 1);
                        break;
                }

                $title = $this->_stripTemplateComponents($threadWriter->get('title'), $threadTitleTemplate);
                $threadWriter->set('title', $title);
                $threadWriter->save();

                if ($addPost)
                {
                    /** @var XenForo_Model_Forum $forumModel */ /** @var XenForo_Model_Post $postModel */
                    $forumModel = $this->getModelFromCache('XenForo_Model_Forum');
                    $postModel = $this->getModelFromCache('XenForo_Model_Post');

                    $forum = $forumModel->getForumById($threadWriter->get('node_id'));
                    if ($forum)
                    {
                        $messageState = $postModel->getPostInsertMessageState($threadWriter->getMergedData(), $forum);
                    }
                    else
                    {
                        $messageState = 'visible';
                    }

                    $user = $this->_getUserModel()->getUserById($this->get('user_id'));
                    if ($user)
                    {
                        $this->set('username', $user['username'], '', array('setAfterPreSave' => true));
                    }

                    $message = new XenForo_Phrase($postPhraseTitle, array(
                        'title' => $this->get('title'),
                        'username' => $this->get('username'),
                        'userId' => $this->get('user_id'),
                        'classifiedLink' => XenForo_Link::buildPublicLink('canonical:classifieds', $this->getMergedData()),
                        'tagLine' => $this->get('tag_line'),
                        'complete' => utf8_strtolower(new XenForo_Phrase(
                            $this->_getAdvertTypeModel()->getCompleteTextPhraseName(
                                $this->get('advert_type_id')
                            ))
                        )
                    ), false);

                    /** @var XenForo_DataWriter_DiscussionMessage_Post $postWriter */
                    $postWriter = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
                    $postWriter->set('message', $message);
                    $postWriter->setOption($postWriter::OPTION_IS_AUTOMATED, true);

                    $postWriter->bulkSet(array(
                        'user_id' => $this->get('user_id'),
                        'username' => $this->get('username'),
                        'message_state' => $messageState,
                        'thread_id' => $threadWriter->get('thread_id'),
                        'message' => strval($message)
                    ));

                    $postWriter->save();
                }
            }
        }

        if ($this->get('user_id') && $this->get('classified_state') == 'visible')
        {
            $this->_db->query(
                'UPDATE kmk_classifieds_trader
                SET classified_count = classified_count + 1
                WHERE user_id = ?', $this->get('user_id')
            );
        }
    }

    protected function _updateDeletionLog($hardDelete = false)
    {
        /** @var XenForo_Model_DeletionLog $model */
        $model = $this->getModelFromCache('XenForo_Model_DeletionLog');

        if ($hardDelete || ($this->isChanged('classified_state') && $this->getExisting('classified_state') == 'deleted'))
        {
            $model->removeDeletionLog('classified', $this->get('classified_id'));
        }

        if ($this->isChanged('classified_state') && $this->get('classified_state') == 'deleted')
        {
            $reason = $this->getExtraData(self::DATA_DELETE_REASON);
            $model->logDeletion('classified', $this->get('classified_id'), $reason);
        }
    }

    protected function _updateModerationQueue()
    {
        if (!$this->isChanged('classified_state'))
        {
            return;
        }

        /** @var XenForo_Model_ModerationQueue $model */
        $model = $this->getModelFromCache('XenForo_Model_ModerationQueue');

        if ($this->get('classified_state') == 'moderated')
        {
            $model->insertIntoModerationQueue('classified', $this->get('classified_id'), $this->get('classified_date'));
        }
        elseif ($this->getExisting('classified_state') == 'moderated')
        {
            $model->deleteFromModerationQueue('classified', $this->get('classified_id'));
        }
    }

    public function checkUserName()
    {
        if ($this->get('user_id'))
        {
            $user = $this->_getUserModel()->getUserById($this->get('user_id'));
            if ($user)
            {
                $changed = $this->get('username') != $user['username'];
                $this->set('username', $user['username']);

                return $changed;
            }
        }

        return false;
    }

    public function updateCustomFields()
    {
        if ($this->_updateCustomFields)
        {
            $classifiedId = $this->get('classified_id');
            $this->_db->query('DELETE FROM kmk_classifieds_field_value WHERE classified_id = ?', $classifiedId);

            foreach ($this->_updateCustomFields AS $fieldId => $value)
            {
                if (is_array($value))
                {
                    $value = XenForo_Helper_Php::safeSerialize($value);
                }

                $this->_db->query(
                    'INSERT INTO kmk_classifieds_field_value
                      (classified_id, field_id, field_value)
                    VALUES
                      (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      field_value = VALUES(field_value)', array($classifiedId, $fieldId, $value)
                );
            }
        }
    }

    protected function _createDiscussionThread($forumId, $prefixId = 0)
    {
        if (!$forumId)
        {
            return false;
        }

        /** @var XenForo_Model_Forum $forumModel */
        $forumModel = $this->getModelFromCache('XenForo_Model_Forum');
        $forum = $forumModel->getForumById($forumId);
        if (!$forum)
        {
            return false;
        }

        /** @var XenForo_DataWriter_Discussion_Thread $threadWriter */
        $threadWriter = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', self::ERROR_SILENT);
        $threadWriter->setExtraData($threadWriter::DATA_FORUM, $forum);

        $threadWriter->bulkSet(array(
            'node_id' => $forumId,
            'title' => $this->_getThreadTitle(),
            'user_id' => $this->get('user_id'),
            'username' => $this->get('username'),
            'discussion_type' => 'classified',
            'prefix_id' => $prefixId
        ));

        $threadWriter->set('discussion_state', $this->getModelFromCache('XenForo_Model_Post')->getPostInsertMessageState(array(), $forum));
        $threadWriter->setOption(XenForo_DataWriter_Discussion::OPTION_PUBLISH_FEED, false);

        $parser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_BbCode_AutoLink', false));
        $snippet = $parser->render(XenForo_Helper_String::wholeWordTrim($this->get('description'), 500));
        $params = array(
            'title' => $this->get('title'),
            'username' => $this->get('username'),
            'snippet' => $snippet,
            'classifiedLink' => XenForo_Link::buildPublicLink('canonical:classifieds', $this->getMergedData()),
            'tagLine' => $this->get('tag_line')
        );

        if ($params['tagLine'])
        {
            $message = new XenForo_Phrase('classified_message_create_classified_with_tagline', $params, false);
        }
        else
        {
            $message = new XenForo_Phrase('classified_message_create_classified', $params, false);
        }

        /** @var XenForo_DataWriter_DiscussionMessage_Post $postWriter */
        $postWriter = $threadWriter->getFirstMessageDw();
        $postWriter->set('message', strval($message));
        $postWriter->setExtraData($postWriter::DATA_FORUM, $forum);
        $postWriter->setOption($postWriter::OPTION_IS_AUTOMATED, true);
        $postWriter->setOption($postWriter::OPTION_PUBLISH_FEED, false);

        if (!$threadWriter->save())
        {
            return false;
        }

        $this->set('discussion_thread_id', $threadWriter->get('thread_id'), '', array('setAfterPreSave' => true));

        $this->getModelFromCache('XenForo_Model_Thread')->markThreadRead(
            $threadWriter->getMergedData(), $forum, XenForo_Application::$time
        );

        $this->getModelFromCache('XenForo_Model_ThreadWatch')->setThreadWatchStateWithUserDefault(
            $this->get('user_id'), $threadWriter->get('thread_id'),
            $this->getExtraData(self::DATA_THREAD_WATCH_DEFAULT)
        );

        return $threadWriter->get('thread_id');
    }

    protected function _getThreadTitle()
    {
        $title = $this->get('title');

        if ($this->get('classified_state') == 'closed' && $this->getOption(self::OPTION_CLOSE_THREAD_TITLE_TEMPLATE))
        {
            $title = str_replace(
                '{title}', $this->get('title'),
                $this->getOption(self::OPTION_CLOSE_THREAD_TITLE_TEMPLATE)
            );
        }

        if ($this->get('classified_state') == 'completed' && $this->getOption(self::OPTION_COMPLETE_THREAD_TITLE_TEMPLATE))
        {
            $title = str_replace(
                '{title}', $this->get('title'),
                $this->getOption(self::OPTION_COMPLETE_THREAD_TITLE_TEMPLATE)
            );

            $title = str_replace(
                '{complete}', new XenForo_Phrase($this->_getAdvertTypeModel()->getCompleteTextPhraseName(
                    $this->get('advert_type_id')
                )), $title
            );
        }

        if ($this->get('classified_state') == 'deleted' && $this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE))
        {
            $title = str_replace(
                '{title}', $this->get('title'),
                $this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE)
            );
        }

        return $title;
    }

    protected function _stripTemplateComponents($string, $template)
    {
        if (!$template)
        {
            return $string;
        }

        $template = str_replace('\{title\}', '(.*)', preg_quote($template, '/'));

        if (preg_match('/^' . $template . '$/', $string, $match))
        {
            return $match[1];
        }

        return $string;
    }

    protected $_package;

    public function getPackageInfo()
    {
        if ($this->_package === null)
        {
            $this->_package = $this->_getPackageModel()->getPackageById($this->get('package_id'));
            if (!$this->_package)
            {
                return false;
            }

            return $this->_getPackageModel()->preparePackage($this->_package);
        }

        return $this->_package;
    }

    protected function _validateCategoryId(&$data)
    {
        $category = $this->_getCategoryModel()->getCategoryById($data);
        if (!$category)
        {
            $this->error(new XenForo_Phrase('requested_category_not_found'), 'category_id');
            return false;
        }

        return true;
    }

    protected function _associateAttachments($attachmentHash, $contentType)
    {
        return $this->_db->update('kmk_attachment', array(
            'content_type' => $contentType,
            'content_id' => $this->get('classified_id'),
            'temp_hash' => '',
            'unassociated' => 0
        ), 'temp_hash = ' . $this->_db->quote($attachmentHash));
    }

    protected function _deleteAttachments()
    {
        $this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds(
            'classified',
            array($this->get('classified_id'))
        );
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Classified');
    }

    /**
     * @return GFNClassifieds_Model_Trader
     */
    protected function _getTraderModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Trader');
    }

    /**
     * @return GFNClassifieds_Model_AdvertType
     */
    protected function _getAdvertTypeModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_AdvertType');
    }

    /**
     * @return GFNClassifieds_Model_ClassifiedWatch
     */
    protected function _getClassifiedWatchModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_ClassifiedWatch');
    }

    /**
     * @return GFNClassifieds_Model_Category
     */
    protected function _getCategoryModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Category');
    }

    /**
     * @return GFNClassifieds_Model_CategoryWatch
     */
    protected function _getCategoryWatchModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryWatch');
    }

    /**
     * @return GFNClassifieds_Model_Field
     */
    protected function _getFieldModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Field');
    }

    /**
     * @return GFNClassifieds_Model_Package
     */
    protected function _getPackageModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Package');
    }

    /**
     * @var GFNClassifieds_Eloquent_Category
     */
    protected $_category;

    public function getCategory()
    {
        if (!$this->_category)
        {
            if (!$this->get('category_id'))
            {
                throw new XenForo_Exception(new XenForo_Phrase('category_id_needs_to_be_set_first'));
            }

            $this->_category = new GFNClassifieds_Eloquent_Category($this->get('category_id'));
        }

        return $this->_category;
    }

    /**
     * @var GFNClassifieds_Eloquent_AdvertType
     */
    protected $_advertType;

    public function getAdvertType()
    {
        if (!$this->_advertType)
        {
            if (!$this->get('advert_type_id'))
            {
                throw new XenForo_Exception(new XenForo_Phrase('advert_type_id_needs_to_be_set_first'));
            }

            $this->_advertType = new GFNClassifieds_Eloquent_AdvertType($this->get('advert_type_id'));
        }

        return $this->_advertType;
    }

    protected $_discussionThread;

    /**
     * @return false|GFNClassifieds_Eloquent_Discussion_Thread
     */
    public function getDiscussionThread()
    {
        if ($this->_discussionThread === null)
        {
            $this->_discussionThread = false;

            if ($this->get('discussion_thread_id'))
            {
                $thread = new GFNClassifieds_Eloquent_Discussion_Thread($this->get('discussion_thread_id'));
                if ($thread->writer()->isUpdate() && $thread->get('discussion_type') == 'classified')
                {
                    $this->_discussionThread = $thread;
                }
            }
        }

        return $this->_discussionThread;
    }

    /**
     * @var GFNClassifieds_DataWriter_Location
     */
    protected $_locationWriter;

    public function getLocationWriter()
    {
        if (!$this->_locationWriter)
        {
            $this->_locationWriter = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Location', XenForo_DataWriter::ERROR_SILENT);
            if ($this->isUpdate())
            {
                if ($this->_getClassifiedModel()->getLocationById($this->get('classified_id')))
                {
                    $this->_locationWriter->setExistingData($this->get('classified_id'));
                }
                else
                {
                    $this->_locationWriter->set('classified_id', $this->get('classified_id'));
                }
            }
            else
            {
                $this->_locationWriter->set('classified_id', 0);
            }
        }

        return $this->_locationWriter;
    }

    protected $_updateCustomFields = null;

    public function setCustomFields(array $fieldValues, array $fieldsShown = null)
    {
        $fieldModel = $this->_getFieldModel();
        $fields = $fieldModel->getFieldsForEdit($this->get('category_id'));

        if (!is_array($fieldsShown))
        {
            $fieldsShown = array_keys($fields);
        }

        if ($this->get('classified_id') && !$this->_importMode)
        {
            $existingValues = $fieldModel->getFieldValues($this->get('classified_id'));
        }
        else
        {
            $existingValues = array();
        }

        $finalValues = array();

        foreach ($fieldsShown AS $fieldId)
        {
            if (!isset($fields[$fieldId]))
            {
                continue;
            }

            $field = $fields[$fieldId];
            $multiChoice = ($field['field_type'] == 'checkbox' || $field['field_type'] == 'multiselect');

            if ($multiChoice)
            {
                // multi selection - array
                $value = array();
                if (isset($fieldValues[$fieldId]))
                {
                    if (is_string($fieldValues[$fieldId]))
                    {
                        $value = array($fieldValues[$fieldId]);
                    }
                    else if (is_array($fieldValues[$fieldId]))
                    {
                        $value = $fieldValues[$fieldId];
                    }
                }
            }
            else
            {
                // single selection - string
                if (isset($fieldValues[$fieldId]))
                {
                    if (is_array($fieldValues[$fieldId]))
                    {
                        $value = count($fieldValues[$fieldId]) ? strval(reset($fieldValues[$fieldId])) : '';
                    }
                    else
                    {
                        $value = strval($fieldValues[$fieldId]);
                    }
                }
                else
                {
                    $value = '';
                }
            }

            $existingValue = (isset($existingValues[$fieldId]) ? $existingValues[$fieldId] : null);

            if (!$this->_importMode)
            {
                if (!$fieldModel->verifyFieldValue($field, $value, $error))
                {
                    $this->error($error, "custom_field_$fieldId");
                    continue;
                }

                if ($field['required'] && ($value === '' || $value === array()))
                {
                    $this->error(new XenForo_Phrase('please_enter_value_for_all_required_fields'), 'required');
                    continue;
                }
            }

            if ($value !== $existingValue)
            {
                $finalValues[$fieldId] = $value;
            }
        }

        $this->_updateCustomFields = $this->_filterValidFields($finalValues + $existingValues, $fields);
        $this->set('custom_classified_fields', $this->_updateCustomFields);
    }

    public function requiresPayment()
    {
        return $this->_requiresPayment;
    }

    public function payment()
    {
        return $this->_payment;
    }

    protected function _filterValidFields(array $values, array $fields)
    {
        $newValues = array();
        foreach ($fields AS $field)
        {
            if (isset($values[$field['field_id']]))
            {
                $newValues[$field['field_id']] = $values[$field['field_id']];
            }
        }

        return $newValues;
    }

    /** @model deprecated */
    protected function _copyFeaturedImageToExternalDirectory()
    {
        /** @var XenForo_Model_Attachment $attachmentModel */
        $attachmentModel = $this->getModelFromCache('XenForo_Model_Attachment');
        $featuredImageData = $attachmentModel->getAttachmentsByContentId('classified_icon', $this->get('classified_id'));
        if (!$featuredImageData)
        {
            return;
        }

        $featuredImageData = reset($featuredImageData);
        $filePath = $attachmentModel->getAttachmentDataFilePath($featuredImageData);
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo)
        {
            return;
        }

        $image = XenForo_Image_Abstract::createFromFile($filePath, $imageInfo[2]);
        if (!$image)
        {
            return;
        }

        $filePath = $this->_getClassifiedModel()->getFeaturedImagePath($this->get('classified_id'));

        if (@file_exists($filePath)) // sanity check...
        {
            @unlink($filePath);
        }

        XenForo_Helper_File::createDirectory(dirname($filePath), true);
        $image->output(IMAGETYPE_JPEG, $filePath);
    }

    protected function _associateFeaturedImage()
    {
        if ($this->get('featured_image_attachment_id') == $this->getExisting('featured_image_attachment_id'))
        {
            return;
        }

        /** @var XenForo_Model_Attachment $attachmentModel */
        $attachmentModel = $this->getModelFromCache('XenForo_Model_Attachment');
        $featuredImageData = $attachmentModel->getAttachmentById($this->get('featured_image_attachment_id'));
        if (!$featuredImageData)
        {
            return;
        }

        $filePath = $attachmentModel->getAttachmentDataFilePath($featuredImageData);
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo)
        {
            return;
        }

        $image = XenForo_Image_Abstract::createFromFile($filePath, $imageInfo[2]);
        if (!$image)
        {
            return;
        }

        $this->_resizeClassifiedIcon($image);
        $filePath = $this->_getClassifiedModel()->getFeaturedImagePath($this->get('classified_id'));

        if (@file_exists($filePath)) // sanity check...
        {
            @unlink($filePath);
        }

        XenForo_Helper_File::createDirectory(dirname($filePath), true);
        $image->output(IMAGETYPE_JPEG, $filePath);
    }

    protected function _resizeClassifiedIcon(XenForo_Image_Abstract &$image)
    {
        if (!$image->canResize($image->getWidth(), $image->getHeight()))
        {
            return;
        }

        $image->thumbnailFixedShorterSide(GFNClassifieds_Options::getInstance()->get('iconDimensions'));
        $diff = floor(abs($image->getWidth() - $image->getHeight()) / 2);

        switch ($image->getOrientation())
        {
            case XenForo_Image_Abstract::ORIENTATION_LANDSCAPE:
                $image->crop($diff, 0, $image->getHeight(), $image->getHeight());
                break;

            case XenForo_Image_Abstract::ORIENTATION_PORTRAIT:
                $image->crop(0, $diff, $image->getWidth(), $image->getWidth());
                break;
        }
    }

    protected function _unassociateFeaturedImage()
    {
        $filePath = $this->_getClassifiedModel()->getFeaturedImagePath($this->get('classified_id'));

        if (@file_exists($filePath))
        {
            @unlink($filePath);
        }
    }

    public function commentRemoved(GFNClassifieds_DataWriter_Comment $comment)
    {
        if ($comment->getExisting('message_state') != 'visible')
        {
            return;
        }

        $this->updateCommentCount(-1);

        if ($this->get('last_comment_id') == $comment->get('comment_id'))
        {
            $this->updateLastComment();
        }
    }

    public function commentUpdate(GFNClassifieds_DataWriter_Comment $comment)
    {
        if ($comment->get('message_state') != 'visible')
        {
            return;
        }

        if ($comment->isChanged('message_state'))
        {
            $this->updateCommentCount(1);
        }

        if ($comment->get('post_date') >= $this->get('last_comment_date'))
        {
            $this->set('last_comment_id', $comment->get('comment_id'));
            $this->set('last_comment_date', $comment->get('post_date'));
            $this->set('last_comment_user_id', $comment->get('user_id'));
            $this->set('last_comment_username', $comment->get('username'));
        }
    }

    public function updateCommentCount($adjust = null)
    {
        if ($adjust === null)
        {
            $this->set('comment_count', $this->_db->fetchOne(
                'SELECT COUNT(*)
                FROM kmk_classifieds_comment
                WHERE classified_id = ?
                AND message_state = ?', array($this->get('classified_id'), 'visible')
            ));
        }
        else
        {
            $this->set('comment_count', $this->get('comment_count') + $adjust);
        }
    }

    public function updateLastComment()
    {
        $comment = $this->_db->fetchRow($this->_db->limit(
            'SELECT *
            FROM kmk_classifieds_comment
            WHERE classified_id = ?
            AND message_state = ?
            ORDER BY post_date DESC', 1
        ), array($this->get('classified_id'), 'visible'));

        if (!$comment)
        {
            $this->set('last_comment_id', 0);
            $this->set('last_comment_date', 0);
            $this->set('last_comment_user_id', 0);
            $this->set('last_comment_username', '');
        }
        else
        {
            $this->set('last_comment_id', $comment['comment_id']);
            $this->set('last_comment_date', $comment['post_date']);
            $this->set('last_comment_user_id', $comment['user_id']);
            $this->set('last_comment_username', $comment['username']);
        }
    }
}