<?php

class AnonymousPosting_Engine
{

    public static function prepareResponse(
        XenForo_Controller $controller,
        XenForo_Input $input,
        XenForo_ControllerResponse_Abstract $response
    ) {
        if ($response instanceof XenForo_ControllerResponse_View
            && isset($response->params['forum'])
        ) {
            $forum = $response->params['forum'];
            $thread = (isset($response->params['thread']) ? $response->params['thread'] : array());

            /** @var AnonymousPosting_XenForo_Model_Forum $forumModel */
            $forumModel = $controller->getModelFromCache('XenForo_Model_Forum');

            if ($forumModel->AnonymousPosting_canPostAnonymouslyInForum($forum)) {
                $hash = self::getAnonymousPostingHash($forum['node_id'],
                    isset($thread['thread_id']) ? $thread['thread_id'] : 0);

                $response->params['anonymous_posting'] = array(
                    'checked' => ($hash === $input->filterSingle('anonymous_posting', XenForo_Input::STRING)),
                    'hash' => $hash,
                );
            }
        }

        return $response;
    }

    public static function getAnonymousPostingHash($forumId, $threadId)
    {
        $session = XenForo_Application::getSession();
        $salt = XenForo_Application::getConfig()->get('globalSalt');

        return md5(sprintf('%s%d%d%s', $session->getSessionId(), $forumId, $threadId, $salt));
    }

    public static function processAnonymousPosting(
        $forumId,
        $threadId,
        XenForo_Controller $controller,
        XenForo_DataWriter_DiscussionMessage $dw
    ) {
        $input = $controller->getInput()->filter(array(
            'attachment_hash' => XenForo_Input::STRING,
            'anonymous_posting' => XenForo_Input::STRING,
        ));

        // verify our hash
        if ($input['anonymous_posting'] !== self::getAnonymousPostingHash($forumId, $threadId)) {
            return null;
        }

        // use an pseudo-user
        $poster = array(
            'user_id' => 0,
            'username' => new XenForo_Phrase('anonymous_posting_poster'),
        );

        // try to find an actual user if configured
        $posterUsername = AnonymousPosting_Option::get('poster');
        if (!empty($posterUsername)) {
            /** @var XenForo_Model_User $userModel */
            $userModel = $controller->getModelFromCache('XenForo_Model_User');
            $posterUser = $userModel->getUserByName($posterUsername);
            if (!empty($posterUser)) {
                $poster = array(
                    'user_id' => $posterUser['user_id'],
                    'username' => $posterUser['username'],
                );
            }
        }

        // reset data writer to use anonymous values
        $dw->bulkSet(array(
            'anonymous_posting_real_user_id' => $dw->get('user_id'),
            'anonymous_posting_real_username' => $dw->get('username'),
        ));
        $dw->setOption(AnonymousPosting_XenForo_DataWriter_DiscussionMessage_Post::OPTION_IS_ANONYMOUS, true);
        $dw->bulkSet(array(
            'user_id' => $poster['user_id'],
            'username' => $poster['username'],
        ));

        // reset attachment data if available
        if (!empty($input['attachment_hash'])) {
            XenForo_Application::getDb()->query('
				UPDATE kmk_attachment_data AS data
				INNER JOIN kmk_attachment AS attachment ON (attachment.data_id = data.data_id)
				SET data.user_id = ?
				WHERE attachment.temp_hash = ?
			', array(
                $poster['user_id'],
                $input['attachment_hash']
            ));
        }

        return $poster;
    }

    public static function helperRoboHashUrl($data, $sizeCode)
    {
        if (!empty($data['post_id'])
            && !empty($data['thread_id'])
            && !empty($data['anonymous_posting_real_user_id'])
        ) {
            $hash = md5($data['thread_id'] . $data['anonymous_posting_real_user_id']
                . XenForo_Application::getConfig()->get('globalSalt'));

            switch ($sizeCode) {
                case 'm':
                    $size = 96;
                    break;
                case 's':
                    $size = 48;
                    break;
                case 'l':
                default:
                    $size = 192;
                    break;
            }

            $optionValue = intval(XenForo_Application::getOptions()->get('anonymous_roboHash'));
            $set = 'set1';
            switch ($optionValue) {
                case 2:
                    $set = 'set2';
                    break;
                case 3:
                    $set = 'set3';
                    break;
            }

            return sprintf('//robohash.org/%1$s.png?size=%2$dx%2$d&set=%3$s',
                $hash, $size, $set);
        }

        return '';
    }
}