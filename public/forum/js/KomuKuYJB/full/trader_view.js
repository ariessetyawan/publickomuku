/* 9192c04e8435bb18bff9070e32491351516e49fb
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

    KomuKuYJB.Tabs = function($container)
    {
        $container.find('li > a').each(function()
        {
            var self = $(this);

            if (self.attr('onclick'))
            {
                return;
            }

            self.attr('href', self.attr('href').replace(KomuKuYJB.traderViewLink, KomuKuYJB.memberViewLink));

            self.on('click', function(e)
            {
                e.preventDefault();
                XenForo.redirect(self.attr('href'));
            });
        });
    };

    XenForo.register('.Tabs.mainTabs', 'KomuKuYJB.Tabs');
}
(jQuery, this, document);