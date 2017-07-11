<?php /*425a4769ed055068f42046cda6060eec98143494*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerPublic_CategoryWatch extends GFNClassifieds_ControllerPublic_Abstract
{
    protected function _preDispatch($action)
    {
        $this->_assertRegistrationRequired();
    }

    public function actionIndex()
    {
        $categoryModel = $this->models()->category();
        $watchModel = $this->models()->categoryWatch();
        $visitor = XenForo_Visitor::getInstance();

        $categoriesWatched = $watchModel->getUserCategoryWatchByUser($visitor['user_id']);

        if ($categoriesWatched)
        {
            $viewableCategories = $categoryModel->getViewableCategories();
            $categoryList = $categoryModel->groupCategoriesByParent($viewableCategories);
            $categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);

            $categories = $categoryModel->ungroupCategories($categoryList, array_keys($categoriesWatched));
        }
        else
        {
            $categories = array();
        }

        $viewParams = array(
            'categories' => $categories,
            'categoriesWatched' => $categoriesWatched
        );

        return $this->responseView('GFNClassifieds_ViewPublic_WatchedCategories', 'classifieds_watched_categories', $viewParams);
    }

    public function actionUpdate()
    {
        $this->_assertPostOnly();

        $input = $this->_input->filter(array(
            'category_ids' => array(XenForo_Input::UINT, 'array' => true),
            'do' => XenForo_Input::STRING
        ));

        $watch = $this->models()->categoryWatch()->getUserCategoryWatchByCategoryIds(XenForo_Visitor::getUserId(), $input['category_ids']);

        foreach ($watch AS $categoryWatch)
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_CategoryWatch');
            $writer->setExistingData($categoryWatch, true);

            switch ($input['do'])
            {
                case 'stop':
                    $writer->delete();
                    break;

                case 'email':
                    $writer->set('send_email', 1);
                    $writer->save();
                    break;

                case 'no_email':
                    $writer->set('send_email', 0);
                    $writer->save();
                    break;

                case 'alert':
                    $writer->set('send_alert', 1);
                    $writer->save();
                    break;

                case 'no_alert':
                    $writer->set('send_alert', 0);
                    $writer->save();
                    break;

                case 'include_children':
                    $writer->set('include_children', 1);
                    $writer->save();
                    break;

                case 'no_include_children':
                    $writer->set('include_children', 0);
                    $writer->save();
                    break;
            }
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->getDynamicRedirect($this->_buildLink('classifieds/watched/categories'))
        );
    }
}