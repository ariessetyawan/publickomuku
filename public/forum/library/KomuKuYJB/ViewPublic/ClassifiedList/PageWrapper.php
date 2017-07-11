<?php /*0cc4d825707e49f29dea3dcacb1dca9ffd7eb87f*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ViewPublic_ClassifiedList_PageWrapper extends XenForo_ViewPublic_Base
{
    public function renderHtml()
    {
        $params = &$this->_params;

        if (!empty($params['groupedCategories']))
        {
            $params['categorySidebarHtml'] = $this->_getCategorySidebarHtml(0, $params['groupedCategories'], isset($params['selectedCategoryId']) ? $params['selectedCategoryId'] : 0);
        }
        else
        {
            $params['categorySidebarHtml'] = 'N/A';
        }

        // TODO: gonna have to find a work-around for this...
        if (!empty($params['extraSidebarBlocks']))
        {
            $params['extraSidebarBlocksHtml'] = array();

            foreach ($params['extraSidebarBlocks'] as $position => $blocks)
            {
                foreach ($blocks as $block)
                {
                    if ($block instanceof XenForo_ControllerResponse_View)
                    {
                        $params['extraSidebarBlocksHtml'][$position][] = $this->_renderer->renderSubView($block);
                    }
                }
            }
        }
    }

    protected function _getCategorySidebarHtml($thisId, $groupedCategories, $selectedCategoryId)
    {
        $params = array();

        if (isset($groupedCategories[$thisId]))
        {
            foreach ($groupedCategories[$thisId] as $category)
            {
                $template = $this->createTemplateObject('classifieds_category_sidebar_list_item');
                $template->setParam('category', $category);
                $template->setParam('selectedCategoryId', $selectedCategoryId);

                if (isset($groupedCategories[$category['category_id']]) && isset($this->_params['selectedCategories']) && in_array($category['category_id'], $this->_params['selectedCategories']))
                {
                    $template->setParam('children', $this->_getCategorySidebarHtml($category['category_id'], $groupedCategories, $selectedCategoryId));
                }

                $params['categories'][] = $template;
            }
        }

        return $this->createTemplateObject('classifieds_category_sidebar_list', $params);
    }
}