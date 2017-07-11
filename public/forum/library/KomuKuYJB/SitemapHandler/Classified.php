<?php /*a5cb10b58b70bdb156c8bd4f65b3fb9288551b42*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_SitemapHandler_Classified extends XenForo_SitemapHandler_Abstract
{
    /**
     * @var KomuKuYJB_Model_Classified
     */
    protected $_classifiedModel;

    public function getRecords($previousLast, $limit, array $viewingUser)
    {
        $classifiedModel = $this->_getClassifiedModel();
        $classifiedIds = $classifiedModel->getClassifiedIdsInRange($previousLast, $limit);

        $classifieds = $classifiedModel->getClassifiedsByIds($classifiedIds, array(
            'join' => $classifiedModel::FETCH_CATEGORY,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        ksort($classifieds);

        return $classifiedModel->unserializePermissionsInList($classifieds, 'category_permission_cache');
    }

    /**
     * Determine if a particular record should be included in the sitemap.
     *
     * @param array $entry
     * @param array $viewingUser
     *
     * @return boolean
     */
    public function isIncluded(array $entry, array $viewingUser)
    {
        return $this->_getClassifiedModel()->canViewClassifiedAndContainer(
            $entry, $entry, $null, $viewingUser, $entry['permissions']
        );
    }

    /**
     * Gets the sitemap data for an entry. Can either return an array with keys:
     *    loc (required, canonical URL), lastmod (unix timestamp, last modification time),
     *    priority (0.0-1.0, higher value more important), changefreq (daily/weekly/etc value),
     *  image (array with sub-options for image sitemap)
     * Or may return an array of multiple such arrays.
     *
     * @param array $entry
     *
     * @return array
     */
    public function getData(array $entry)
    {
        $entry['title'] = XenForo_Helper_String::censorString($entry['title']);

        return array(
            'loc' => XenForo_Link::buildPublicLink('canonical:classifieds', $entry),
            'lastmod' => $entry['last_update']
        );
    }

    /**
     * Should return true if the process can be interrupted at any record and
     * picked up from there in another request. Types with potentially
     * large amounts of content must allow this to be true.
     *
     * @return boolean
     */
    public function isInterruptable()
    {
        return true;
    }

    protected function _getClassifiedModel()
    {
        if (!$this->_classifiedModel)
        {
            $this->_classifiedModel = XenForo_Model::create('KomuKuYJB_Model_Classified');
        }

        return $this->_classifiedModel;
    }
}