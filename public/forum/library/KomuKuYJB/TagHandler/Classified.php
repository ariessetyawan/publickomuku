<?php /*a3949d7845e7109134a92f023628c79ed8d9e8dc*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_TagHandler_Classified extends XenForo_TagHandler_Abstract
{
    /**
     * @var KomuKuYJB_Model_Classified
     */
    protected $_classifiedModel;

    public function getPermissionsFromContext(array $context, array $parentContext = null)
    {
        if (isset($context['classified_id']))
        {
            $classified = $context;
            $category = $parentContext;
        }
        else
        {
            $classified = null;
            $category = $context;
        }

        if (empty($category['category_id']))
        {
            throw new Exception("Context must be a classified and a category or just a category");
        }

        $visitor = XenForo_Visitor::getInstance();
        $model = $this->_getClassifiedModel();

        $categoryPermissions = $model->getCategoryModel()->getCategoryPermCache(
            $visitor['permission_combination_id'], $category['category_id']
        );

        if ($classified)
        {
            if ($classified['user_id'] == $visitor['user_id'] && XenForo_Permission::hasContentPermission($categoryPermissions, 'manageOthersTagsOwnClass'))
            {
                $removeOthers = true;
            }
            else
            {
                $removeOthers = XenForo_Permission::hasContentPermission($categoryPermissions, 'manageAnyTag');
            }
        }
        else
        {
            $removeOthers = false;
        }

        return array(
            'edit' => $this->_getClassifiedModel()->canEditTags($classified, $category),
            'removeOthers' => $removeOthers,
            'minTotal' => 0
        );
    }

    public function getBasicContent($id)
    {
        return $this->_getClassifiedModel()->getClassifiedById($id);
    }

    public function getContentDate(array $content)
    {
        return $content['classified_date'];
    }

    public function getContentVisibility(array $content)
    {
        return in_array($content['classified_state'], array('visible', 'completed', 'closed'));
    }

    public function updateContentTagCache(array $content, array $cache)
    {
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        $writer->setExistingData($content['classified_id']);
        $writer->set('tags', $cache);
        $writer->save();
    }

    public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
    {
        $model = $this->_getClassifiedModel();

        $classifieds = $model->getClassifiedsByIds($ids, array(
            'join' => KomuKuYJB_Model_Classified::FETCH_CATEGORY
                | KomuKuYJB_Model_Classified::FETCH_USER
                | KomuKuYJB_Model_Classified::FETCH_LOCATION,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        return $model->unserializePermissionsInList($classifieds, 'category_permission_cache');
    }

    public function canViewResult(array $result, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canViewClassifiedAndContainer(
            $result, $result, $null, $viewingUser, $result['permissions']
        );
    }

    public function prepareResult(array $result, array $viewingUser)
    {
        return $this->_getClassifiedModel()->prepareClassified(
            $result, $result, $viewingUser, $result['permissions']
        );
    }

    public function renderResult(XenForo_View $view, array $result)
    {
        return $view->createTemplateObject('search_result_classified', array(
            'classified' => $result
        ));
    }

    /**
     * @return KomuKuYJB_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        if (!$this->_classifiedModel)
        {
            $this->_classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');
        }

        return $this->_classifiedModel;
    }
}