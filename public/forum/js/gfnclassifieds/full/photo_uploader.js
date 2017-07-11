/* ba117554e23164d7b31a18e09d1582a8cc7d6bea
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */

/** @param {jQuery} $ jQuery Object */
!function ($, window, document, _undefined)
{
    if (typeof GFNClassifieds == 'undefined')
    {
        GFNClassifieds = {};
    }

    GFNClassifieds.PhotoUploader = function($photoUploader)
    {
        var uploader = XenForo.AttachmentUploader($photoUploader),
        swfUpload, queueLength = 0,
        $container = $('#PhotoImageContainer_' + $photoUploader.data('uploaderid')),
        createImageHolder = function(fileId)
        {
            var html = '<div class="PhotoImage" id="photoImage_' + fileId + '" data-file-id="' + fileId + '">' +
                '<span class="DeleteButton"></span>' +
                '<span class="ProgressBar"><span class="done"></span></span>' +
                '</div>';

            html = $(html);
            html.find('.DeleteButton').click(deleteImage);
            html.xfInsert('appendTo', '#PhotoImageContainer_' + $photoUploader.data('uploaderid'), 'xfFadeIn');

            return html;
        },
        deleteImage = function(e)
        {
            e.preventDefault();
            var self = $(this), container = self.parent();

            if (self.data('href'))
            {
                XenForo.ajax(self.data('href'), {},function()
                {
                    container.xfRemove('xfFadeOut', function()
                    {
                        showUploader();
                    });
                });
            }
            else if (container.data('file-id') && swfUpload)
            {
                swfUpload.cancelUpload(container.data('file-id'));
                container.xfRemove('xfFadeOut', function()
                {
                    showUploader();
                });
            }
        },
        attachmentErrorHandler = function(e)
        {
            var error = '';
            if (e.ajaxData)
            {
                $.each(e.ajaxData.error, function(i, errorText) { error += errorText + "\n"; });
            }

            uploader.swfAlert(e.file, e.errorCode, error);

            $('#photoImage_' + e.file.id).xfRemove('xfFadeOut', function()
            {
                showUploader();
            });
        },
        showUploader = function()
        {
            $photoUploader.css(
                {
                    overflow: '',
                    height: '',
                    width: '',
                    position: ''
                });
        },
        hideUploader = function()
        {
            $photoUploader.css(
                {
                    overflow: 'hidden',
                    height: '1px',
                    width: '1px',
                    position: 'relative'
                });
        };

        $photoUploader.bind(
            {
                AttachmentQueueValidation: function(e)
                {
                    swfUpload = e.swfUpload;
                    if (queueLength > 1 || !e.isImage)
                    {
                        e.preventDefault();
                        e.swfUpload.cancelUpload(e.file.id, false);
                    }
                },

                AttachmentQueued: function(e)
                {
                    swfUpload = e.swfUpload;

                    if (queueLength > 1)
                    {
                        e.swfUpload.cancelUpload(e.file.id, false);
                        return;
                    }

                    queueLength++;

                    createImageHolder(e.file.id);
                    hideUploader();
                },

                AttachmentQueueError: attachmentErrorHandler,
                AttachmentUploadError: attachmentErrorHandler,

                AttachmentUploadProgress: function(e)
                {
                    swfUpload = e.swfUpload;
                    console.log('Uploaded %d/%d bytes.', e.bytes, e.file.size);
                    var percent = Math.min(100, Math.ceil(e.bytes * 100 / e.file.size));
                    $('#photoImage_' + e.file.id).find('.ProgressBar > .done').css('width', percent + '%');
                },

                AttachmentUploaded: function(e)
                {
                    swfUpload = e.swfUpload;
                    var container;
                    hideUploader();

                    if (typeof e.file != 'undefined')
                    {
                        container = $('#photoImage_' + e.file.id);
                        queueLength--;
                    }
                    else
                    {
                        container = createImageHolder(e.ajaxData.template_id);
                    }

                    container.css('background-image', 'url(' + e.ajaxData.viewUrl + ')');
                    container.find('.DeleteButton').attr('data-href', e.ajaxData.deleteUrl);
                    container.find('.ProgressBar').xfFadeOut();
                    container.addClass('loaded');
                }
            });

        if ($container.find('.PhotoImage.loaded').length)
        {
            $container.find('.PhotoImage.loaded .DeleteButton').click(deleteImage);
            hideUploader();
        }
    };

    if (typeof XenForo.AttachmentUploader == 'function')
    {
        XenForo.register('.PhotoUploader', 'GFNClassifieds.PhotoUploader');
    }
}
(jQuery, this, document);