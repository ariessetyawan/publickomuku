<?php /*e683c2b62c2af95b3a77be01ded9f6b9dc723b5c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000011 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        $this->table()->create('kmk_classifieds_classified', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('classified_id')->unsigned(true)->autoIncrement(true);
            $table->string('title', 100);
            $table->string('tag_line', 100);
            $table->integer('user_id')->unsigned(true);
            $table->string('username', 100);
            $table->string('classified_state', 20)->default('visible');
            $table->integer('feature_date')->unsigned(true)->default(0);
            $table->integer('classified_date')->unsigned(true);
            $table->integer('expire_date')->unsigned(true)->default(0);
            $table->integer('complete_date')->unsigned(true)->default(0);
            $table->mediumText('description');

            $table->integer('category_id')->unsigned(true);
            $table->integer('advert_type_id')->unsigned(true);
            $table->integer('package_id')->unsigned(true);
            $table->integer('discussion_thread_id')->unsigned(true);
            $table->integer('prefix_id')->unsigned(true)->default(0);

            $table->double('price', 14, 2);
            $table->double('price_base_currency', 15, 7);
            $table->string('currency', 3)->default('');

            $table->integer('likes')->unsigned(true)->default(0);
            $table->blob('like_users');
            $table->blob('custom_classified_fields');
            $table->integer('ip_id')->unsigned(true)->default(0);
            $table->integer('warning_id')->unsigned(true)->default(0);
            $table->string('warning_message', 255)->default('');

            $table->integer('update_count')->unsigned(true)->default(0);
            $table->integer('comment_count')->unsigned(true)->default(0);
            $table->integer('renewal_count')->unsigned(true)->default(0);
            $table->integer('gallery_count')->unsigned(true)->default(0);
            $table->integer('attach_count')->unsigned(true)->default(0);
            $table->integer('view_count')->unsigned(true)->default(0);

            $table->boolean('had_first_visible')->default(0);
            $table->integer('last_update')->unsigned(true)->default(0);
            $table->integer('last_comment_id')->unsigned(true)->default(0);
            $table->integer('last_comment_date')->unsigned(true)->default(0);
            $table->integer('last_comment_user_id')->unsigned(true)->default(0);
            $table->string('last_comment_username', 100)->default('');
            $table->integer('complete_user_id')->unsigned(true)->default(0);
            $table->string('complete_username', 100)->default('');
            $table->integer('featured_image_date')->unsigned(true)->default(0);
            $table->integer('featured_image_attachment_id')->unsigned(true)->default(0);
			$table->integer('last_bump_date')->unsigned(true)->default(0);

            $table->mediumBlob('tags');

            $table->primary('classified_id');
            $table->index(array('category_id', 'last_update'), 'category_last_update');
            $table->index(array('advert_type_id', 'last_update'), 'advert_type_last_update');
            $table->index('last_update');
            $table->index(array('user_id', 'last_update'), 'user_last_update');
            $table->index('discussion_thread_id');
            $table->index('prefix_id');
            $table->index('last_bump_date');
        });

        $this->table()->create('kmk_classifieds_classified_location', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('classified_id')->unsigned(true);
            $table->boolean('location_private')->default(0);
            $table->float('latitude', 10, 6);
            $table->float('longitude', 10, 6);
            $table->string('route', 100);
            $table->string('neighborhood', 100);
            $table->string('sublocality_level_1', 100);
            $table->string('locality', 100);
            $table->string('administrative_area_level_2', 100);
            $table->string('administrative_area_level_1', 100);
            $table->string('country', 2);

            $table->primary('classified_id');
            $table->index('country');
        });

        $this->table()->create('kmk_classifieds_classified_watch', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('user_id')->unsigned(true);
            $table->integer('classified_id')->unsigned(true);
            $table->boolean('email_subscribe')->default(0);
            $table->string('watch_key', 16);

            $table->primary(array('user_id', 'classified_id'));
        });

        $this->table()->create('kmk_classifieds_classified_view', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->engine = $table::ENGINE_MEMORY;
            $table->integer('classified_id')->unsigned(true);
            $table->index('classified_id');
        });

        $this->table()->create('kmk_classifieds_category', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('category_id')->unsigned(true)->autoIncrement(true);
            $table->string('title', 100);
            $table->text('description');
            $table->integer('parent_category_id')->unsigned(true)->default(0);
            $table->integer('depth')->unsigned(true)->default(0);
            $table->integer('lft')->unsigned(true)->default(0);
            $table->integer('rgt')->unsigned(true)->default(0);
            $table->integer('display_order')->unsigned(true)->default(0);
            $table->integer('classified_count')->unsigned(true)->default(0);
            $table->integer('last_update')->unsigned(true)->default(0);
            $table->string('last_classified_title', 100)->default('');
            $table->integer('last_classified_id')->unsigned(true)->default(0);
            $table->blob('category_breadcrumb');

            $table->integer('thread_node_id')->unsigned(true)->default(0);
            $table->integer('thread_prefix_id')->unsigned(true)->default(0);
            $table->integer('complete_thread_prefix_id')->unsigned(true)->default(0);

            $table->boolean('enable_comment')->default(1);
            $table->boolean('require_location')->default(0);
            $table->boolean('require_prefix')->default(0);

            $table->mediumBlob('advert_type_cache');
            $table->mediumBlob('package_cache');
            $table->mediumBlob('field_cache');
            $table->mediumBlob('rating_criteria_cache');
            $table->mediumBlob('prefix_cache');

            $table->integer('featured_count')->unsigned(true)->default(0);
            $table->tinyInteger('min_tags')->default(0)->unsigned(true);

            $table->primary('category_id');
            $table->index(array('parent_category_id', 'lft'));
            $table->index(array('lft', 'rgt'));
        });

        $this->table()->create('kmk_classifieds_category_watch', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('user_id')->unsigned(true);
            $table->integer('category_id')->unsigned(true);
            $table->enum('notify_on', array('', 'classified'))->default('');
            $table->boolean('send_alert')->default(0);
            $table->boolean('send_email')->default(0);
            $table->boolean('include_children')->default(1);

            $table->primary(array('user_id', 'category_id'));
            $table->index(array('category_id', 'notify_on'));
        });

        $this->table()->create('kmk_classifieds_package', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('package_id')->unsigned(true)->autoIncrement(true);
            $table->integer('display_order')->unsigned(true)->default(0);
            $table->integer('advert_duration')->unsigned(true)->default(0);
            $table->integer('max_renewal')->default(0);
            $table->boolean('auto_feature_item')->default(0);

            $table->boolean('always_moderate_create')->default(0);
            $table->boolean('always_moderate_update')->default(0);
            $table->boolean('always_moderate_renewal')->default(0);

            $table->boolean('active')->default(1);
            $table->enum('price_format', array('flat', 'flexible', 'percentile'));
            $table->blob('price_rate');
            $table->mediumBlob('user_criteria');

            $table->primary('package_id');
            $table->index('display_order');
        });

        $this->table()->create('kmk_classifieds_package_category', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('package_id')->unsigned(true);
            $table->integer('category_id')->unsigned(true);

            $table->primary(array('package_id', 'category_id'));
            $table->index('category_id');
        });

        $this->table()->create('kmk_classifieds_prefix', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('prefix_id')->unsigned(true)->autoIncrement(true);
            $table->integer('prefix_group_id')->unsigned(true)->default(0);
            $table->integer('display_order')->unsigned(true)->default(0);
            $table->integer('materialized_order')->unsigned(true)->default(0);
            $table->string('css_class', 50)->default('');
            $table->blob('allowed_user_group_ids');

            $table->primary('prefix_id');
            $table->index('materialized_order');
        });

        $this->table()->create('kmk_classifieds_prefix_category', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('prefix_id')->unsigned(true);
            $table->integer('category_id')->unsigned(true);

            $table->primary(array('prefix_id', 'category_id'));
            $table->index('category_id');
        });

        $this->table()->create('kmk_classifieds_prefix_group', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('prefix_group_id')->unsigned(true)->autoIncrement(true);
            $table->integer('display_order')->unsigned(true)->default(0);

            $table->primary('prefix_group_id');
        });

        $this->table()->create('kmk_classifieds_field', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->binary('field_id', 25);
            $table->string('display_group', 25)->default('above_info');
            $table->integer('display_order')->unsigned(true)->default(0);
            $table->string('field_type', 25)->default('textbox');
            $table->blob('field_choices');
            $table->boolean('can_be_filtered')->default(0);
            $table->boolean('include_in_classified_list')->default(0);
            $table->boolean('include_in_thread_list')->default(0);
            $table->boolean('include_in_thread_view')->default(0);
            $table->boolean('include_in_classified_editor')->default(1);
            $table->boolean('include_in_classified_view')->default(1);
            $table->boolean('required')->default(0);
            $table->string('match_type', 25)->default('none');
            $table->string('match_regex', 250)->default('');
            $table->string('match_callback_class', 75)->default('');
            $table->string('match_callback_method', 75)->default('');
            $table->integer('max_length')->unsigned(true)->default(0);
            $table->text('display_template');
            $table->text('hint_text')->default('');

            $table->primary('field_id');
            $table->index(array('display_group', 'display_order'), 'display_group_order');
            $table->index('can_be_filtered');
        });

        $this->table()->create('kmk_classifieds_field_value', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('classified_id')->unsigned(true);
            $table->binary('field_id', 25);
            $table->mediumText('field_value');

            $table->primary(array('classified_id', 'field_id'));
            $table->index('field_id');
        });

        $this->table()->create('kmk_classifieds_field_category', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->binary('field_id', 25);
            $table->integer('category_id')->unsigned(true);

            $table->primary(array('field_id', 'category_id'));
            $table->index('category_id');
        });

        $this->table()->create('kmk_classifieds_advert_type', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('advert_type_id')->unsigned(true)->autoIncrement(true);
            $table->string('badge_color', 30);
            $table->string('complete_badge_color', 30);
            $table->boolean('show_badge')->default(1);
            $table->integer('display_order')->unsigned(true)->default(0);
            $table->integer('classified_count')->unsigned(true)->default(0);

            $table->primary('advert_type_id');
            $table->index('display_order');
        });

        $this->table()->create('kmk_classifieds_advert_type_category', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('advert_type_id')->unsigned(true);
            $table->integer('category_id')->unsigned(true);

            $table->primary(array('advert_type_id', 'category_id'));
            $table->index('category_id');
        });

        $this->table()->create('kmk_classifieds_comment', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('comment_id')->unsigned(true)->autoIncrement(true);
            $table->integer('classified_id')->unsigned(true);
            $table->integer('user_id')->unsigned(true);
            $table->string('username', 100);
            $table->mediumText('message');
            $table->integer('ip_id')->unsigned(true)->default(0);
            $table->integer('post_date')->unsigned(true)->default(0);
            $table->enum('message_state', array('visible', 'moderated', 'deleted'))->default('visible');
            $table->integer('likes')->unsigned(true)->default(0);
            $table->blob('like_users');
            $table->integer('reply_comment_id')->unsigned(true)->default(0);
            $table->integer('reply_parent_comment_id')->unsigned(true)->default(0);
            $table->integer('reply_count')->unsigned(true)->default(0);
            $table->integer('first_reply_date')->unsigned(true)->default(0);
            $table->integer('warning_id')->unsigned(true)->default(0);
            $table->string('warning_message', 255)->default('');

            $table->primary('comment_id');
            $table->index('classified_id');
            $table->index('reply_parent_comment_id');
        });

        $this->table()->create('kmk_classifieds_conversation', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('conversation_id')->unsigned(true);
            $table->integer('user_id')->unsigned(true);
            $table->integer('classified_id')->unsigned(true);
            $table->boolean('show_location')->default(0);

            $table->primary('conversation_id');
            $table->index(array('user_id', 'classified_id'));
        });

        $this->table()->create('kmk_classifieds_payment', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('payment_id')->autoIncrement(true)->unsigned(true);
            $table->integer('payment_date')->unsigned(true);
            $table->integer('user_id')->unsigned(true);
            $table->integer('classified_id')->unsigned(true);
            $table->integer('package_id')->unsigned(true);
            $table->float('amount');
            $table->string('currency', 3, false);
            $table->boolean('is_renewal')->default(0);
            $table->boolean('payment_complete')->default(0);
            $table->boolean('payment_refund')->default(0);

            $table->primary('payment_id');
            $table->index('classified_id');
        });

        $this->table()->alter('kmk_user_option', function(GFNCore_Db_Schema_Table_Alter $table)
        {
            $table->enum('default_classified_watch_state', array('', 'watch_no_email', 'watch_email'))->default('watch_no_email')->after('default_watch_state');
            $table->enum('all_classified_watch_state', array('', 'watch_no_email', 'watch_email'))->default('')->after('default_watch_state');
        });

        $this->table()->alter('kmk_attachment_data', function(GFNCore_Db_Schema_Table_Alter $table)
        {
            $table->integer('slide_width')->unsigned(true)->default(0);
            $table->integer('slide_height')->unsigned(true)->default(0);
        });

        $this->_insertContentTypes();
        $this->_updatePermissions();
        $this->_insertDefaultData();
    }

    public function uninstall()
    {
        $this->table()->drop('kmk_classifieds_classified');
        $this->table()->drop('kmk_classifieds_classified_location');
        $this->table()->drop('kmk_classifieds_classified_watch');
        $this->table()->drop('kmk_classifieds_classified_view');
        $this->table()->drop('kmk_classifieds_category');
        $this->table()->drop('kmk_classifieds_category_watch');
        $this->table()->drop('kmk_classifieds_package');
        $this->table()->drop('kmk_classifieds_package_category');
        $this->table()->drop('kmk_classifieds_prefix');
        $this->table()->drop('kmk_classifieds_prefix_category');
        $this->table()->drop('kmk_classifieds_prefix_group');
        $this->table()->drop('kmk_classifieds_field');
        $this->table()->drop('kmk_classifieds_field_value');
        $this->table()->drop('kmk_classifieds_field_category');
        $this->table()->drop('kmk_classifieds_advert_type');
        $this->table()->drop('kmk_classifieds_advert_type_category');
        $this->table()->drop('kmk_classifieds_comment');
        $this->table()->drop('kmk_classifieds_review');
        $this->table()->drop('kmk_classifieds_conversation');
        $this->table()->drop('kmk_classifieds_payment');

        $this->table()->alter('kmk_user_option', function(GFNCore_Db_Schema_Table_Alter $table)
        {
            $table->drop('default_classified_watch_state');
        });

        $this->table()->alter('kmk_attachment_data', function(GFNCore_Db_Schema_Table_Alter $table)
        {
            $table->drop('slide_width');
            $table->drop('slide_height');
        });

        $handler = new GFNCore_Installer_Handler_ContentType('KomuKuYJB');
        $handler->deleteAll();

        $db = $this->db();
        $db->delete('kmk_permission_entry', 'permission_group_id = \'classifieds\'');
        $db->delete('kmk_admin_permission_entry', 'admin_permission_id IN (' . $db->quote(array('classifiedCategory', 'classifiedPrefix', 'classifiedField', 'classifiedPackage', 'classifiedAdvertType')) . ')');
        $db->delete('kmk_data_registry', 'data_key IN (' . $db->quote(array('classifiedPrefixes', 'classifiedFields')) . ')');

        $handler = new GFNCore_Installer_Handler_Permission();
        $moderators = $handler->getGlobalModPermissions();

        foreach ($moderators as $userId => $permissions)
        {
            unset ($permissions['classifieds']);
            $handler->updateGlobalModPermissions($userId, $permissions);
        }
    }

    protected function _insertContentTypes()
    {
        $handler = new GFNCore_Installer_Handler_ContentType('KomuKuYJB');

        // Category...
        $handler->add('classified_category', $handler::PERMISSION_HANDLER, 'KomuKuYJB_ContentPermission_Category');
        $handler->add('classified_category', $handler::MODERATOR_HANDLER, 'KomuKuYJB_ModeratorHandler_Category');
        $handler->add('classified_category', $handler::SITEMAP_HANDLER, 'KomuKuYJB_SitemapHandler_Category');

        // Classified...
        $handler->add('classified', $handler::ALERT_HANDLER, 'KomuKuYJB_AlertHandler_Classified');
        $handler->add('classified', $handler::ATTACHMENT_HANDLER, 'KomuKuYJB_AttachmentHandler_Classified');
        $handler->add('classified', $handler::EDIT_HISTORY_HANDLER, 'KomuKuYJB_EditHistoryHandler_Classified');
        $handler->add('classified', $handler::LIKE_HANDLER, 'KomuKuYJB_LikeHandler_Classified');
        $handler->add('classified', $handler::MODERATION_QUEUE_HANDLER, 'KomuKuYJB_ModerationQueueHandler_Classified');
        $handler->add('classified', $handler::MODERATOR_LOG_HANDLER, 'KomuKuYJB_ModeratorLogHandler_Classified');
        $handler->add('classified', $handler::NEWS_FEED_HANDLER, 'KomuKuYJB_NewsFeedHandler_Classified');
        $handler->add('classified', $handler::REPORT_HANDLER, 'KomuKuYJB_ReportHandler_Classified');
        $handler->add('classified', $handler::SEARCH_HANDLER, 'KomuKuYJB_Search_DataHandler_Classified');
        $handler->add('classified', $handler::SITEMAP_HANDLER, 'KomuKuYJB_SitemapHandler_Classified');
        $handler->add('classified', $handler::SPAM_HANDLER, 'KomuKuYJB_SpamHandler_Classified');
        $handler->add('classified', $handler::STATS_HANDLER, 'KomuKuYJB_StatsHandler_Classified');
        $handler->add('classified', $handler::WARNING_HANDLER, 'KomuKuYJB_WarningHandler_Classified');

        // Classified Gallery...
        $handler->add('classified_gallery', $handler::ATTACHMENT_HANDLER, 'KomuKuYJB_AttachmentHandler_ClassifiedGallery');

        // Classified Icon...
        $handler->add('classified_icon', $handler::ATTACHMENT_HANDLER, 'KomuKuYJB_AttachmentHandler_ClassifiedIcon');
    }

    protected function _updatePermissions()
    {
        $handler = new GFNCore_Installer_Handler_Permission();

        // General Permissions...
        $handler->applyGlobalPermission('classifieds', 'view', 'general', 'viewNode', false);
        $handler->applyGlobalPermission('classifieds', 'viewAttach', 'general', 'viewNode', false);
        $handler->applyGlobalPermission('classifieds', 'viewGallery', 'general', 'viewNode', false);
        $handler->applyGlobalPermission('classifieds', 'viewComment', 'general', 'viewNode', false);
        $handler->applyGlobalPermission('classifieds', 'viewEditHistory', 'general', 'viewNode', false);
        $handler->applyGlobalPermission('classifieds', 'search', 'general', 'search', false);
        $handler->applyGlobalPermission('classifieds', 'like', 'forum', 'like', false);
        $handler->applyGlobalPermission('classifieds', 'likeComment', 'forum', 'like', false);
        $handler->applyGlobalPermission('classifieds', 'contact', 'conversation', 'start', false);
        $handler->applyGlobalPermission('classifieds', 'add', 'forum', 'postThread', false);
        $handler->applyGlobalPermission('classifieds', 'max', -1, null, false);
        $handler->applyGlobalPermission('classifieds', 'uploadAttach', 'forum', 'uploadAttachment', false);
        $handler->applyGlobalPermission('classifieds', 'uploadGallery', 'forum', 'postThread', false);
        $handler->applyGlobalPermission('classifieds', 'openCloseSelf', 'forum', 'postThread', false);
        $handler->applyGlobalPermission('classifieds', 'addComment', 'forum', 'postReply', false);
        $handler->applyGlobalPermission('classifieds', 'editSelf', 'forum', 'editOwnPost', false);
        $handler->applyGlobalPermission('classifieds', 'deleteSelf', 'forum', 'deleteOwnPost', false);
        $handler->applyGlobalPermission('classifieds', 'bumpOwnClassifiedTime', 1440, null, false);
        $handler->applyGlobalPermission('classifieds', 'editOwnClassifiedTime', 'forum', 'editOwnPostTimeLimit', false);
        $handler->applyGlobalPermission('classifieds', 'editCommentSelf', 'forum', 'editOwnPost', false);
        $handler->applyGlobalPermission('classifieds', 'deleteCommentSelf', 'forum', 'deleteOwnPost', false);
        $handler->applyGlobalPermission('classifieds', 'editOwnCommentTime', 'forum', 'editOwnPostTimeLimit', false);

        // Moderator Permissions...
        $handler->applyGlobalPermission('classifieds', 'viewDeleted', 'forum', 'viewDeleted', true);
        $handler->applyGlobalPermission('classifieds', 'viewModerated', 'forum', 'viewModerated', true);
        $handler->applyGlobalPermission('classifieds', 'openCloseAny', 'forum', 'lockUnlockThread', true);
        $handler->applyGlobalPermission('classifieds', 'editAny', 'forum', 'manageAnyThread', true);
        $handler->applyGlobalPermission('classifieds', 'bumpAny', 'forum', 'manageAnyThread', true);
        $handler->applyGlobalPermission('classifieds', 'deleteAny', 'forum', 'manageAnyThread', true);
        $handler->applyGlobalPermission('classifieds', 'hardDeleteAny', 'forum', 'hardDeleteAnyThread', true);
        $handler->applyGlobalPermission('classifieds', 'undelete', 'forum', 'undelete', true);
        $handler->applyGlobalPermission('classifieds', 'warn', 'forum', 'warn', true);
        $handler->applyGlobalPermission('classifieds', 'approveUnapprove', 'forum', 'approveUnapprove', true);
        $handler->applyGlobalPermission('classifieds', 'reassign', 'forum', 'manageAnyThread', true);
        $handler->applyGlobalPermission('classifieds', 'featureUnfeature', 'forum', 'stickUnstickThread', true);
        $handler->applyGlobalPermission('classifieds', 'viewCommentDeleted', 'forum', 'viewDeleted', true);
        $handler->applyGlobalPermission('classifieds', 'viewCommentModerated', 'forum', 'viewModerated', true);
        $handler->applyGlobalPermission('classifieds', 'editCommentAny', 'forum', 'editAnyPost', true);
        $handler->applyGlobalPermission('classifieds', 'deleteCommentAny', 'forum', 'deleteAnyPost', true);
        $handler->applyGlobalPermission('classifieds', 'hardDeleteCommentAny', 'forum', 'hardDeleteAnyPost', true);
        $handler->applyGlobalPermission('classifieds', 'undeleteComment', 'forum', 'undelete', true);
        $handler->applyGlobalPermission('classifieds', 'warnComment', 'forum', 'warn', true);
        $handler->applyGlobalPermission('classifieds', 'approveUnapproveComment', 'forum', 'approveUnapprove', true);
    }

    protected function _insertDefaultData()
    {
        $this->_insertDefaultAdvertTypes();
    }

    protected function _insertDefaultAdvertTypes()
    {
        $data = array(
            array(
                'title' => 'For Sale',
                'zero_value_text' => 'Free',
                'complete_text' => 'Sold',
                'badge_color' => 'rgb(140, 193, 82)',
                'show_badge' => true,
                'display_order' => 10
            ),
            array(
                'title' => 'Wanted',
                'zero_value_text' => 'Offers',
                'complete_text' => 'Completed',
                'badge_color' => 'rgb(233, 87, 63)',
                'show_badge' => true,
                'display_order' => 20
            ),
            array(
                'title' => 'Exchange',
                'zero_value_text' => 'Exchange',
                'complete_text' => 'Completed',
                'badge_color' => 'rgb(150, 122, 220)',
                'show_badge' => true,
                'display_order' => 30
            )
        );

        foreach ($data as $dat)
        {
            /** @var KomuKuYJB_DataWriter_AdvertType $writer */
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_AdvertType', XenForo_DataWriter::ERROR_SILENT);
            $writer->setOption($writer::OPTION_REBUILD_CACHE, false);

            $writer->set('badge_color', $dat['badge_color']);
            $writer->set('complete_badge_color', $dat['badge_color']);
            $writer->set('show_badge', $dat['show_badge']);
            $writer->set('display_order', $dat['display_order']);

            $writer->setExtraData($writer::DATA_TITLE, $dat['title']);
            $writer->setExtraData($writer::DATA_ZERO_VALUE_TEXT, $dat['zero_value_text']);
            $writer->setExtraData($writer::DATA_COMPLETE_TEXT, $dat['complete_text']);

            $writer->save();
        }

        /** @var KomuKuYJB_Model_AdvertType $model */
        $model = XenForo_Model::create('KomuKuYJB_Model_AdvertType');
        $model->rebuildAdvertTypeCache();
    }
}