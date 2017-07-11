<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_DataWriter_Ratings extends XenForo_DataWriter
{
    /**
     * Constant to store the extra data fields for item DW in _postSave.
     *
     * @var string
     */
    const OPTION_MAX_MESSAGE_LENGTH = 'maxMessageLength';

    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'kmk_user_ratings' => array(
                'rating_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'from_user_id' => array('type' => self::TYPE_UINT, 'required' => true),
                'to_user_id' => array('type' => self::TYPE_UINT, 'required' => true),
                'from_username' => array('type' => self::TYPE_STRING, 'required' => true),
                'to_username' => array('type' => self::TYPE_STRING, 'required' => true),
                'rating' => array('type' => self::TYPE_INT, 'required' => true, 'verification' => array('$this', '_verifyRating')),
                'message' => array('type' => self::TYPE_STRING),
                'active' => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
                'rating_date' => array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
            ),
        );
    }

    /**
     * Gets the actual existing data out of data that was passed in. See parent for explanation.
     *
     * @param mixed
     *
     * @return array|false
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data)) {
            return false;
        }

        return array('kmk_user_ratings' => $this->getModelFromCache('ThemeHouse_UserRatings_Model_Ratings')->getRatingById($id));
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'rating_id = '.$this->_db->quote($this->getExisting('rating_id'));
    }

    /**
     * _getDefaultOptions.
     */
    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_MAX_MESSAGE_LENGTH => XenForo_Application::get('options')->messageLength,
        );
    }

    /**
     * Specific discussion pre-save behaviors.
     */
    protected function _preSave()
    {
        //Set up a maxium message length. Staff is excluded.
        $visitor = XenForo_Visitor::getInstance();
        $messageLength = utf8_strlen($this->get('message'));

        $maxMessageLength = $this->getOption(self::OPTION_MAX_MESSAGE_LENGTH);

        if ($maxMessageLength != 0 and $messageLength > $maxMessageLength and !$visitor['is_admin'] and !$visitor['is_moderator'] and !$visitor['is_staff']) {
            $exceededMessage = $messageLength - $maxMessageLength;

            $this->error(new XenForo_Phrase('th_max_length_message_error',
                array('count' => $maxMessageLength, 'exceeded' => $exceededMessage)), 'message'
            );
        }
    }

    /**
     * Specific discussion post-save behaviors.
     */
    protected function _postSave()
    {
        $remove = null;
        if ($this->isUpdate()) {
            if ($this->isChanged('rating')) {
                $remove = $this->getExisting('rating');
            } else {
                return;
            }
        }

        $model = $this->getModelFromCache('ThemeHouse_UserRatings_Model_User');
        $user = $model->getUserById($this->get('to_user_id'));

        $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_User');

        if (isset($user['user_id'])) {
            $dw->setExistingData($user['user_id']);
        } else {
            $dw->set('user_id', $this->get('to_user_id'));
        }
        $dw->setRating($this->get('rating'), $remove);

        $dw->save();
    }

    /**
     * Specific discussion post-delete behaviors.
     */
    protected function _postDelete()
    {
        $model = $this->getModelFromCache('ThemeHouse_UserRatings_Model_User');
        $user = $model->getUserById($this->get('to_user_id'));

        $rating = $this->get('rating');

        $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_User');
        $dw->setExistingData($user['user_id']);

        switch ($rating) {
            case -1: $user['negative']--; $dw->set('negative', $user['negative']); break;
            case 0: $user['neutral']--; $dw->set('neutral', $user['neutral']); break;
            case 1: $user['positive']--; $dw->set('positive', $user['positive']); break;
        }

        $dw->set('total', $user['total'] - $rating);

        if ($user['positive'] + $user['neutral'] + $user['negative'] == 0) {
            $dw->set('rating', 0);
        } else {
            $dw->set('rating', max(0, ($user['positive'] - $user['negative']) / ($user['positive'] + $user['neutral'] + $user['negative']) * 100));
        }

        $dw->save();
    }

    /**
     * It verifies user ratings.
     */
    protected function _verifyRating(&$value, $writer, $fieldName, $fieldData)
    {
        $value = min(max($value, -1), 1);

        return true;
    }
}
