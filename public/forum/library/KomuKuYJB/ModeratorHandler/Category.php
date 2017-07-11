<?php /*f801c7e071e9f8f96fddca60d923150bae108e91*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ModeratorHandler_Category extends XenForo_ModeratorHandler_Abstract
{
    public function getModeratorInterfaceGroupIds()
    {
        return array('classifiedModeratorPermissions');
    }

    public function getAddModeratorOption(XenForo_View $view, $selectedContentId, $contentType)
    {
        $model = $this->_getCategoryModel();
        $categories = array_merge(array('0' => array('value' => 0, 'label' => '')), $model->getCategoryOptionArray($model->getAllCategories()));

        return array(
            'value' => $contentType,
            'label' => new XenForo_Phrase('classified_moderator') . ':',
            'disabled' => array(
                XenForo_Template_Helper_Admin::select("type_id[$contentType]", $selectedContentId, $categories)
            )
        );
    }

    public function getContentTitles(array $ids)
    {
        $categories = $this->_getCategoryModel()->getAllCategories();
        $titles = array();

        foreach ($ids as $k => $i)
        {
            if (isset($categories[$i]))
            {
                $category = $categories[$i];
                $titles[$k] = new XenForo_Phrase('classified_category') . " - $category[title]";
            }
        }

        return $titles;
    }

    /**
     * @return KomuKuYJB_Model_Category
     * @throws XenForo_Exception
     */
    protected function _getCategoryModel()
    {
        return XenForo_Model::create('KomuKuYJB_Model_Category');
    }
}