/* 1a1a9703d3adb5f6b41212a85948938b77fb7cff
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

    KomuKuYJB.CommentPoster = function($form)
    {
        $form.bind(
        {
            AutoValidationComplete: function(e)
            {
                if (e.ajaxData.comments)
                {
                    //e.preventDefault();
                    var comments = e.ajaxData.comments;

                    new XenForo.ExtLoader(e.ajaxData, function()
                    {
                        $.each(comments, function(k, v)
                        {
                            $(v).xfInsert('prependTo', '#NewComments');
                        });
                    });

                    $form.find('input[name=last_date]').val(e.ajaxData.lastCommentDate);
                    $form.find('input:submit').removeAttr('disabled').removeClass('disabled');

                    var wysiwyg = $form.find('textarea').val('').data('XenForo.BbCodeWysiwygEditor');
                    if (wysiwyg)
                    {
                        wysiwyg.resetEditor();
                    }
                }
            }
        });
    };

    XenForo.register('#QuickReply', 'KomuKuYJB.CommentPoster');
}
(jQuery, this, document);