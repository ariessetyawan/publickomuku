/* 00fd30214081dd1040afe6620c809510c60b117d
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */

/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    if (typeof KomuKuYJB == 'undefined')
    {
        KomuKuYJB = {};
    }

    KomuKuYJB.ContainerClicker = function($container)
    {
        var href = $container.find('.TitleLink').attr('href');
        if (!href)
        {
            return;
        }

        $container.find('.ClickTrigger').css('cursor', 'pointer').bind('click', function(e)
        {
            if ($(e.target).closest('a').length || $(e.target).closest('input').length)
            {
                return;
            }

            XenForo.redirect(href);
        });
    };

    KomuKuYJB.PreviewTooltip = function($container)
    {
        var parent = $container.closest('.xenPreviewTooltip'),
        diff = parseInt(parent.offset().left + parent.outerWidth()) - $(window).width();

        if (diff > 0)
        {
            parent.css('width', parent.width() - diff);
        }
    };

    XenForo.register('.ContainerClicker', 'KomuKuYJB.ContainerClicker');
    XenForo.register('.PreviewTooltipProxy', 'KomuKuYJB.PreviewTooltip');
}
(jQuery, this, document);