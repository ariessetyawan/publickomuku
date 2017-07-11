<?php /*c0df6f897d44be4261cac2bd48d24ecc72cf99e2*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000051 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->table()->drop('kmk_classifieds_review');

            $this->table()->alter('kmk_classifieds_category', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->mediumBlob('rating_criteria_cache')->after('field_cache');
            });

            $this->table()->alter('kmk_classifieds_classified', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->decimal('price', 14, 2);
                $table->integer('expire_date')->unsigned(true)->default(0);

                $table->integer('complete_date')->unsigned(true)->default(0)->after('expire_date');
                $table->integer('complete_user_id')->unsigned(true)->default(0)->after('last_comment_username');
                $table->string('complete_username', 100)->default('')->after('complete_user_id');
                $table->integer('featured_image_attachment_id')->unsigned(true)->default(0)->after('featured_image_date');

                $table->index('last_bump_date');
            });

            $this->_removeRedundantContentTypes();
        }

        $this->table()->create('kmk_classifieds_rating_feedback', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('feedback_id')->unsigned(true)->autoIncrement(true);
            $table->integer('classified_id')->unsigned(true)->default(0);
            $table->integer('user_id')->unsigned(true);
            $table->string('username', 100);
            $table->integer('for_user_id')->unsigned(true);
            $table->string('for_username', 100);
            $table->tinyInteger('rating', 1);
            $table->integer('parent_feedback_id')->unsigned(true)->default(0);
            $table->mediumText('message');
            $table->integer('ip_id')->unsigned(true)->default(0);
            $table->integer('warning_id')->unsigned(true)->default(0);
            $table->string('warning_message', 255)->default('');
            $table->integer('feedback_date')->unsigned(true)->default(0);
            $table->enum('feedback_state', array('visible', 'moderated', 'deleted'))->default('visible');
            $table->integer('likes')->unsigned(true)->default(0);
            $table->blob('like_users');
            $table->boolean('had_first_visible')->default(0);
            $table->blob('criteria_feedbacks');

            $table->primary('feedback_id');
            $table->index('classified_id');
            $table->index('user_id');
            $table->index('for_user_id');
            $table->index('feedback_date');
        });

        $this->table()->create('kmk_classifieds_rating_criteria', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->binary('criteria_id', 25);
            $table->integer('display_order')->unsigned(true)->default(0);
            $table->boolean('required')->default(0);
            $table->boolean('is_global')->default(0);
            $table->boolean('show_message')->default(1);
            $table->boolean('require_message')->default(0);

            $table->primary('criteria_id');
            $table->index('display_order');
        });

        $this->table()->create('kmk_classifieds_rating_criteria_feedback', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('feedback_id')->unsigned(true);
            $table->binary('criteria_id', 25);
            $table->tinyInteger('rating', 1);
            $table->mediumText('message');

            $table->primary(array('feedback_id', 'criteria_id'));
            $table->index('criteria_id');
        });

        $this->table()->create('kmk_classifieds_rating_criteria_category', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->binary('criteria_id', 25);
            $table->integer('category_id')->unsigned(true);

            $table->primary(array('criteria_id', 'category_id'));
            $table->index('criteria_id');
        });

        $this->table()->create('kmk_classifieds_trader', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('user_id')->unsigned(true);
            $table->integer('classified_count')->default(0)->unsigned(true);
            $table->integer('rating_count')->default(0)->unsigned(true);
            $table->integer('rating_positive_count')->default(0)->unsigned(true);
            $table->integer('rating_neutral_count')->default(0)->unsigned(true);
            $table->integer('rating_negative_count')->default(0)->unsigned(true);
            $table->decimal('rating_avg', 3)->default(0)->unsigned(true);
            $table->decimal('rating_weighted', 3)->default(0)->unsigned(true);
            $table->integer('response_time')->default(0)->unsigned(true);
            $table->decimal('response_percentage', 5)->default(0);

            $table->primary('user_id');
            $table->index('classified_count');
            $table->index('rating_count');
            $table->index('rating_weighted');
        });

        $this->table()->create('kmk_classifieds_trader_field', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->binary('field_id', 25);
            $table->string('display_group', 25)->default('above_info');
            $table->integer('display_order')->unsigned(true)->default(0);
            $table->string('field_type', 25)->default('textbox');
            $table->blob('field_choices');
            $table->boolean('required')->default(0);
            $table->string('match_type', 25)->default('none');
            $table->string('match_regex', 250)->default('');
            $table->string('match_callback_class', 75)->default('');
            $table->string('match_callback_method', 75)->default('');
            $table->integer('max_length')->unsigned(true)->default(0);
            $table->text('display_template');
            $table->boolean('show_registration')->default(0);
            $table->boolean('moderator_editable')->default(0);
            $table->enum('user_editable', array('yes', 'once', 'never'))->default('yes');
            $table->boolean('viewable_member_profile')->default(0);
            $table->boolean('viewable_advertiser_profile')->default(1);
            $table->boolean('viewable_message')->default(0);
            $table->boolean('viewable_classified')->default(1);

            $table->primary('field_id');
            $table->index(array('display_group', 'display_order'), 'display_group_order');
        });

        $this->table()->create('kmk_classifieds_trader_field_value', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->integer('user_id')->unsigned(true);
            $table->binary('field_id', 25);
            $table->mediumText('field_value');

            $table->primary(array('user_id', 'field_id'));
            $table->index('field_id');
        });

        /*XenForo_Application::defer(
            'KomuKuYJB_Deferred_PostInstall_1000051',
            array(
                'isUpgrade' => $isUpgrade
            ), 'KomuKuYJB-1000051', true
        );*/

        $this->_insertContentTypes();
        $this->_updatePermissions();

        $this->rebuild();
    }

    public function rebuild()
    {
        @set_time_limit(0);
        $db = $this->db();
        $position = 0;

        do
        {
            $userIds = $db->fetchCol('SELECT user_id FROM kmk_user WHERE user_id > ? ORDER BY user_id ASC LIMIT 10000', $position);

            foreach ($userIds as $userId)
            {
                $db->query('INSERT IGNORE INTO kmk_classifieds_trader (user_id) VALUES (?)', $userId);
                $position = $userId;
            }
        }
        while (!empty($userIds));

        try
        {
            $this->table()->alter('kmk_user', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->drop('classified_count');
                $table->drop('classified_rating_count');
                $table->drop('classified_rating_sum');
                $table->drop('classified_rating_avg');
                $table->drop('classified_rating_weighted');
            });
        }
        catch (Exception $e) { }

        /** @var KomuKuYJB_Model_Classified $model */
        $model = XenForo_Model::create('KomuKuYJB_Model_Classified');
        $model->rebuildCount();
    }

    public function uninstall()
    {
        $this->table()->drop('kmk_classifieds_rating_feedback');
        $this->table()->drop('kmk_classifieds_rating_criteria');
        $this->table()->drop('kmk_classifieds_rating_criteria_feedback');
        $this->table()->drop('kmk_classifieds_rating_criteria_category');
        $this->table()->drop('kmk_classifieds_trader');
        $this->table()->drop('kmk_classifieds_trader_field');
        $this->table()->drop('kmk_classifieds_trader_field_value');
    }

    protected function _removeRedundantContentTypes()
    {
        $handler = new GFNCore_Installer_Handler_ContentType('KomuKuYJB');
        $handler->delete('classified_rating');
    }

    protected function _insertContentTypes()
    {
        $handler = new GFNCore_Installer_Handler_ContentType('KomuKuYJB');

        // Rating Feedback...
        $handler->add('classified_trader_rating', $handler::ALERT_HANDLER, 'KomuKuYJB_AlertHandler_TraderRating');
        $handler->add('classified_trader_rating', $handler::LIKE_HANDLER, 'KomuKuYJB_LikeHandler_TraderRating');
        $handler->add('classified_trader_rating', $handler::MODERATION_QUEUE_HANDLER, 'KomuKuYJB_ModerationQueueHandler_TraderRating');
        $handler->add('classified_trader_rating', $handler::MODERATOR_LOG_HANDLER, 'KomuKuYJB_ModeratorLogHandler_TraderRating');
        $handler->add('classified_trader_rating', $handler::NEWS_FEED_HANDLER, 'KomuKuYJB_NewsFeedHandler_TraderRating');
        $handler->add('classified_trader_rating', $handler::REPORT_HANDLER, 'KomuKuYJB_ReportHandler_TraderRating');
        $handler->add('classified_trader_rating', $handler::SPAM_HANDLER, 'KomuKuYJB_SpamHandler_TraderRating');
        $handler->add('classified_trader_rating', $handler::WARNING_HANDLER, 'KomuKuYJB_WarningHandler_TraderRating');
    }

    protected function _updatePermissions()
    {
        $handler = new GFNCore_Installer_Handler_Permission();

        // Classified Moderator Permissions...
        $handler->applyGlobalPermission('classified', 'activateAny', 'classifieds', 'openCloseAny', true);

        // General Permissions...
        $handler->applyGlobalPermission('classifiedTraderRating', 'view', 'classifieds', 'view');
        $handler->applyGlobalPermission('classifiedTraderRating', 'like', 'classifieds', 'like');
        $handler->applyGlobalPermission('classifiedTraderRating', 'add', 'classifieds', 'add');
        $handler->applyGlobalPermission('classifiedTraderRating', 'respond', 'classifieds', 'add');
        $handler->applyGlobalPermission('classifiedTraderRating', 'edit', 'classifieds', 'editSelf');
        $handler->applyGlobalPermission('classifiedTraderRating', 'delete', 'classifieds', 'deleteSelf');
        $handler->applyGlobalPermission('classifiedTraderRating', 'editOwnTime', 'classifieds', 'editOwnClassifiedTime');

        // Moderator Permissions...
        $handler->applyGlobalPermission('classifiedTraderRating', 'viewDeleted', 'classifieds', 'viewDeleted', true);
        $handler->applyGlobalPermission('classifiedTraderRating', 'viewModerated', 'classifieds', 'viewModerated', true);
        $handler->applyGlobalPermission('classifiedTraderRating', 'editAny', 'classifieds', 'editAny', true);
        $handler->applyGlobalPermission('classifiedTraderRating', 'deleteAny', 'classifieds', 'deleteAny', true);
        $handler->applyGlobalPermission('classifiedTraderRating', 'hardDeleteAny', 'classifieds', 'hardDeleteAny', true);
        $handler->applyGlobalPermission('classifiedTraderRating', 'undelete', 'classifieds', 'undelete', true);
        $handler->applyGlobalPermission('classifiedTraderRating', 'warn', 'classifieds', 'warn', true);
        $handler->applyGlobalPermission('classifiedTraderRating', 'approveUnapprove', 'classifieds', 'approveUnapprove', true);
    }
}