/* e0c020d92e85c9cfc5950920000dd154e59972e4
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright � 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */

/** @param {jQuery} $ jQuery Object */
!function ($, window, document, _undefined)
{
    if (typeof GFNClassifieds == 'undefined')
    {
        GFNClassifieds = {};
    }

    GFNClassifieds.PackageInfoLoader = function($select)
    {
        $infoWrapper = $('.PackageInfo');

        $updateInfo = function()
        {
            $infoWrapper.xfFadeUp(null, function()
            {
                XenForo.ajax($select.data('package-url'), {package_id: $select.val()}, function(ajaxData)
                {
                    if (XenForo.hasTemplateHtml(ajaxData))
                    {
                        $infoWrapper.html(ajaxData.templateHtml).xfFadeDown();
                    }
                }, {error: false, global: false});
            });
        };

        $select.change($updateInfo);
        $updateInfo();
    };

    GFNClassifieds.PriceValidation = function($input)
    {
        $input.bind('keypress', function(e)
        {
            if (e.ctrlKey || e.altKey || e.metaKey)
            {
                return true;
            }

            if (event.charCode >= 48 && event.charCode <= 57)
            {
                return true;
            }

            return event.charCode == 44 || event.charCode == 46 || event.charCode == 0;

        });
    };

    XenForo.register('.PackageLoader', 'GFNClassifieds.PackageInfoLoader');
    XenForo.register('.PriceValidation', 'GFNClassifieds.PriceValidation');
}
(jQuery, this, document);