<?php

class KomuKu_Emoticons_Importer_UserSmilie extends XenForo_Importer_Abstract
{
    public static function getName()
    {
        return '[KomuKu] Emoticons: Import from User Smilies';
    }

    public function configure(XenForo_ControllerAdmin_Abstract $controller, array &$config)
    {
        if($controller->getInput()->filterSingle('retain_keys', XenForo_Input::BOOLEAN)) {
            throw new XenForo_Exception('This import did not support retain IDs', true);
        }

        if($config)
        {
            $errors = $this->validateConfiguration($config);
            if($errors)
            {
                return $controller->responseError($errors);
            }

            $this->_bootstrap($config);
            return true;
        }
        else
        {
            return $controller->responseView('KomuKu_Emoticons_ViewAdmin_Emoticon_Import', 'emoticon_import');
        }
    }

    public function validateConfiguration(array &$config)
    {
        $errors = array();
        $db = XenForo_Application::getDb();

        try
        {
            $db->query("SELECT 1 FROM kmk_user_smilie LIMIT 1");
        }
        catch(Zend_Db_Exception $e)
        {
            $errors[] = new XenForo_Phrase('emoticon_the_addon_required_did_not_exists');
        }

        $config['db']['dbname'] = '';
        return $errors;
    }

    public function getSteps()
    {
        return array(
            'userSmilies' => array('title' => new XenForo_Phrase('emoticon_import_user_smilies'))
        );
    }

    public function stepUserSmilies($start, array $options)
    {
        $options = array_merge(array(
            'limit' => 100,
            'max' => false
        ), $options);

        $db = XenForo_Application::getDb();

        if($options['max'] === false)
        {
            $options['max'] = $db->fetchOne('SELECT MAX(user_smilie_id) FROM kmk_user_smilie');
        }

        $smilies = $db->fetchAll(
            $db->limit('
            SELECT smilie.*,permission_combination.cache_value AS global_permission_cache
            FROM kmk_user_smilie as smilie
                INNER JOIN kmk_user as user ON (user.user_id = smilie.user_id)
                LEFT JOIN kmk_permission_combination AS permission_combination ON
                    (permission_combination.permission_combination_id = user.permission_combination_id)
            WHERE smilie.user_smilie_id > ' .$db->quote($start),$options['limit'])
        );
        if(!$smilies)
        {
            return true;
        }

        $total = 0;
        $next = 0;

        $emoticonModel = XenForo_Model::create('KomuKu_Emoticons_Model_Emoticon');

        XenForo_Db::beginTransaction();

        foreach($smilies as &$smilie)
        {
            $next = $smilie['user_smilie_id'];

            $temp = tempnam(XenForo_Helper_File::getTempDir(), 'emoticonImport');
            $contents = @file_get_contents($smilie['image_url']);

            if(!$temp)
            {
                continue;
            }

            $parts = @parse_url($smilie['image_url']);
            if(!$contents
                || !$parts
                || $emoticonModel->getEmoticonByTextReplace($smilie['smilie_text']))
            {
                unlink($temp);
                continue;
            }

            file_put_contents($temp, $contents);
            $smilie['permissions'] = XenForo_Permission::unserializePermissions($smilie['global_permission_cache']);

            $upload = new XenForo_Upload(basename($parts['path']), $temp);
            $inputData = array(
                'caption' => $smilie['smilie_title'],
                'text_replace' => str_replace(':', '', $smilie['smilie_text'])
            );

            try
            {
                $emoticonModel->doUpload($upload, $inputData, $smilie);
                $total++;
            }
            catch(Exception $e)
            {
                @unlink($temp);
            }
        }

        XenForo_Db::commit();

        $this->_session->incrementStepImportTotal($total);
		return array($next, $options, $this->_getProgressOutput($next, $options['max']));
    }
}
