<?php /*5a9be03e6720e4b4972b18b376461ca000a0f163*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ControllerPublic_Classified extends KomuKuYJB_ControllerPublic_Abstract
{
    protected function _checkCsrf($action)
    {
        $action = strtolower($action);
        if ($action == 'paymentcompleted' || $action == 'paymentcancelled' || $action == 'paycompleted')
        {
            return;
        }

        parent::_checkCsrf($action);
    }

    public function actionIndex()
    {
        return $this->responseReroute(__CLASS__, 'view');
    }

    public function actionView()
    {
        list($classified, $category) = $this->_getClassifiedViewInfo();
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds', $classified));

        if ($classified['gallery_count'] || $classified['attach_count'])
        {
            $attachments = $this->models()->classified()->getAttachments($classified['classified_id']);
            if ($attachments)
            {
                /** @var XenForo_Model_Attachment $attachmentModel */
                $attachmentModel = $this->getModelFromCache('XenForo_Model_Attachment');
                $attachments = $attachmentModel->prepareAttachments($attachments);
                $galleryImages = array();

                foreach ($attachments as $i => $attachment)
                {
                    switch ($attachment['content_type'])
                    {
                        case 'classified_icon':
                            unset ($attachments[$i]);
                            break;

                        case 'classified_gallery':
                            $galleryImages[$i] = $attachment;
                            unset ($attachments[$i]);
                            break;
                    }
                }
            }
            else
            {
                $attachments = array();
                $galleryImages = array();
            }
        }
        else
        {
            $attachments = array();
            $galleryImages = array();
        }

        $classified['attachments'] = $attachments;
        $advertType = $this->models()->advertType()->getAdvertTypeById($classified['advert_type_id']);
        $this->models()->advertType()->prepareAdvertType($advertType);

        if (!$this->models()->classified()->canViewGalleryImage($classified, $category))
        {
            $galleryImages = array();
        }

        $viewParams = array(
            'classified' => $classified,
            'category' => $category,
            'advertType' => $advertType,
            'galleryImages' => $galleryImages
        );

        $this->models()->classified()->logClassifiedView($classified['classified_id']);

        return $this->_getWrapper(
            'description', $this->responseView(
                'KomuKuYJB_ViewPublic_Classified_Description',
                'classifieds_item_description', $viewParams
            ), $classified, $category, array(
                'advertType' => $advertType,
                'galleryImages' => $galleryImages
            )
        );
    }

    public function actionField()
    {
        $fieldId = $this->_input->filterSingle('field', XenForo_Input::STRING);
        if (!$fieldId)
        {
            return $this->getNotFoundResponse();
        }

        list($classified, $category) = $this->_getClassifiedViewInfo();
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/fields', $classified, array('fieldId' => $fieldId)));

        $viewParams = array(
            'classified' => $classified,
            'category' => $category
        );

        if ($fieldId == 'extra')
        {
            if (!$classified['showExtraInfoTab'])
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                    $this->_buildLink('classifieds', $classified)
                );
            }

            $templateName = 'classifieds_item_field_extra';
            $selectedTab = 'extra';
        }
        else
        {
            $fields = $this->models()->field()->getFieldCache();

            if (!isset($fields[$fieldId])
                || !isset($category['field_cache']['new_tab'][$fieldId])
                || !isset($classified['customFields'][$fieldId])
                || (is_string($classified['customFields'][$fieldId]) && $classified['customFields'][$fieldId] === '')
                || (is_array($classified['customFields'][$fieldId]) && count($classified['customFields'][$fieldId]) == 0)
            )
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
                    $this->_buildLink('classifieds', $classified)
                );
            }

            $viewParams += array(
                'field' => $fields[$fieldId],
                'fieldId' => $fieldId
            );

            $templateName = 'classifieds_item_field';
            $selectedTab = 'field_' . $fieldId;
        }

        return $this->_getWrapper(
            $selectedTab, $this->responseView(
                'KomuKuYJB_ViewPublic_Classified_Field',
                $templateName, $viewParams
            ), $classified, $category
        );
    }

    public function actionHistory()
    {
        $this->_request->setParam('content_type', 'classified');
        $this->_request->setParam('content_id', $this->_input->filterSingle('classified_id', XenForo_Input::UINT));
        return $this->responseReroute('XenForo_ControllerPublic_EditHistory', 'index');
    }

    public function actionWatch()
    {
        $this->_assertRegistrationRequired();
        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        $classifiedModel = $this->models()->classified();
        $watchModel = $this->models()->classifiedWatch();

        if (!$classifiedModel->canWatchClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            if ($this->_input->filterSingle('stop', XenForo_Input::STRING))
            {
                $newState = '';
            }
            else if ($this->_input->filterSingle('email_subscribe', XenForo_Input::UINT))
            {
                $newState = 'watch_email';
            }
            else
            {
                $newState = 'watch_no_email';
            }

            $watchModel->setClassifiedWatchState(XenForo_Visitor::getUserId(), $classified['classified_id'], $newState);

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds', $classified),
                null,
                array('linkPhrase' => ($newState ? new XenForo_Phrase('unwatch_this_classified') : new XenForo_Phrase('watch_this_classified')))
            );
        }
        else
        {
            $watch = $watchModel->getUserClassifiedWatchByClassifiedId(XenForo_Visitor::getUserId(), $classified['classified_id']);

            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category),
                'watch' => $watch
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Watch', 'classifieds_item_watch', $viewParams);
        }
    }

    public function actionLocation()
    {
        list($classified, $category) = $this->_getClassifiedViewInfo();

        if (!$classified['canViewLocation'])
        {
            return $this->responseNoPermission();
        }

        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/location', $classified));

        $viewParams = array(
            'classified' => $classified,
            'category' => $category
        );

        return $this->_getWrapper(
            'location', $this->responseView(
                'KomuKuYJB_ViewPublic_Classified_Location',
                'classifieds_item_location', $viewParams
            ), $classified, $category
        );
    }

    public function actionTags()
    {
        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canEditTags($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        /** @var XenForo_Model_Tag $tagModel */
        $tagModel = $this->getModelFromCache('XenForo_Model_Tag');
        $tagger = $tagModel->getTagger('classified');

        $tagger->setContent($classified['classified_id']);
        $tagger->setPermissionsFromContext($classified, $category);

        $editTags = $tagModel->getTagListForEdit('classified', $classified['classified_id'], $tagger->getPermission('removeOthers'));

        if ($this->isConfirmedPost())
        {
            $tags = $this->_input->filterSingle('tags', XenForo_Input::STRING);
            if ($editTags['uneditable'])
            {
                // this is mostly a sanity check; this should be ignored
                $tags .= (strlen($tags) ? ', ' : '') . implode(', ', $editTags['uneditable']);
            }
            $tagger->setTags($tagModel->splitTags($tags));

            $errors = $tagger->getErrors();
            if ($errors)
            {
                return $this->responseError($errors);
            }

            $cache = $tagger->save();

            if ($this->_noRedirect())
            {
                $view = $this->responseView('', 'helper_tag_list', array(
                    'tags' => $cache,
                    'editUrl' => $this->_buildLink('classifieds/tags', $classified)
                ));

                $view->jsonParams = array(
                    'isTagList' => true,
                    'redirect' => $this->_buildLink('classifieds', $classified)
                );

                return $view;
            }
            else
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $this->_buildLink('classifieds', $classified)
                );
            }
        }
        else
        {
            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'tags' => $editTags,
                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Tags', 'classifieds_item_tags', $viewParams);
        }
    }

    public function actionLocationPublish()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canEditClassified($classified, $category))
        {
            return $this->responseNoPermission();
        }

        $conversationId = $this->_input->filterSingle('id', XenForo_Input::UINT);
        /** @var XenForo_Model_Conversation $conversationModel */
        $conversationModel = $this->getModelFromCache('XenForo_Model_Conversation');

        $conversation = $conversationModel->getConversationMasterById($conversationId);
        if (!$conversation || !$conversation['user_id'])
        {
            return $this->responseNoPermission();
        }

        if ($this->models()->classified()->publishLocation($conversation['conversation_id']))
        {
            XenForo_Model_Alert::alert(
                $conversation['user_id'], $classified['user_id'], $classified['username'],
                'classified', $classified['classified_id'], 'publish_location'
            );
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->getDynamicRedirect($this->_buildLink('classifieds', $classified))
        );
    }

    public function actionGallery()
    {
        list($classified, $category) = $this->_getClassifiedViewInfo();
        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/gallery', $classified));

        /** @var XenForo_Model_Attachment $attachmentModel */
        $attachmentModel = $this->getModelFromCache('XenForo_Model_Attachment');
        $galleryImages = $attachmentModel->getAttachmentsByContentId('classified_gallery', $classified['classified_id']);
        $galleryImages = $attachmentModel->prepareAttachments($galleryImages);

        if (!$this->models()->classified()->canViewGalleryImage($classified, $category))
        {
            return $this->responseError('no_viewable_gallery_found', 404);
        }

        $viewParams = array(
            'classified' => $classified,
            'category' => $category,
            'galleryImages' => $galleryImages
        );

        return $this->_getWrapper(
            'gallery', $this->responseView(
                'KomuKuYJB_ViewPublic_Classified_Gallery',
                'classifieds_item_gallery', $viewParams
            ), $classified, $category, array(
                'galleryImages' => $galleryImages
            )
        );
    }

    public function actionComments()
    {
        return $this->responseReroute(__CLASS__, 'comment');
    }

    public function actionComment()
    {
        list($classified, $category) = $this->_getClassifiedViewInfo();
        $commentModel = $this->models()->comment();

        $criteria = array(
            'classified_id' => $classified['classified_id']
        );

        $criteria += $commentModel->getPermissionBasedFetchConditions($category);

        $totalComments = $commentModel->countComments($criteria);
        $page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
        $perPage = KomuKuYJB_Options::getInstance()->get('commentsPerPage');

        if ($totalComments)
        {
            $this->canonicalizePageNumber($page, $perPage, $totalComments, 'classifieds/comments', $classified);
            $this->canonicalizeRequestUrl($this->_buildLink('classifieds/comments', $classified, array('page' => $page)));

            $fetchOptions = array(
                'join' => $commentModel::FETCH_USER,
                'page' => $page,
                'perPage' => $perPage,
                'likeUserId' => XenForo_Visitor::getUserId()
            );

            if ($criteria['deleted'])
            {
                $fetchOptions['join'] |= $commentModel::FETCH_DELETION_LOG;
            }

            $comments = $commentModel->getComments($criteria, $fetchOptions);
            if (empty($comments))
            {
                return $this->responseNoPermission();
            }

            foreach ($comments as $commentId => $comment)
            {
                if (!$commentModel->canViewComment($comment, $classified, $category))
                {
                    unset ($comments[$commentId]);
                }
            }

            if (empty($comments))
            {
                return $this->responseNoPermission();
            }

            $parentCommentIds = array_keys($comments);
            unset ($criteria['classified_id']);

            $replies = $commentModel->getRepliesByParentCommentIds($parentCommentIds, $criteria, $fetchOptions);

            $session = XenForo_Application::getSession();
            if ($session->isRegistered('classifiedCommentLoadAllReplies'))
            {
                $replies = array_merge(
                    $commentModel->getComments(
                        $criteria + array(
                            'reply_parent_comment_id' => $session->get('classifiedCommentLoadAllReplies')
                        ), $fetchOptions + array(
                            'order' => 'post_date',
                            'direction' => 'asc'
                        )
                    ), $replies
                );

                $session->remove('classifiedCommentLoadAllReplies');
            }

            $replies = $commentModel->prepareComments($replies, $classified, $category);

            $comments = $commentModel->prepareComments($comments, $classified, $category);
            $commentModel->mergeRepliesToComments($replies, $comments);
        }
        else
        {
            $comments = array();
            $this->canonicalizeRequestUrl($this->_buildLink('classifieds/comments', $classified));
        }

        $inlineModOptions = $commentModel->getInlineModOptionsForComments($comments, $classified, $category);

        $viewParams = array(
            'comments' => $comments,
            'classified' => $classified,
            'category' => $category,

            'page' => $page,
            'perPage' => $perPage,
            'totalComments' => $totalComments,
            'lastCommentDate' => $classified['last_comment_date'],
            'inlineModOptions' => $inlineModOptions
        );

        return $this->_getWrapper(
                'comment', $this->responseView(
                'KomuKuYJB_ViewPublic_Classified_Comment',
                'classifieds_item_comment', $viewParams
            ), $classified, $category, array(
                'hideComments' => true,
                'hideControls' => true,
                'totalComments' => $totalComments
            )
        );
    }

    public function actionIp()
    {
        list($classified, $category) = $this->_getClassifiedViewInfo();

        if (!$this->models()->user()->canViewIps($errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $ipInfo = $this->models()->ip()->getContentIpInfo($classified);
        if (empty($ipInfo['contentIp']))
        {
            return $this->responseError(new XenForo_Phrase('no_ip_information_available'));
        }

        $viewParams = array(
            'classified' => $classified,
            'category' => $category,
            'ipInfo' => $ipInfo,
            'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
        );

        return $this->responseView('KomuKuYJB_ViewPublic_Classified_Ip', 'classifieds_item_ip', $viewParams);
    }

    public function actionContact()
    {
        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable(null, array(
            'contactUserId' => XenForo_Visitor::getUserId(),
            'join' => KomuKuYJB_Model_Classified::FETCH_LOCATION
        ));

        if ($classified['conversation_id'])
        {
            $this->_request->setParam('conversation_id', $classified['conversation_id']);
            XenForo_Application::set('KomuKuYJBContactPage', array($classified, $category));
            return $this->responseReroute('XenForo_ControllerPublic_Conversation', 'view');
        }

        $this->canonicalizeRequestUrl($this->_buildLink('classifieds/contact', $classified));

        $classifiedModel = $this->models()->classified();
        $categoryModel = $this->models()->category();
        /** @var XenForo_Model_Conversation $conversationModel */
        $conversationModel = $this->getModelFromCache('XenForo_Model_Conversation');

        if (!$classifiedModel->canContactAdvertiser($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            return $this->_insertConversation($classified);
        }

        $categoryBreadcrumbs = $categoryModel->getCategoryBreadcrumb($category);
        $categoryBreadcrumbs[] = array(
            'href' => XenForo_Link::buildPublicLink('full:classifieds', $classified),
            'value' => $classified['title']
        );

        $draft = $this->_getDraftModel()->getDraftByUserKey(
            'classified-contact-' . $classified['classified_id'],
            XenForo_Visitor::getUserId()
        );

        $attachmentHash = null;
        $title = new XenForo_Phrase('classified_contact', array('classified' => $classified['title']));

        if ($draft)
        {
            $extra = XenForo_Helper_Php::safeUnserialize($draft['extra_data']);
            if (!empty($extra['title']))
            {
                $title = $extra['title'];
            }
            if (!empty($extra['attachment_hash']))
            {
                $attachmentHash = $extra['attachment_hash'];
            }
        }

        $attachmentParams = $conversationModel->getAttachmentParams(array(), array(), null, $attachmentHash);

        $viewParams = array(
            'categoryBreadcrumbs' => $categoryBreadcrumbs,
            'category' => $category,
            'classified' => $classified,

            'title' => $title,
            'attachmentParams' => $attachmentParams,
            'attachmentConstraints' => $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentConstraints()
        );

        return $this->responseView('KomuKuYJB_ViewPublic_Classified_Contact', 'classifieds_item_contact', $viewParams);
    }

    public function actionContactSaveDraft()
    {
        $this->_assertPostOnly();

        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable(null, array(
            'contactUserId' => XenForo_Visitor::getUserId()
        ));

        if (!$this->models()->classified()->canContactAdvertiser($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $extra = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'attachment_hash' => XenForo_Input::STRING
        ));

        $message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
        $forceDelete = $this->_input->filterSingle('delete_draft', XenForo_Input::BOOLEAN);
        $key = 'classified-contact-' . $classified['classified_id'];

        if (!strlen($message) || $forceDelete)
        {
            $draftSaved = false;
            $draftDeleted = $this->_getDraftModel()->deleteDraft($key) || $forceDelete;
        }
        else
        {
            $this->_getDraftModel()->saveDraft($key, $message, $extra);
            $draftSaved = true;
            $draftDeleted = false;
        }

        $viewParams = array(
            'draftSaved' => $draftSaved,
            'draftDeleted' => $draftDeleted
        );

        return $this->responseView('XenForo_ViewPublic_Conversation_SaveDraft', '', $viewParams);
    }

    protected function _insertConversation(array $classified)
    {
        $this->_assertPostOnly();

        /** @var XenForo_Model_Conversation $conversationModel */
        $conversationModel = $this->getModelFromCache('XenForo_Model_Conversation');

        $data = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'attachment_hash' => XenForo_Input::STRING
        ));

        $data['message'] = $this->getHelper('Editor')->getMessageText('message', $this->_input);
        $data['message'] = XenForo_Helper_String::autoLinkBbCode($data['message']);
        $visitor = XenForo_Visitor::getInstance();

        /** @var XenForo_DataWriter_ConversationMaster $writer */
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_ConversationMaster');
        $writer->setExtraData($writer::DATA_ACTION_USER, $visitor->toArray());
        $writer->setExtraData($writer::DATA_MESSAGE, $data['message']);

        $writer->set('user_id', $visitor['user_id']);
        $writer->set('username', $visitor['username']);
        $writer->set('title', $data['title']);
        $writer->set('classified_id', $classified['classified_id']);

        $writer->addRecipientUserIds(array($classified['user_id']));

        $messageWriter = $writer->getFirstMessageDw();
        $messageWriter->set('message', $data['message']);
        $messageWriter->setExtraData($messageWriter::DATA_ATTACHMENT_HASH, $data['attachment_hash']);

        $writer->preSave();

        /** @var XenForo_Model_SpamPrevention $spamModel */
        $spamModel = $this->getModelFromCache('XenForo_Model_SpamPrevention');

        if (!$writer->hasErrors() && $spamModel->visitorRequiresSpamCheck())
        {
            $spamResult = $spamModel->checkMessageSpam($data['title'] . "\n" . $data['message'], array(), $this->_request);
            switch ($spamResult)
            {
                case XenForo_Model_SpamPrevention::RESULT_MODERATED:
                case XenForo_Model_SpamPrevention::RESULT_DENIED;
                    $spamModel->logSpamTrigger('conversation', null);
                    $writer->error(new XenForo_Phrase('your_content_cannot_be_submitted_try_later'));
                    break;
            }
        }

        if (!$writer->hasErrors())
        {
            $this->assertNotFlooding('conversation');
        }

        $writer->save();
        $conversation = $writer->getMergedData();
        $this->_getDraftModel()->deleteDraft('classified-contact-' . $classified['classified_id']);

        $conversationModel->markConversationAsRead(
            $conversation['conversation_id'], XenForo_Visitor::getUserId(), XenForo_Application::$time
        );

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/contact', $classified),
            new XenForo_Phrase('successfully_contacted_advertiser')
        );
    }

    public function actionAdd()
    {
        if (!XenForo_Application::isRegistered('classifiedCreateCategory'))
        {
            return $this->responseReroute('KomuKuYJB_ControllerPublic_Home', 'create');
        }

        $category = XenForo_Application::get('classifiedCreateCategory');

        $classified = array(
            'category_id' => $category['category_id'],
            'classified_id' => null,
            'location_private' => KomuKuYJB_Options::getInstance()->get('privateLocationByDefault'),
            'package_id' => KomuKuYJB_Options::getInstance()->get('defaultPackageId')
        );

        $draft = $this->_getDraftModel()->getDraftByUserKey('classifieds-category-' . $category['category_id'], XenForo_Visitor::getUserId());
        if ($draft)
        {
            $extra = XenForo_Helper_Php::safeUnserialize($draft['extra_data']);

            $classified += array(
                'description' => $draft['message'],
                'title' => $extra['title'],
                'tag_line' => $extra['tag_line'],
                'prefix_id' => $extra['prefix_id'],
                'advert_type_id' => $extra['advert_type_id'],
                'package_id' => $extra['package_id'],
                'price' => $extra['price'],
                'currency' => $extra['currency'],
                'customFields' => $extra['custom_fields'],
                'tagString' => isset($extra['tags']) ? $extra['tags'] : ''
            );
        }

        $extraParams = array(
            'canEditPackage' => true
        );

        return $this->_getAddEditResponse($classified, $category, array(), $extraParams);
    }

    public function actionEdit()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable(null, array(
            'join' => KomuKuYJB_Model_Classified::FETCH_LOCATION
        ));

        $classifiedModel = $this->models()->classified();
        if (!$classifiedModel->canEditClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        return $this->_getAddEditResponse($classified, $category, $classifiedModel->getAttachments($classified['classified_id']));
    }

    protected function _getAddEditResponse(array $classified, array $category, array $attachments, array $viewParams = array())
    {
        $fieldModel = $this->models()->field();
        $classifiedModel = $this->models()->classified();
        $categoryModel = $this->models()->category();
        $categoryBreadcrumbs = $categoryModel->getCategoryBreadcrumb($category);

        if ($classified['classified_id'])
        {
            $categoryBreadcrumbs[] = array(
                'href' => XenForo_Link::buildPublicLink('full:classifieds', $classified),
                'value' => $classified['title']
            );
        }

        $classifiedIconUploaderId = 'ClassifiedIcon_' . md5(uniqid('', true));
        $galleryUploaderId = 'GalleryImages_' . md5(uniqid('', true));

        $customFields = $fieldModel->getFieldsForEdit(
            $category['category_id'], empty($classified['classified_id']) ? 0 : $classified['classified_id']
        );

        $fieldModel->prepareFields($customFields, true, empty($classified['customFields']) ? array() : $classified['customFields']);

        foreach ($customFields as $fieldId => $field)
        {
            if (empty($field['include_in_classified_editor']))
            {
                unset ($customFields[$fieldId]);
            }
        }

        $packages = $this->models()->package()->getUsablePackagesInCategories($category['category_id']);
        if (!$packages)
        {
            return $this->responseError(new XenForo_Phrase('no_valid_usable_package_found'));
        }

        if (count($packages) == 1 && !$classified['classified_id'])
        {
            $package = reset($packages);
            $classified['package_id'] = $package['package_id'];
        }

        $advertTypes = $this->models()->advertType()->getUsableAdvertTypesInCategories($category['category_id']);
        if (!$advertTypes)
        {
            return $this->responseError(new XenForo_Phrase('no_valid_usable_advert_type_found'));
        }

        if (count($advertTypes) == 1 && !$classified['classified_id'])
        {
            $advertType = reset($advertTypes);
            $classified['advert_type_id'] = $advertType['advert_type_id'];
        }

        $galleryImages = array();
        $classifiedIcon = null;

        if ($attachments)
        {
            /** @var XenForo_Model_Attachment $attachmentModel */
            $attachmentModel = $this->getModelFromCache('XenForo_Model_Attachment');
            $attachments = $attachmentModel->prepareAttachments($attachments);

            foreach ($attachments as $i => $attachment)
            {
                switch ($attachment['content_type'])
                {
                    case 'classified_icon':
                        $classifiedIcon = $attachment;
                        unset ($attachments[$i]);
                        break;

                    case 'classified_gallery':
                        $galleryImages[$i] = $attachment;
                        unset ($attachments[$i]);
                        break;
                }
            }
        }

        $currencies = array(
            'IDR' => array(
                'value' => 'IDR',
                'title' => 'Indonesia Rupiah'
            ),
            'USD' => array(
                'value' => 'USD',
                'title' => 'United States Dollars'
            ),
            'AUD' => array(
                'value' => 'AUD',
                'title' => 'Australian Dollar'
            ),
            'GBP' => array(
                'value' => 'GBP',
                'title' => 'British Pound'
            ),
            'EUR' => array(
                'value' => 'EUR',
                'title' => 'Euro'
            ),
        );

        $options = KomuKuYJB_Options::getInstance();
        if ($options->get('customCurrencyId'))
        {
            $currencies[$options->get('customCurrencyId')] = array(
                'value' => $options->get('customCurrencyId'),
                'title' => $options->get('customCurrencyTitle')
            );
        }

        /** @var XenForo_Model_Tag $tagModel */
        $tagModel = $this->getModelFromCache('XenForo_Model_Tag');
        $tagger = $tagModel->getTagger('classified');

        if (empty($classified['classified_id']))
        {
            $tagger->setPermissionsFromContext($category);
            $canEditTags = $this->models()->classified()->canEditTags(null, $category);
        }
        else
        {
            $tagger->setPermissionsFromContext($classified, $category);
            $canEditTags = $this->models()->classified()->canEditTags($classified, $category);
        }

        $viewParams += array(
            'classified' => $classified,
            'categoryBreadcrumbs' => $categoryBreadcrumbs,
            'category' => $category,

            'customFields' => $this->models()->field()->groupFields($customFields),
            'prefixes' => $this->models()->prefix()->getUsablePrefixesInCategories($category['category_id']),
            'advertTypes' => $advertTypes,
            'packages' => $packages,

            'attachments' => $attachments,
            'attachmentParams' => $classifiedModel->getAttachmentParams(),
            'attachmentConstraints' => $classifiedModel->getAttachmentConstraints(),

            'classifiedIconUploaderId' => $classifiedIconUploaderId,
            'galleryUploaderId' => $galleryUploaderId,
            'fileParams' => array(
                $classifiedIconUploaderId => $classifiedModel->getClassifiedIconParams(array(), array(
                    'category_id' => $category['category_id']
                )),
                $galleryUploaderId => $classifiedModel->getGalleryImageParams(array(), array(
                    'category_id' => $category['category_id']
                ))
            ),
            'fileConstraints' => array(
                $classifiedIconUploaderId => $classifiedModel->getClassifiedIconConstraints(),
                $galleryUploaderId => $classifiedModel->getGalleryImageConstraints()
            ),

            'classifiedIcon' => $classifiedIcon,
            'galleryImages' => $galleryImages,

            'currencies' => $currencies,
            'baseCurrency' => $this->models()->package()->getDefaultCurrency(),

            'canEditTags' => $canEditTags,
            'tagPermissions' => $tagger->getPermissions()
        );

        return $this->responseView('KomuKuYJB_ViewPublic_Classified_Edit', 'classifieds_item_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();
        $categoryModel = $this->models()->category();
        $packageModel = $this->models()->package();

        $classifiedId = $this->_input->filterSingle('classified_id', XenForo_Input::UINT);
        if ($classifiedId)
        {
            list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable($classifiedId);
            if (!$this->models()->classified()->canEditClassified($classified, $category, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }

            $package = $packageModel->getPackageById($classified['package_id']);
            $canEditCategory = $categoryModel->hasPermission('editAny', $category);
            $canEditPackage = $categoryModel->hasPermission('editAny', $category);
        }
        else
        {
            $category = false;
            $classified = false;
            $package = false;
            $canEditCategory = true;
            $canEditPackage = true;
        }

        $data = $this->_input->filter(array(
            'category_id' => XenForo_Input::UINT,
            'title' => XenForo_Input::STRING,
            'tag_line' => XenForo_Input::STRING,
            'prefix_id' => XenForo_Input::UINT,
            'package_id' => XenForo_Input::UINT,
            'advert_type_id' => XenForo_Input::UINT,
            'price' => XenForo_Input::UNUM,
            'currency' => XenForo_Input::STRING,
            'featured_image_attachment_id' => XenForo_Input::UINT
        ));

        if (!$data['category_id'])
        {
            return $this->responseError(new XenForo_Phrase('you_must_select_category'));
        }

        if (empty($data['price']) && $this->_input->filterSingle('price', XenForo_Input::STRING) === '')
        {
            return $this->responseError(array(
                'price' => new XenForo_Phrase('you_must_enter_price_or_enter_zero_for_no_price')
            ));
        }

        $newCategory = $category;

        if ($canEditCategory)
        {
            if (!$classified || $classified['category_id'] != $data['category_id'])
            {
                $newCategory = $this->getContentHelper()->assertCategoryValidAndViewable($data['category_id']);
                if (!$categoryModel->canAddClassified($newCategory, $key))
                {
                    throw $this->getErrorOrNoPermissionResponseException($key);
                }
            }

            $categoryId = $data['category_id'];
        }
        else
        {
            $categoryId = $classified['category_id'];
            unset ($data['category_id']);
        }

        $newPackage = $package;

        if ($canEditPackage)
        {
            if (!$classified || $classified['package_id'] != $data['package_id'] || $classified['category_id'] != $categoryId)
            {
                $newPackage = $packageModel->getPackageById($data['package_id']);
                if (!$newPackage || !$packageModel->verifyPackageIsUsable($newPackage['package_id'], $categoryId))
                {
                    return $this->responseError(new XenForo_Phrase('please_select_valid_package'));
                }
            }

            $packageId = $data['package_id'];
        }
        else
        {
            $packageId = $classified['package_id'];
            unset ($data['package_id']);
        }

        if (!$classified || $classified['prefix_id'] != $data['prefix_id'] || $classified['category_id'] != $categoryId)
        {
            if (!$this->models()->prefix()->verifyPrefixIsUsable($data['prefix_id'], $categoryId))
            {
                $data['prefix_id'] = 0;
            }
        }

        /** @var KomuKuYJB_DataWriter_Classified $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');

        if ($classifiedId)
        {
            $writer->setExistingData($classifiedId);
        }
        else
        {
            $visitor = XenForo_Visitor::getInstance();
            $writer->set('user_id', $visitor->get('user_id'));
            $writer->set('username', $visitor->get('username'));
        }

        $description = $this->getHelper('Editor')->getMessageText('description', $this->_input);
        $description = XenForo_Helper_String::autoLinkBbCode($description);

        $writer->bulkSet($data);
        $writer->set('description', $description);

        if (!$classifiedId || $newPackage['package_id'] != $package['package_id'])
        {
            if (
                $newPackage['always_moderate_create']
                && ($writer->get('classified_state') == 'visible' || !$classifiedId)
                && !$categoryModel->hasPermission('approveUnapprove', $newCategory)
            )
            {
                $writer->set('classified_state', 'moderated');
            }
        }

        $writer->setExtraData($writer::DATA_GALLERY_HASH, $this->_input->filterSingle('gallery_hash', XenForo_Input::STRING));
        $writer->setExtraData($writer::DATA_ATTACHMENT_HASH, $this->_input->filterSingle('attachment_hash', XenForo_Input::STRING));
        //$writer->setExtraData($writer::DATA_CLASSIFIED_ICON_HASH, $this->_input->filterSingle('classified_icon_hash', XenForo_Input::STRING));

        $customFields = $this->getContentHelper()->getCustomFieldValues($null, $shownCustomFields);
        $writer->setCustomFields($customFields, $shownCustomFields);

        if (!$classifiedId)
        {
            $watch = XenForo_Visitor::getInstance()->get('default_watch_state');
            if (!$watch)
            {
                $watch = 'watch_no_email';
            }

            $writer->setExtraData($writer::DATA_THREAD_WATCH_DEFAULT, $watch);
        }

        if ($newCategory['require_location'])
        {
            $loc = $this->_input->filterSingle('location', XenForo_Input::ARRAY_SIMPLE);
            if (empty($loc['longitude']) || empty($loc['latitude']))
            {
                return $this->responseError(new XenForo_Phrase('no_valid_location_data_found'));
            }

            $loc = new XenForo_Input($loc);
            $locWriter = $writer->getLocationWriter();

            $locWriter->bulkSet($loc->filter(array(
                'latitude' => XenForo_Input::FLOAT,
                'longitude' => XenForo_Input::FLOAT,
                'route' => XenForo_Input::STRING,
                'neighborhood' => XenForo_Input::STRING,
                'sublocality_level_1' => XenForo_Input::STRING,
                'locality' => XenForo_Input::STRING,
                'administrative_area_level_2' => XenForo_Input::STRING,
                'administrative_area_level_1' => XenForo_Input::STRING,
                'country' => XenForo_Input::STRING,
                'location_private' => XenForo_Input::BOOLEAN
            )));

            unset ($loc);
        }

        $tagger = null;

        if ($this->models()->classified()->canEditTags($classified ?: null, $newCategory))
        {
            /** @var XenForo_Model_Tag $tagModel */
            $tagModel = $this->getModelFromCache('XenForo_Model_Tag');
            $tagger = $tagModel->getTagger('classified');

            if (is_array($classified))
            {
                $tagger->setContent($classified['classified_id']);
                $tagger->setPermissionsFromContext($classified, $newCategory);
            }
            else
            {
                $tagger->setPermissionsFromContext($newCategory);
            }

            $tags = $this->_input->filterSingle('tags', XenForo_Input::STRING);
            $tagger->setTags($tagModel->splitTags($tags));
            $writer->mergeErrors($tagger->getErrors());
        }

        $writer->preSave();

        if ($newCategory['require_prefix']
            && !$writer->get('prefix_id')
            && (!$classified || $classified['category_id'] == $newCategory['category_id'])
        )
        {
            $writer->error(new XenForo_Phrase('please_select_a_prefix'), 'prefix_id');
        }

        if (!$writer->hasErrors() && $writer->isInsert())
        {
            $this->assertNotFlooding('post');
        }

        $writer->save();
        $classified = $writer->getMergedData();

        if ($writer->isUpdate() && XenForo_Visitor::getUserId() != $classified['user_id'])
        {
            $basicLog = $this->_getLogChanges($writer);
            if ($basicLog)
            {
                if (isset($basicLog['prefix_id']))
                {
                    if ($basicLog['prefix_id'])
                    {
                        $phrase = new XenForo_Phrase('classifieds_prefix_' . $basicLog['prefix_id']);
                        $oldValue = $phrase->render();
                    }
                    else
                    {
                        $oldValue = '-';
                    }

                    XenForo_Model_Log::logModeratorAction('classified', $classified, 'prefix', array('old' => $oldValue));
                    unset ($basicLog['prefix_id']);
                }
            }

            if ($basicLog)
            {
                XenForo_Model_Log::logModeratorAction('classified', $classified, 'edit', $basicLog);
            }
        }

        if ($tagger)
        {
            if ($writer->isInsert())
            {
                $tagger->setContent($classified['classified_id'], true);
            }

            $tagger->save();
        }

        if ($writer->isInsert())
        {
            $this->_getDraftModel()->deleteDraft("classifieds-category-$categoryId");

            $watch = XenForo_Visitor::getInstance()->get('default_classified_watch_state');
            $this->models()->classifiedWatch()->setClassifiedWatchState(
                XenForo_Visitor::getUserId(), $writer->get('classified_id'), $watch
            );
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::RESOURCE_CREATED,
            $this->_buildLink('classifieds', $classified)
        );
    }

    public function actionActivate()
    {
        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canActivateClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $payment = $this->models()->payment()->getLatestPaymentInfoByClassified($classified['classified_id']);
        if (!$payment || $payment['user_id'] != $classified['user_id'])
        {
            return $this->responseNoPermission();
        }

        if ($this->models()->classified()->hasPermission('activateAny', $category))
        {
            $this->_checkCsrfFromToken();

            /** @var KomuKuYJB_DataWriter_Classified $writer */
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
            $writer->setExistingData($classified);

            $writer->set('classified_state', 'visible'); // a moderator is activating the classified... no need to send for moderation...

            $payment = new KomuKuYJB_Eloquent_Payment($payment);
            $payment['payment_date'] = XenForo_Application::$time;
            $payment['payment_complete'] = 1;
            $payment['payment_refund'] = 0;

            $writer->save();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds', $classified)
            );
        }

        /* @var $processorModel bdPaygate_Model_Processor */
        $processorModel = $this->getModelFromCache('bdPaygate_Model_Processor');
        $processorNames = $processorModel->getProcessorNames();
        $processors = array();

        foreach ($processorNames as $processorId => $processorClass)
        {
            $processors[$processorId] = bdPaygate_Processor_Abstract::create($processorClass);
        }

        $viewParams = array(
            'classified' => $classified,
            'category' => $category,
            'payment' => $payment,
            'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category),

            'processors' => $processors,
            'itemId' => $processorModel->generateItemId(
                $payment['is_renewal'] ? 'classified_renew' : 'classified_open',
                XenForo_Visitor::getInstance(), array($classified['classified_id'], $payment['payment_id'])
            )
        );

        return $this->responseView('KomuKuYJB_ViewPublic_Classified_Pay', 'classifieds_item_pay', $viewParams);
    }

    public function actionPayCompleted()
    {
        return $this->actionPaymentCompleted();
    }

    public function actionPaymentCompleted()
    {
        list($classified) = $this->getContentHelper()->assertClassifiedValidAndViewable();
        $payment = $this->models()->payment()->getLatestPaymentInfoByClassified($classified['classified_id']);

        if (!$payment || XenForo_Application::$time - $payment['payment_date'] > 86400)
        {
            return $this->responseNoPermission();
        }

        return $this->responseMessage(new XenForo_Phrase('payment_received_please_allow_couple_of_minutes_for_the_system_to_process_payment'));
    }

    public function actionPaymentCancelled()
    {
        list($classified) = $this->getContentHelper()->assertClassifiedValidAndViewable();
        $payment = $this->models()->payment()->getLatestPaymentInfoByClassified($classified['classified_id']);

        if (!$payment || XenForo_Application::$time - $payment['payment_date'] > 86400)
        {
            return $this->responseNoPermission();
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds', $classified)
        );
    }

    public function actionPreview()
    {
        if (!$this->_request->isXmlHttpRequest())
        {
            return $this->responseNoPermission();
        }

        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$classified['canViewPreview'])
        {
            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Preview', '', array('classified' => false));
        }

        $viewParams = array(
            'classified' => $classified,
            'category' => $category
        );

        return $this->responseView('KomuKuYJB_ViewPublic_Classified_Preview', 'classifieds_list_item_preview', $viewParams);
    }

    public function actionEditPreview()
    {
        $this->_assertPostOnly();
        $contentHelper = $this->getContentHelper();

        $classifiedId = $this->_input->filterSingle('classified_id', XenForo_Input::UINT);
        if ($classifiedId)
        {

        }
        else
        {
            $categoryId = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
        }

        $description = $this->getHelper('Editor')->getMessageText('description', $this->_input);
        $description = XenForo_Helper_String::autoLinkBbCode($description);

        $viewParams = array(
            'description' => $description
        );

        return $this->responseView('KomuKuYJB_ViewPublic_Classified_Preview', 'classifieds_item_edit_preview', $viewParams);
    }

    public function actionLike()
    {
        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canLikeClassified($classified, $category, $key))
        {
            throw $this->getErrorOrNoPermissionResponseException($key);
        }

        $likeModel = $this->_getLikeModel();
        $existingLike = $likeModel->getContentLikeByLikeUser('classified', $classified['classified_id'], XenForo_Visitor::getUserId());

        if ($this->_request->isPost())
        {
            if ($existingLike)
            {
                $latestUsers = $likeModel->unlikeContent($existingLike);
            }
            else
            {
                $latestUsers = $likeModel->likeContent('classified', $classified['classified_id'], $classified['user_id']);
            }

            $liked = ($existingLike ? false : true);

            if ($this->_noRedirect() && $latestUsers !== false)
            {
                $classified['likeUsers'] = $latestUsers;
                $classified['likes'] += ($liked ? 1 : -1);
                $classified['like_date'] = ($liked ? XenForo_Application::$time : 0);

                $viewParams = array(
                    'classified' => $classified,
                    'liked' => $liked
                );

                return $this->responseView(
                    'KomuKuYJB_ViewPublic_Classified_LikeConfirmed', '', $viewParams
                );
            }
            else
            {
                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $this->_buildLink('classifieds', $classified)
                );
            }
        }
        else
        {
            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category),
                'like' => $existingLike
            );

            return $this->responseView(
                'KomuKuYJB_ViewPublic_Classified_Like', 'classifieds_item_like', $viewParams
            );
        }
    }

    public function actionLikes()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        $likes = $this->_getLikeModel()->getContentLikes('classified', $classified['classified_id']);
        if (!$likes)
        {
            return $this->responseError(new XenForo_Phrase('no_one_has_liked_this_classified_yet'), 404);
        }

        $viewParams = array(
            'classified' => $classified,
            'category' => $category,
            'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category),
            'likes' => $likes
        );

        return $this->responseView('KomuKuYJB_ViewPublic_Classified_Likes', 'classifieds_item_likes', $viewParams);
    }

    public function actionReport()
    {
        list($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canReportClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            $reportMessage = $this->_input->filterSingle('message', XenForo_Input::STRING);
            if (!$reportMessage)
            {
                return $this->responseError(new XenForo_Phrase('please_enter_reason_for_reporting_this_message'));
            }

            $this->assertNotFlooding('report');

            $classified['category'] = $category;

            /* @var $reportModel XenForo_Model_Report */
            $reportModel = XenForo_Model::create('XenForo_Model_Report');
            $reportModel->reportContent('classified', $classified, $reportMessage);

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds', $classified),
                new XenForo_Phrase('thank_you_for_reporting_this_message')
            );
        }
        else
        {
            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Report', 'classifieds_item_report', $viewParams);
        }
    }

    /* Moderation stuff... */

    public function actionUnapprove()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canUnapproveClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        $writer->setExistingData($classified);
        $writer->set('classified_state', 'moderated');
        $writer->save();

        XenForo_Model_Log::logModeratorAction('classified', $classified, 'unapprove');

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds', $classified)
        );
    }

    public function actionApprove()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));

        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canApproveClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        $writer->setExistingData($classified);
        $writer->set('classified_state', 'visible');
        $writer->save();

        XenForo_Model_Log::logModeratorAction('classified', $classified, 'approve');

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds', $classified)
        );
    }

    public function actionDelete()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        $hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
        $deleteType = ($hardDelete ? 'hard' : 'soft');

        if (!$this->models()->classified()->canDeleteClassified($classified, $category, $deleteType, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            /** @var KomuKuYJB_DataWriter_Classified $writer */
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
            $writer->setExistingData($classified);

            if ($hardDelete)
            {
                $writer->delete();

                XenForo_Model_Log::logModeratorAction('classified', $classified, 'delete_hard');
            }
            else
            {
                $reason = $this->_input->filterSingle('reason', XenForo_Input::STRING);
                $writer->setExtraData($writer::DATA_DELETE_REASON, $reason);
                $writer->set('classified_state', 'deleted');
                $writer->save();

                if (XenForo_Visitor::getUserId() != $classified['user_id'])
                {
                    XenForo_Model_Log::logModeratorAction('classified', $classified, 'delete_soft', array('reason' => $reason));
                }
            }

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/categories', $category)
            );
        }
        else
        {
            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category),
                'canHardDelete' => $this->models()->classified()->canDeleteClassified($classified, $category, 'hard')
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Delete', 'classifieds_item_delete', $viewParams);
        }
    }

    public function actionUndelete()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canUndeleteClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
            $writer->setExistingData($classified);
            $writer->set('classified_state', 'visible');
            $writer->save();

            XenForo_Model_Log::logModeratorAction('classified', $classified, 'undelete');

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds', $classified)
            );
        }
        else
        {
            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category),
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Undelete', 'classifieds_item_undelete', $viewParams);
        }
    }

    public function actionToggleFeatured()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canFeatureUnfeatureClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($classified['feature_date'])
        {
            $this->models()->classified()->unfeatureClassified($classified);

            $redirectPhrase = 'classified_unfeatured';
            $actionPhrase = 'feature_classified';
        }
        else
        {
            $this->models()->classified()->featureClassified($classified);

            $redirectPhrase = 'classified_featured';
            $actionPhrase = 'unfeature_classified';
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds', $classified),
            new XenForo_Phrase($redirectPhrase),
            array('actionPhrase' => new XenForo_Phrase($actionPhrase))
        );
    }

    public function actionOpen()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canOpenClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        $writer->setExistingData($classified);
        $writer->set('classified_state', 'visible');
        $writer->save();

        XenForo_Model_Log::logModeratorAction('classified', $classified, 'open');

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds', $classified)
        );
    }

    public function actionMarkComplete()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canMarkClassifiedAsComplete($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            $username = $this->_input->filterSingle('username', XenForo_Input::STRING);

            $user = $this->models()->user()->getUserByName($username, array(
                'join' => XenForo_Model_User::FETCH_USER_FULL
            ));

            if ($user)
            {
                if ($user['user_id'] == $classified['user_id'])
                {
                    return $this->responseError(new XenForo_Phrase('you_cannot_mark_yourself_as_other_party'), 403);
                }

                $this->models()->classified()->markClassifiedAsCompleted(
                    $classified['classified_id'], $user['user_id'], $user['username']
                );

                $classified['classified_state'] = 'completed';
                $classified['complete_user_id'] = $user['user_id'];
                $classified['complete_username'] = $user['username'];
                $classified['complete_date'] = XenForo_Application::$time;

                if (!$this->models()->traderRating()->canAddTraderRating($classified) || !$this->_input->filterSingle('rate_user', XenForo_Input::BOOLEAN))
                {
                    return $this->responseRedirect(
                        XenForo_ControllerResponse_Redirect::SUCCESS,
                        $this->_buildLink('classifieds', $classified)
                    );
                }

                XenForo_Application::set('classifiedUserRatingClassified', $classified);
                XenForo_Application::set('classifiedUserRatingUser', $user);

                return $this->responseReroute('KomuKuYJB_ControllerPublic_TraderRating', 'add');
            }
            else
            {
                $this->models()->classified()->markClassifiedAsCompleted(
                    $classified['classified_id'], 0, ''
                );

                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $this->_buildLink('classifieds', $classified)
                );
            }
        }
        else
        {
            $advertType = $this->models()->advertType()->getAdvertTypeById($classified['advert_type_id']);
            $this->models()->advertType()->prepareAdvertType($advertType);

            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'advertType' => $advertType,

                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_MarkComplete', 'classifieds_item_mark_complete', $viewParams);
        }
    }

    public function actionBump()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();
      
        if (!$this->models()->classified()->canBumpClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $this->models()->classified()->bumpClassified($classified,XenForo_Visitor::getUserId());
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds', $classified)
        );
    }

    public function actionClose()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canCloseClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
            $writer->setExistingData($classified);
            $writer->set('classified_state', 'closed');
            $writer->save();

            if (XenForo_Visitor::getUserId() != $classified['user_id'])
            {
                XenForo_Model_Log::logModeratorAction('classified', $classified, 'close');
            }

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds', $classified)
            );
        }
        else
        {
            $viewParams = array(
                'classified' => $classified,
                'category' => $category,

                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Close', 'classifieds_item_close', $viewParams);
        }
    }

    public function actionRenew()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canRenewClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $package = $this->models()->package()->getPackageById($classified['package_id']);
        if (!$package)
        {
            return $this->responseError('associated_package_for_classified_does_not_exist', 404);
        }

        if ($this->isConfirmedPost())
        {
            /** @var KomuKuYJB_DataWriter_Classified $writer */
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
            $writer->setOption($writer::OPTION_USER_EDITING, false);
            $writer->setOption($writer::OPTION_IS_RENEWAL, true);
            $writer->setExistingData($classified);
            $writer->save();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds', $classified),
                new XenForo_Phrase('classified_successfully_renewed')
            );
        }
        else
        {
            $this->models()->package()->preparePackage($package);
            list (, $renewalFee) = $this->models()->classified()->calculatePayment($classified['price_base_currency'], $package);

            $viewParams = array(
                'classified' => $classified,
                'package' => $package,
                'renewalFee' => $renewalFee,

                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Renew', 'classifieds_item_renew', $viewParams);
        }
    }

    public function actionReassign()
    {
        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();

        if (!$this->models()->classified()->canReassignClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            $user = $this->getModelFromCache('XenForo_Model_User')->getUserByName(
                $this->_input->filterSingle('username', XenForo_Input::STRING),
                array('join' => XenForo_Model_User::FETCH_USER_PERMISSIONS)
            );

            if (!$user)
            {
                return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
            }

            $user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);
            if (!XenForo_Permission::hasPermission($user['permissions'], 'classifieds', 'view'))
            {
                return $this->responseError(new XenForo_Phrase('you_may_only_reassign_classified_to_user_with_permission_to_view'));
            }

            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
            $writer->setExistingData($classified);
            $writer->set('user_id', $user['user_id']);
            $writer->set('username', $user['username']);
            $writer->save();

            XenForo_Model_Log::logModeratorAction('classified', $classified, 'reassign', array('form' => $writer->getExisting('username')));

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds', $classified)
            );
        }
        else
        {
            $viewParams = array(
                'classified' => $classified,
                'category' => $category,
                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('KomuKuYJB_ViewPublic_Classified_Reassign', 'classifieds_item_reassign', $viewParams);
        }
    }

    /**
     * @return XenForo_Model_Draft
     */
    protected function _getDraftModel()
    {
        return $this->getModelFromCache('XenForo_Model_Draft');
    }

    protected function _getWrapper($selectedTab, XenForo_ControllerResponse_View $subView, array $classified, array $category, array $containerParams = array())
    {
        return $this->getHelper('KomuKuYJB_ControllerHelper_PageWrapper')->getClassifiedViewWrapper($selectedTab, $subView, $classified, $category, $containerParams);
    }

    protected function _getClassifiedViewInfo(array $fetchOptions = array())
    {
        $fetchOptions += array(
            'join' => 0,
            'likeUserId' => XenForo_Visitor::getUserId(),
            'watchUserId' => XenForo_Visitor::getUserId(),
            'contactUserId' => XenForo_Visitor::getUserId()
        );

        $fetchOptions['join'] |= KomuKuYJB_Model_Classified::FETCH_CATEGORY | KomuKuYJB_Model_Classified::FETCH_LOCATION;

        if (XenForo_Visitor::getInstance()->hasPermission('classifieds', 'viewDeleted'))
        {
            $fetchOptions['join'] |= KomuKuYJB_Model_Classified::FETCH_DELETION_LOG;
        }

        list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable(null, $fetchOptions);
        return array($classified, $category);
    }

    public static function getSessionActivityDetailsForList(array $activities)
    {
        $classifiedIds = array();

        foreach ($activities as $activity)
        {
            if (!empty($activity['params']['classified_id']))
            {
                $classifiedIds[$activity['params']['classified_id']] = intval($activity['params']['classified_id']);
            }
        }

        $classifiedData = array();

        if ($classifiedIds)
        {
            /** @var KomuKuYJB_Model_Classified $classifiedModel */ /** @var KomuKuYJB_Model_Category $categoryModel */
            $classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');
            $categoryModel = XenForo_Model::create('KomuKuYJB_Model_Category');
            $permissionCombinationId = XenForo_Visitor::getPermissionCombinationId();

            $classifieds = $classifiedModel->getClassifiedsByIds($classifiedIds, array(
                'join' => KomuKuYJB_Model_Classified::FETCH_CATEGORY,
                'permissionCombinationId' => $permissionCombinationId
            ));

            foreach ($classifieds as $classified)
            {
                $categoryModel->setCategoryPermCache($permissionCombinationId, $classified['category_id'], $classified['category_permission_cache']);

                if ($classifiedModel->canViewClassifiedAndContainer($classified, $classified))
                {
                    $classified['title'] = XenForo_Helper_String::censorString($classified['title']);
                    $classifiedData[$classified['classified_id']] = array(
                        'title' => $classified['title'],
                        'url' => XenForo_Link::buildPublicLink('classifieds', $classified)
                    );
                }
            }
        }

        $output = array();

        foreach ($activities as $key => $activity)
        {
            $classified = false;

            if (!empty($activity['params']['classified_id']))
            {
                $classifiedId = $activity['params']['classified_id'];
                if (isset($classifiedData[$classifiedId]))
                {
                    $classified = $classifiedData[$classifiedId];
                }
            }

            if ($classified)
            {
                $output[$key] = array(
                    new XenForo_Phrase('viewing_classified'),
                    $classified['title'],
                    $classified['url'],
                    ''
                );
            }
            else
            {
                $output[$key] = new XenForo_Phrase('viewing_classified');
            }
        }

        return $output;
    }
}