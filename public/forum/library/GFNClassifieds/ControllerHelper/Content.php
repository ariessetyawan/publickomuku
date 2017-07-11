<?php /*2e2a8089f062d0a6d8455600ac559015d14895ec*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerHelper_Content extends XenForo_ControllerHelper_Abstract
{
    /**
     * @var XenForo_Visitor
     */
    protected $_visitor;

    /**
     * @var GFNClassifieds_ControllerHelper_Model
     */
    protected $_models;

    protected function _constructSetup()
    {
        $this->_visitor = XenForo_Visitor::getInstance();
        $this->_models = $this->_controller->getHelper('GFNClassifieds_ControllerHelper_Model');
    }

    public function getCategoryOrError($categoryId, array $fetchOptions = array())
    {
        if ($categoryId === null)
        {
            $categoryId = $this->_getContentId('category_id');
        }

        $category = $this->_models->category()->getCategoryById($categoryId, $fetchOptions);
        if (!$category)
        {
            throw $this->getNotFoundResponseException('requested_category_not_found');
        }

        return $this->_models->category()->prepareCategory($category);
    }

    public function getClassifiedOrError($classifiedId = null, array $fetchOptions = array())
    {
        if ($classifiedId === null)
        {
            $classifiedId = $this->_getContentId('classified_id');
        }

        $classified = $this->_models->classified()->getClassifiedById($classifiedId, $fetchOptions);
        if (!$classified)
        {
            throw $this->getNotFoundResponseException('requested_classified_not_found');
        }

        return $classified;
    }

    public function getCommentOrError($commentId = null, array $fetchOptions = array())
    {
        if ($commentId === null)
        {
            $commentId = $this->_getContentId('comment_id');
        }

        $comment = $this->_models->comment()->getCommentById($commentId, $fetchOptions);
        if (!$comment)
        {
            throw $this->getNotFoundResponseException('requested_comment_not_found');
        }

        return $comment;
    }

    public function getTraderRatingOrError($feedbackId = null, array $fetchOptions = array())
    {
        if ($feedbackId === null)
        {
            $feedbackId = $this->_getContentId('feedback_id');
        }

        $rating = $this->_models->traderRating()->getTraderRatingById($feedbackId, $fetchOptions);
        if (!$rating)
        {
            throw $this->getNotFoundResponseException('requested_trader_rating_not_found');
        }

        return $rating;
    }

    public function getTraderOrError($userId = null, array $fetchOptions = array())
    {
        if ($userId === null)
        {
            $userId = $this->_getContentId('user_id');
        }

        if (!isset($fetchOptions['join']))
        {
            $fetchOptions['join'] = 0;
        }

        $fetchOptions['join'] |= XenForo_Model_User::FETCH_USER_FULL | XenForo_Model_User::FETCH_LAST_ACTIVITY;

        $trader = $this->_models->user()->getUserById($userId, $fetchOptions);
        if (!$trader)
        {
            throw $this->getNotFoundResponseException('requested_trader_not_found');
        }

        return $trader;
    }

    public function assertCategoryValidAndViewable($categoryId = null, array $fetchOptions = array())
    {
        $fetchOptions += array('permissionCombinationId' => $this->_visitor['permission_combination_id']);
        $categoryModel = $this->_models->category();
        $category = $this->getCategoryOrError($categoryId, $fetchOptions);

        if (isset($category['category_permission_cache']))
        {
            $categoryModel->setCategoryPermCache(
                $this->_visitor['permission_combination_id'], $category['category_id'], $category['category_permission_cache']
            );

            unset ($category['category_permission_cache']);
        }

        if (!$categoryModel->canViewCategory($category, $errorPhraseKey))
        {
            throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        return $category;
    }

    public function assertClassifiedValidAndViewable($classifiedId = null, array $classifiedFetchOptions = array(), array $categoryFetchOptions = array())
    {
        if (!isset($classifiedFetchOptions['join']))
        {
            $classifiedFetchOptions['join'] = 0;
        }

        $classifiedFetchOptions['join'] |= GFNClassifieds_Model_Classified::FETCH_USER;

        $classified = $this->getClassifiedOrError($classifiedId, $classifiedFetchOptions);
        $category = $this->assertCategoryValidAndViewable($classified['category_id'], $categoryFetchOptions);

        if (!$this->_models->classified()->canViewClassified($classified, $category, $errorPhraseKey))
        {
            throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $classified = $this->_models->classified()->prepareClassified($classified, $category);
        $classified = $this->_models->classified()->prepareClassifiedCustomFields($classified, $category);

        return array($classified, $category);
    }

    public function assertCommentValidAndViewable($commentId = null, array $commentFetchOptions = array(), array $classifiedFetchOptions = array(), array $categoryFetchOptions = array())
    {
        if (!isset($commentFetchOptions['join']))
        {
            $commentFetchOptions['join'] = 0;
        }

        $commentFetchOptions['join'] |= GFNClassifieds_Model_Comment::FETCH_USER;

        $comment = $this->getCommentOrError($commentId, $commentFetchOptions);
        list ($classified, $category) = $this->assertClassifiedValidAndViewable(
            $comment['classified_id'], $classifiedFetchOptions, $categoryFetchOptions
        );

        if (!$this->_models->comment()->canViewComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $comment = $this->_models->comment()->prepareComment($comment, $classified, $category);
        return array($comment, $classified, $category);
    }

    public function assertTraderRatingValidAndViewable($feedbackId = null, array $fetchOptions = array())
    {
        if (!isset($fetchOptions['join']))
        {
            $fetchOptions['join'] = 0;
        }

        $fetchOptions['join'] |= GFNClassifieds_Model_TraderRating::FETCH_USER;
        $fetchOptions['likeUserId'] = XenForo_Visitor::getUserId();

        $rating = $this->getTraderRatingOrError($feedbackId, $fetchOptions);
        if (!$this->_models->traderRating()->canViewTraderRating($rating, $errorPhraseKey))
        {
            throw $this->_controller->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        return $rating;
    }

    public function getCustomFieldValues(array &$values = null, array &$shownKeys = null)
    {
        $input = $this->_controller->getInput();

        if ($values === null)
        {
            $values = $input->filterSingle('custom_fields', XenForo_Input::ARRAY_SIMPLE);
        }

        if ($shownKeys === null)
        {
            $shownKeys = $input->filterSingle('custom_fields_shown', XenForo_Input::STRING, array('array' => true));
        }

        if (!$shownKeys)
        {
            return array();
        }

        $fieldModel = $this->_models->field();
        $fields = $fieldModel->getFields(array());

        $output = array();
        foreach ($shownKeys as $key)
        {
            if (!isset($fields[$key]))
            {
                continue;
            }

            $field = $fields[$key];

            if (isset($values[$key]))
            {
                $output[$key] = $values[$key];
            }
            else if ($field['field_type'] == 'bbcode' && isset($values[$key . '_html']))
            {
                $messageTextHtml = strval($values[$key . '_html']);

                if ($input->filterSingle('_xfRteFailed', XenForo_Input::UINT))
                {
                    // actually, the RTE failed to load, so just treat this as BB code
                    $output[$key] = $messageTextHtml;
                }
                else if ($messageTextHtml !== '')
                {
                    $output[$key] = $this->_controller->getHelper('Editor')->convertEditorHtmlToBbCode($messageTextHtml, $input);
                }
                else
                {
                    $output[$key] = '';
                }
            }
        }

        return $output;
    }

    public function getRatingCriteriaValues(array &$values = null, array &$shownKeys = null)
    {
        $input = $this->_controller->getInput();

        if ($values === null)
        {
            $values = array();
            $rating = $input->filterSingle('criteria_rating', XenForo_Input::INT, array('array' => true));
            $review = $input->filterSingle('criteria_review', XenForo_Input::STRING, array('array' => true));

            foreach ($rating as $k => $v)
            {
                $values[$k]['rating'] = $v;
            }

            foreach ($review as $k => $v)
            {
                $values[$k]['message'] = $v;
            }

            foreach ($values as $k => $v)
            {
                if (!isset($v['rating']))
                {
                    unset ($values[$k]);
                }
            }
        }

        if ($shownKeys === null)
        {
            $shownKeys = $input->filterSingle('criteria_shown', XenForo_Input::STRING, array('array' => true));
        }

        if (!$shownKeys)
        {
            return array();
        }

        $criteriaModel = $this->_models->ratingCriteria();
        $criterias = $criteriaModel->getRatingCriterias(array());

        $output = array();
        foreach ($shownKeys as $key)
        {
            if (!isset($criterias[$key]))
            {
                continue;
            }

            if (isset($values[$key]))
            {
                $output[$key] = $values[$key];
            }
        }

        return $output;
    }

    protected function _getContentId($key, $filter = XenForo_Input::UINT)
    {
        return $this->_controller->getInput()->filterSingle($key, $filter);
    }

    public function getNotFoundResponseException($phraseName = 'requested_page_not_found')
    {
        if ($phraseName === 'requested_page_not_found')
        {
            return $this->_controller->responseException($this->_controller->getNotFoundResponse());
        }

        return $this->_controller->responseException(
            $this->_controller->responseError(new XenForo_Phrase($phraseName), 404)
        );
    }
}