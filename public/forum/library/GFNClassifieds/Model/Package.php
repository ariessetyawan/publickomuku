<?php /*21970c6258fe35c9fa2f31ac66a1a01361c2d68c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_Package extends GFNClassifieds_Model
{
    const FETCH_CATEGORY = 0x01;

    public function getPackageById($packageId, array $fetchOptions = array())
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_package
            WHERE package_id = ?', $packageId
        );
    }

    public function getAllPackages(array $fetchOptions = array())
    {
        return $this->getPackages(array(), $fetchOptions);
    }

    public function getPackages(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->preparePackageConditions($conditions, $fetchOptions);
        $joinOptions = $this->preparePackageFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            "SELECT package.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_package AS package
            {$joinOptions['joinTables']}
            WHERE {$whereClause}
            ORDER BY display_order ASC", 'package_id'
        );
    }

    public function canAddClassified(array $package, $viewingUser = null)
    {

    }

    public function preparePackageFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $selectFields .= ', category_assoc.category_id';
                $joinTables .= ' INNER JOIN kmk_classifieds_package_category AS category_assoc
                                    ON (category_assoc.package_id = package.package_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables'   => $joinTables
        );
    }

    public function preparePackageConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (!empty($conditions['category_ids']))
        {
            $conditions['category_id'] = $conditions['category_ids'];
        }

        if (isset($conditions['category_id']))
        {
            if (is_array($conditions['category_id']))
            {
                $sqlConditions[] = 'category_assoc.category_id IN (' . $db->quote($conditions['category_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'category_assoc.category_id = ' . $db->quote($conditions['category_id']);
            }

            $this->addFetchOptionJoin($fetchOptions, self::FETCH_CATEGORY);
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function getPackagesInCategories($categoryIds)
    {
        if (!$categoryIds)
        {
            return array();
        }

        $db = $this->_getDb();

        return $db->fetchAll(
            'SELECT package.*, category_assoc.category_id
            FROM kmk_classifieds_package AS package
            INNER JOIN kmk_classifieds_package_category AS category_assoc
              ON (package.package_id = category_assoc.package_id)
            WHERE category_assoc.category_id IN (' . $db->quote($categoryIds) . ')
            ORDER BY package.display_order'
        );
    }

    public function preparePackage(array &$package)
    {
        $package['title'] = new XenForo_Phrase(
            $this->getPackageTitlePhraseName($package['package_id'])
        );

        $package['description'] = new XenForo_Phrase(
            $this->getPackageDescriptionPhraseName($package['package_id'])
        );

        if (!is_array($package['price_rate']))
        {
            $package['price_rate'] = XenForo_Helper_Php::safeUnserialize($package['price_rate']);
        }

        return $package;
    }

    public function preparePackages(array &$packages)
    {
        array_walk($packages, array($this, 'preparePackage')); return $packages;
    }

    public function getPackageTitlePhraseName($packageId)
    {
        return 'classifieds_package_' . $packageId . '_title';
    }

    public function getPackageDescriptionPhraseName($packageId)
    {
        return 'classifieds_package_' . $packageId . '_desc';
    }

    public function getPackageMasterTitlePhraseValue($packageId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getPackageTitlePhraseName($packageId)
        );
    }

    public function getPackageMasterDescriptionPhraseValue($packageId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getPackageDescriptionPhraseName($packageId)
        );
    }

    public function getPackageOption(array $selectedPackages = array(0), array $packages = null)
    {
        if ($packages === null)
        {
            $packages = $this->getAllPackages();
        }

        $options = array();

        foreach ($packages as $package)
        {
            $options[] = array(
                'value' => $package['package_id'],
                'label' => new XenForo_Phrase($this->getPackageTitlePhraseName($package['package_id'])),
                'selected' => in_array($package['package_id'], $selectedPackages)
            );
        }

        return $options;
    }

    public function getUsablePackagesInCategories($categoryIds, array $viewingUser = null, $verifyUsability = true)
    {
        $this->standardizeViewingUserReference($viewingUser);
        $packages = $this->getPackagesInCategories($categoryIds);
        $return = array();

        foreach ($packages as $package)
        {
            if ($package['active'] && (!$verifyUsability || $this->_verifyPrefixIsUsableInternal($package, $viewingUser)))
            {
                $return[] = $this->preparePackage($package);
            }
        }

        return $return;
    }

    protected function _verifyPrefixIsUsableInternal(array $package, array $viewingUser)
    {
        $this->standardizeViewingUserReference($viewingUser);
        return XenForo_Helper_Criteria::userMatchesCriteria($package['user_criteria'], true, $viewingUser);
    }

    public function verifyPackageIsUsable($packageId, $categoryId, array $viewingUser = null, array &$package = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        $package = $this->getPackageIfInCategory($packageId, $categoryId);
        if (!$package)
        {
            return false;
        }

        if (!$package['active'])
        {
            return false;
        }

        return $this->_verifyPrefixIsUsableInternal($package, $viewingUser);
    }

    public function getPackageIfInCategory($packageId, $categoryId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT package.*
            FROM kmk_classifieds_package AS package
            INNER JOIN kmk_classifieds_package_category AS assoc
            ON (assoc.package_id = package.package_id AND assoc.category_id = ?)
            WHERE package.package_id = ?', array($categoryId, $packageId)
        );
    }

    /**
     * @return string
     * @deprecated
     */
    public function getDefaultCurrency()
    {
        $options = GFNClassifieds_Options::getInstance();

        if ($options->get('customCurrencyId'))
        {
            return $options->get('customCurrencyId');
        }

        return $options->get('defaultCurrency');
    }
}