<?php /*4d67c11e999f64a3692b04a7e29f8d800116ce2c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Option_DefaultPackage
{
    public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $options = array(
            array(
                'value' => 0,
                'label' => ''
            )
        );

        /** @var GFNClassifieds_Model_Package $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_Package');

        foreach ($model->getAllPackages() as $package)
        {
            $options[] = array(
                'value' => $package['package_id'],
                'label' => $model->getPackageMasterTitlePhraseValue($package['package_id']),
                'selected' => $package['package_id'] == (isset($preparedOption['option_value']) ? $preparedOption['option_value'] : '')
            );
        }

        $preparedOption['formatParams'] = $options;

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            'option_list_option_select', $view, $fieldPrefix, $preparedOption, $canEdit
        );
    }
}