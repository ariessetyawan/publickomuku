<?php /*d2f7f87b8c43c20d2ece3d6ddaa119e5c47e1825*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_Option extends GFNCore_ControllerAdmin_SystemOption
{
    protected function _preDispatch($action)
    {
        $this->assertAdminPermission('option');
    }

    protected function _getOptionGroupId()
    {
        return 'gfnclassifieds';
    }

    protected function _getGroupedOptions()
    {
        return array(
            'basic' => array(
                'gfnclassifieds_navTabLocation', 'gfnclassifieds_defaultCurrency', 'gfnclassifieds_customCurrencyId',
                'gfnclassifieds_customCurrencyTitle', 'gfnclassifieds_defaultPackageId'
            ),

            'list' => array(
                'gfnclassifieds_sidebarLocation', 'gfnclassifieds_defaultListViewMode', 'gfnclassifieds_classifiedsPerPage'
            ),

            'classified' => array(
                'gfnclassifieds_tagLineRequired', 'gfnclassifieds_privateLocationByDefault', 'gfnclassifieds_disableAttachment',
                'gfnclassifieds_iconDimensions', 'gfnclassifieds_classifiedViewOtherClassifieds'
            ),

            'gallery' => array(
                'gfnclassifieds_galleryImageSize', 'gfnclassifieds_galleryImageCount', 'gfnclassifieds_galleryImageDimensions',
                'gfnclassifieds_gallerySlideDimensions', 'gfnclassifieds_galleryThumbnailDimensions'
            ),

            'comment' => array(
                'gfnclassifieds_maxCommentLength', 'gfnclassifieds_commentsPerPage'
            ),

            'thread' => array(
                'gfnclassifieds_showIconThreadList', 'gfnclassifieds_showPriceThreadList', 'gfnclassifieds_showPrefixThreadList',
                'gfnclassifieds_showTypeThreadList', 'gfnclassifieds_showAddButtonInForum', 'gfnclassifieds_classifiedDeleteThreadAction',
                'gfnclassifieds_classifiedCloseThreadAction', 'gfnclassifieds_classifiedCompleteThreadAction'
            ),

            'rating' => array(
                'gfnclassifieds_allowOutsideRating'
            )
        );
    }

    protected function _getRoutePrefixLink()
    {
        return 'classifieds/options';
    }
}