<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_DataWriter_User extends XenForo_DataWriter
{
    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'kmk_user_ratings_stats' => array(
                'user_id' => array('type' => self::TYPE_UINT, 'default' => array('kmk_user', 'user_id'), 'required' => true),
                'positive' => array('type' => self::TYPE_UINT, 'default' => 0),
                'neutral' => array('type' => self::TYPE_UINT, 'default' => 0),
                'negative' => array('type' => self::TYPE_UINT, 'default' => 0),
                'total' => array('type' => self::TYPE_INT, 'default' => 0),
                'rating' => array('type' => self::TYPE_UINT, 'default' => 0),
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
        if (!$userId = $this->_getExistingPrimaryKey($data, 'user_id')) {
            return false;
        }

        return array('kmk_user_ratings_stats' => $this->getModelFromCache('ThemeHouse_UserRatings_Model_User')->getUserById($userId));
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'user_id = '.$this->_db->quote($this->getExisting('user_id'));
    }

    /**
     * It sets user ratings.
     */
    public function setRating($rating, $removeRating = null)
    {
        $types = array(
            -1 => 'negative',
            0 => 'neutral',
            1 => 'positive',
        );

        if (!isset($types[$rating])) {
            return;
        }

        $this->set($types[$rating], $this->get($types[$rating]) + 1);
        if (isset($types[$removeRating])) {
            $this->set($types[$removeRating], $this->get($types[$removeRating]) - 1);
        }
        $this->set('total', $this->get('total') + $rating);
        $denominator = $this->get('positive') + $this->get('neutral') + $this->get('negative');
        if ($denominator) {
            $this->set('rating', max(0, ($this->get('positive') - $this->get('negative')) / $denominator * 100));
        } else {
            $this->set('rating', 0);
        }
    }
}
