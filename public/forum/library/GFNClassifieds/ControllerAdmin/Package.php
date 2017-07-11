<?php /*07d876a4362682bfff034c7662b5c71d481930d2*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_Package extends GFNClassifieds_ControllerAdmin_Abstract
{
    protected function _getAdminPermission()
    {
        return 'classifiedPackage';
    }

    public function actionList()
    {
        $model = $this->models()->package();
        $packages = $model->getAllPackages();
        $model->preparePackages($packages);

        $viewParams = array(
            'packages' => $packages
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_Package_List', 'classifieds_package_list', $viewParams);
    }

    public function actionToggle()
    {
        $this->_assertPostOnly();

        return $this->_getToggleResponse(
            $this->models()->package()->getAllPackages(),
            'GFNClassifieds_DataWriter_Package',
            $this->_buildLink('classifieds/packages')
        );
    }

    public function actionAdd()
    {
        return $this->_getAddEditResponse(array(
            'package_id' => null,
            'advert_duration' => 1,
            'max_renewal' => 0,
            'active' => true,
            'always_moderate_create' => false,
            'always_moderate_update' => false,
            'always_moderate_renewal' => false,
            'auto_feature_item' => false,
            'display_order' => 1,
            'pricing_format' => 'flat',
            'pricing_rate' => array(
                'purchase' => '',
                'renewal' => ''
            ),
            'user_criteria' => array()
        ));
    }

    public function actionEdit()
    {
        return $this->_getAddEditResponse($this->_getPackageOrError());
    }

    protected function _getAddEditResponse(array $package, array $viewParams = array())
    {
        $model = $this->models()->package();

        if (empty($package['package_id']))
        {
            $masterTitle = '';
            $masterDescription = '';
            $selCategoryIds = array();
        }
        else
        {
            $selCategoryIds = $this->models()->association()->package()->getAssociationByPackage($package['package_id']);
            $masterTitle = $model->getPackageMasterTitlePhraseValue($package['package_id']);
            $masterDescription = $model->getPackageMasterDescriptionPhraseValue($package['package_id']);
            $package['price_rate'] = XenForo_Helper_Php::safeUnserialize($package['price_rate']);
        }

        $viewParams += array(
            'package' => $package,
            'masterTitle' => $masterTitle,
            'masterDescription' => $masterDescription,

            'userCriteria' => XenForo_Helper_Criteria::prepareCriteriaForSelection($package['user_criteria']),
            'userCriteriaData' => XenForo_Helper_Criteria::getDataForUserCriteriaSelection(),

            'selCategoryIds' => $selCategoryIds,
            'categories' => $this->models()->category()->getAllCategories(),

            'baseCurrency' => $model->getDefaultCurrency() //TODO
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_Package_Edit', 'classifieds_package_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $data = $this->_input->filter(array(
            'advert_duration' => XenForo_Input::UINT,
            'max_renewal' => XenForo_Input::INT,
            'auto_feature_item' => XenForo_Input::BOOLEAN,
            'always_moderate_create' => XenForo_Input::BOOLEAN,
            'always_moderate_update' => XenForo_Input::BOOLEAN,
            'always_moderate_renewal' => XenForo_Input::BOOLEAN,
            'active' => XenForo_Input::BOOLEAN,
            'display_order' => XenForo_Input::UINT,
            'price_format' => XenForo_Input::STRING,
            'user_criteria' => XenForo_Input::ARRAY_SIMPLE
        ));

        $extra = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'description' => array(XenForo_Input::STRING, 'noTrim' => true),
            'applicable_categories' => array(XenForo_Input::UINT, 'array' => true)
        ));

        /** @var GFNClassifieds_DataWriter_Package $writer */
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Package');

        if ($existing = $this->_input->filterSingle('package_id', XenForo_Input::UINT))
        {
            $writer->setExistingData($existing);
        }

        switch ($data['price_format'])
        {
            case 'flat':
            case 'percentile':
                $rate = $this->_input->filterSingle('rate', XenForo_Input::FLOAT, array('array' => true));
                break;

            case 'flexible':
                $input = new XenForo_Input($this->_input->filterSingle('rate', XenForo_Input::ARRAY_SIMPLE));
                $rate = array();

                $value = $input->filterSingle('item_price', XenForo_Input::FLOAT, array('array' => true));
                $purchase = $input->filterSingle('listing', XenForo_Input::FLOAT, array('array' => true));
                $renewal = $input->filterSingle('renewal', XenForo_Input::FLOAT, array('array' => true));

                foreach ($value as $i => $v)
                {
                    if (isset($purchase[$i], $renewal[$i]))
                    {
                        $rate[] = array(
                            'item_price' => $v,
                            'listing' => $purchase[$i],
                            'renewal' => $renewal[$i]
                        );
                    }
                }
                break;

            default:
                return $this->responseError(new XenForo_Phrase('invalid_price_format_specified'), 400);
        }

        $writer->bulkSet($data);
        $writer->setPricingRate($rate);
        $writer->setExtraData($writer::DATA_TITLE, $extra['title']);
        $writer->setExtraData($writer::DATA_DESCRIPTION, $extra['description']);
        $writer->setExtraData($writer::DATA_CATEGORIES, $extra['applicable_categories']);
        $writer->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->_buildLink('classifieds/packages') . $this->getLastHash($writer->get('package_id'))
        );
    }

    public function actionDelete()
    {
        $package = $this->_getPackageOrError();

        if ($this->isConfirmedPost())
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Package');
            $writer->setExistingData($package, true);
            $writer->delete();

            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                $this->_buildLink('classifieds/packages')
            );
        }

        $viewParams = array(
            'package' => $this->models()->package()->preparePackage($package)
        );

        return $this->responseView('GFNClassifieds_ViewAdmin_Package_Delete', 'classifieds_package_delete', $viewParams);
    }

    protected function _getPackageOrError($packageId = null)
    {
        if ($packageId === null)
        {
            $packageId = $this->_input->filterSingle('package_id', XenForo_Input::UINT);
        }

        $package = $this->models()->package()->getPackageById($packageId);
        if (!$package)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_package_not_found');
        }

        return $package;
    }
} 