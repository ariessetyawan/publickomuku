<?php /*ed9fdbd242dbd1a00c9eb1c598c7feb38b4e9cf1*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_WarningHandler_TraderRating extends XenForo_WarningHandler_Abstract
{
    protected function _canView(array $content, array $viewingUser)
    {
        return $this->_getTraderRatingModel()->canViewTraderRating($content, $null, $viewingUser);
    }

    protected function _canWarn($userId, array $content, array $viewingUser)
    {
        return $this->_getTraderRatingModel()->canWarnTraderRating($content, $null, $viewingUser);
    }

    protected function _canDeleteContent(array $content, array $viewingUser)
    {
        return $this->_getTraderRatingModel()->canDeleteTraderRating($content, 'soft', $null, $viewingUser);
    }

    protected function _getContent(array $contentIds, array $viewingUser)
    {
        $model = $this->_getTraderRatingModel();

        return $model->getTraderRatingsByIds($contentIds, array(
            'join' => $model::FETCH_USER
        ));
    }

    public function getContentTitle(array $content)
    {
        new XenForo_Phrase('trader_rating_by_x_for_y', array('by' => $content['username'], 'for' => $content['for_username']));
    }

    public function getContentDetails(array $content)
    {
        return $content['message'];
    }

    public function getContentUrl(array $content, $canonical = false)
    {
        return XenForo_Link::buildPublicLink(($canonical ? 'canonical:' : '') . 'classifieds/traders/ratings', $content);
    }

    protected function _warn(array $warning, array $content, $publicMessage, array $viewingUser)
    {
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);

        if ($writer->setExistingData($content))
        {
            $writer->set('warning_id', $warning['warning_id']);
            $writer->set('warning_message', $publicMessage);
            $writer->save();
        }
    }

    protected function _reverseWarning(array $warning, array $content)
    {
        if ($content)
        {
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);

            if ($writer->setExistingData($content))
            {
                $writer->set('warning_id', 0);
                $writer->set('warning_message', '');
                $writer->save();
            }
        }
    }

    protected function _deleteContent(array $content, $reason, array $viewingUser)
    {
        $this->_getTraderRatingModel()->deleteTraderRating($content['feedback_id'], 'soft', array('reason' => $reason));
        XenForo_Model_Log::logModeratorAction('classified_trader_rating', $content, 'delete_soft', array('reason' => $reason));
        XenForo_Helper_Cookie::clearIdFromCookie($content['feedback_id'], 'inlinemod_classified_trader_ratings');
    }

    /**
     * @return GFNClassifieds_Model_TraderRating
     */
    protected function _getTraderRatingModel()
    {
        static $model = null;

        if ($model === null)
        {
            $model = XenForo_Model::create('GFNClassifieds_Model_TraderRating');
        }

        return $model;
    }
}