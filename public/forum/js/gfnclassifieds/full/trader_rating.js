/* 45d3f0f8690e42c339df4abd3a1fbaeb59856ee7
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

    GFNClassifieds.TraderRating = function($container)
    {
        var changeSelection = function()
        {
            $container.find('.TraderRating').each(function()
            {
                var $label = $(this);

                if ($label.find('.TraderRatingSelector').is(':checked'))
                {
                    $label.addClass('selected');
                }
                else
                {
                    $label.removeClass('selected');
                }
            });
        };

        $container.find('.TraderRatingSelector').change(changeSelection);
        changeSelection();
    };

    XenForo.register('.TraderRatingContainer', 'GFNClassifieds.TraderRating');
}
(jQuery, this, document);