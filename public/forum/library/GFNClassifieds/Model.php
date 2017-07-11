<?php /*450d31159d27525230b59124548afa5e815c870f*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class GFNClassifieds_Model extends XenForo_Model
{
    /**
     * @return XenForo_Model_Phrase
     */
    protected function _getPhraseModel()
    {
        return $this->getModelFromCache('XenForo_Model_Phrase');
    }

    protected function _getContentId($content, $primaryKey)
    {
        if (!is_array($content))
        {
            return $content;
        }

        if (isset($content[$primaryKey]))
        {
            return $content[$primaryKey];
        }

        return null;
    }
}