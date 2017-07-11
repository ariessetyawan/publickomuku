/* a8ece59539ea240aed53195ba244674e812e5f13
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */

/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    if (typeof GFNClassifieds == 'undefined')
    {
        GFNClassifieds = {};
    }

    GFNClassifieds.FileType = function($input)
    {
        var $editor = $('.TextHintEditor'),
        update = function()
        {
            switch ($input.val())
            {
                case 'hint_text':
                    $editor.xfFadeDown();
                    break;

                default:
                    $editor.xfFadeUp();
                    break;
            }
        };

        $input.change(update);
        $(window).load(update);
    };

    XenForo.register('input[name="field_type"]', 'GFNClassifieds.FileType');
}
(jQuery, this, document);