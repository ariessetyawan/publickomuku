<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Data_Phrase
{
    public static function getPhrases()
    {
        return array(
            'unable_to_connect_to_api_server' => 'Unable to connect to API server. Please check error log.',
            'error_with_http_client' => 'Error with the HTTP client. Please check error log.',
            'invalid_response_body_from_api_server' => 'Invalid response body from API server.',
            'invalid_response_from_api_server' => 'Invalid response from API server.',
            'response_validation_failed' => 'Response validation failed. Most likely an attack. Please contact sikhlana@gfnlabs.com',
            'no_column_specified' => 'No column specified.',
            'minimum_xenforo_version_error' => 'The required minimum XenForo version is {required}. Currently installed version is {current}',
            'following_required_addons_not_installed' => "The following required add-ons are not installed: '{addons}'",
            'following_addons_are_out_of_date' => "The following add-ons are out of date: '{addons}'",
            'unable_to_read_directory' => "Unable to read directory '{directory}'"
        );
    }
} 