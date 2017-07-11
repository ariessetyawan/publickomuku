<?php /*9db16d71485aa5f7a4ecd1838cf48e3eb9b0d087*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_Package extends XenForo_DataWriter
{
    const DATA_TITLE = 'phraseTitle';
    const DATA_DESCRIPTION = 'phraseDescription';
    const DATA_CATEGORIES = 'categories';

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_package' => array(
                'package_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 1
                ),
                'advert_duration' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'max_renewal' => array(
                    'type' => self::TYPE_INT,
                    'required' => true
                ),
                'auto_feature_item' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'active' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'always_moderate_create' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => false
                ),
                'always_moderate_update' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => false
                ),
                'always_moderate_renewal' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => false
                ),
                'price_format' => array(
                    'type' => self::TYPE_STRING,
                    'default' => 'flat'
                ),
                'price_rate' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:2:{s:8:"purchase";s:4:"0.00";s:7:"renewal";s:4:"0.00";}'
                ),
                'user_criteria' => array(
                    'type' => self::TYPE_UNKNOWN,
                    'required' => true,
                    'verification' => array('$this', '_validateCriteria')
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $packageId = $this->_getExistingPrimaryKey($data);
        $package = $this->_getPackageModel()->getPackageById($packageId);

        if (!$package)
        {
            return false;
        }

        return array('kmk_classifieds_package' => $package);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'package_id = ' . $this->_db->quote($this->getExisting('package_id'));
    }

    public function _preSave()
    {
        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null && strlen($titlePhrase) == 0)
        {
            $this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
        }

        $descriptionPhrase = $this->getExtraData(self::DATA_DESCRIPTION);
        if ($descriptionPhrase !== null && strlen($descriptionPhrase) == 0)
        {
            $this->setExtraData(self::DATA_DESCRIPTION, false);
        }

        if ($this->get('max_renewal') < 0)
        {
            $this->set('max_renewal', -1);
        }
    }

    protected function _postSave()
    {
        $packageId = $this->get('package_id');

        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getTitlePhraseName($packageId), $titlePhrase,
                '', array('global_cache' => 1)
            );
        }

        $descriptionPhrase = $this->getExtraData(self::DATA_DESCRIPTION);
        if ($descriptionPhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getDescriptionPhraseName($packageId), $descriptionPhrase
            );
        }

        $categoryIds = $this->getExtraData(self::DATA_CATEGORIES);
        if (is_array($categoryIds))
        {
            $this->_getAssociationModel()->package()->updateAssociationByPackage($packageId, $categoryIds);
        }
    }

    protected function _postDelete()
    {
        $packageId = $this->get('package_id');

        $this->_deleteMasterPhrase($this->_getTitlePhraseName($packageId));
        $this->_deleteMasterPhrase($this->_getDescriptionPhraseName($packageId));

        $this->_getAssociationModel()->package()->removeAssociationByPackage($packageId);
    }

    public function setPricingRate(array $rate)
    {
        switch ($this->get('price_format'))
        {
            case 'flat':
            case 'percentile':
                $this->set('price_rate', array(
                    'listing' => sprintf('%.2f', $rate['listing']),
                    'renewal' => sprintf('%.2f', $rate['renewal'])
                ));
                break;

            case 'flexible':
                $new = array();

                foreach ($rate as $i => $v)
                {
                    if (empty($v['item_price']) && empty($v['listing']) && empty($v['renewal']))
                    {
                        continue;
                    }

                    $new[strval($v['item_price'])] = array(
                        'item_price' => sprintf('%.2f', $v['item_price']),
                        'listing' => sprintf('%.2f', $v['listing']),
                        'renewal' => sprintf('%.2f', $v['renewal'])
                    );
                }

                ksort($new, SORT_NUMERIC);
                $this->set('price_rate', array_values($new));
                break;
        }

        return true;
    }

    /**
     * @return GFNClassifieds_Model_Package
     */
    protected function _getPackageModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Package');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation
     */
    protected function _getAssociationModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation');
    }

    protected function _getTitlePhraseName($packageId)
    {
        return $this->_getPackageModel()->getPackageTitlePhraseName($packageId);
    }

    protected function _getDescriptionPhraseName($packageId)
    {
        return $this->_getPackageModel()->getPackageDescriptionPhraseName($packageId);
    }

    protected function _validateAllowedUserGroupIds(&$data)
    {
        if (!is_array($data))
        {
            $data = preg_split('#,\s*#', $data);
        }

        $data = array_map('intval', $data);
        $data = array_unique($data);
        sort($data, SORT_NUMERIC);
        $data = implode(',', $data);
        return true;
    }

    protected function _validateCriteria(&$criteria)
    {
        $criteriaFiltered = XenForo_Helper_Criteria::prepareCriteriaForSave($criteria);
        $criteria = XenForo_Helper_Php::safeSerialize($criteriaFiltered);
        return true;
    }
} 