<?php

class AnonymousPosting_Option
{
    public static function get($key, $subKey = null)
    {
        $options = XenForo_Application::getOptions();

        return $options->get('anonymous_posting_' . $key, $subKey);
    }

    public static function validatePoster(&$value, XenForo_DataWriter $dw, $fieldName)
    {
        if (!empty($value)) {
            /** @var XenForo_Model_User $model */
            $model = XenForo_Model::create('XenForo_Model_User');
            $user = $model->getUserByName($value);
            if (empty($user)) {
                $dw->error(new XenForo_Phrase('anonymous_poster_invalid_poster_username'), 'option_value');
                return false;
            }
        }

        return true;
    }

}
