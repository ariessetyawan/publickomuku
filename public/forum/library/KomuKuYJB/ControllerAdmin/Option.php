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
class KomuKuYJB_ControllerAdmin_Option extends GFNCore_ControllerAdmin_SystemOption
{
    protected function _preDispatch($action)
    {
        $this->assertAdminPermission('option');
    }

    protected function _getOptionGroupId()
    {
        return 'KomuKuYJB';
    }

    protected function _getGroupedOptions()
    {
        return array(
            'basic' => array(
                'KomuKuYJB_navTabLocation', 'KomuKuYJB_defaultCurrency', 'KomuKuYJB_customCurrencyId',
                'KomuKuYJB_customCurrencyTitle', 'KomuKuYJB_defaultPackageId'
            ),

            'list' => array(
                'KomuKuYJB_sidebarLocation', 'KomuKuYJB_defaultListViewMode', 'KomuKuYJB_classifiedsPerPage'
            ),

            'classified' => array(
                'KomuKuYJB_tagLineRequired', 'KomuKuYJB_privateLocationByDefault', 'KomuKuYJB_disableAttachment',
                'KomuKuYJB_iconDimensions', 'KomuKuYJB_classifiedViewOtherClassifieds'
            ),

            'gallery' => array(
                'KomuKuYJB_galleryImageSize', 'KomuKuYJB_galleryImageCount', 'KomuKuYJB_galleryImageDimensions',
                'KomuKuYJB_gallerySlideDimensions', 'KomuKuYJB_galleryThumbnailDimensions'
            ),

            'comment' => array(
                'KomuKuYJB_maxCommentLength', 'KomuKuYJB_commentsPerPage'
            ),

            'thread' => array(
                'KomuKuYJB_showIconThreadList', 'KomuKuYJB_showPriceThreadList', 'KomuKuYJB_showPrefixThreadList',
                'KomuKuYJB_showTypeThreadList', 'KomuKuYJB_showAddButtonInForum', 'KomuKuYJB_classifiedDeleteThreadAction',
                'KomuKuYJB_classifiedCloseThreadAction', 'KomuKuYJB_classifiedCompleteThreadAction'
            ),

            'rating' => array(
                'KomuKuYJB_allowOutsideRating'
            )
        );
    }

    protected function _getRoutePrefixLink()
    {
        return 'classifieds/options';
    }
}