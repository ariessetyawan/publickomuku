/* c1dcdc1153e2ddf304c75a5b70269c5793906bca
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */

/** @param {jQuery} $ jQuery Object */
!function ($, window, document, _undefined)
{
    if (typeof KomuKuYJB == 'undefined')
    {
        KomuKuYJB = {};
    }

    KomuKuYJB.GalleryUploader = function($galleryUploader)
    {
        var uploader = XenForo.AttachmentUploader($galleryUploader),
        swfUpload,
        $container = $('#GalleryImageContainer_' + $galleryUploader.data('uploaderid')),
        $featuredImageContainer = $('.ClassifiedFeaturedIcon'),
        $featuredImageField = $('.ClassifiedFeaturedIconField'),
        createImageHolder = function(fileId)
        {
            $container.removeClass('empty');
            var html = '<div class="GalleryImage" id="galleryImage_' + fileId + '" data-file-id="' + fileId + '">' +
                '<span class="FeatureButton"></span>' +
                '<span class="DeleteButton"></span>' +
                '<span class="ProgressBar"><span class="done"></span></span>' +
                '</div>';

            html = $(html);
            html.find('.DeleteButton').click(deleteImage);
            html.find('.FeatureButton').click(featureImage);
            html.xfInsert('appendTo', '#GalleryImageContainer_' + $galleryUploader.data('uploaderid'), 'xfFadeIn');
            $container.removeClass('empty');

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
                        if (!$container.find('.GalleryImage').length)
                        {
                            $container.addClass('empty');
                        }
                    });
                });
            }
            else if (container.data('file-id') && swfUpload)
            {
                swfUpload.cancelUpload(container.data('file-id'));
                container.xfRemove('xfFadeOut', function()
                {
                    if (!$container.find('.GalleryImage').length)
                    {
                        $container.addClass('empty');
                    }
                });
            }
        },
        featureImage = function(e)
        {
            e.preventDefault();
            var self = $(this), container = self.parent();

            self.toggleClass('selected');
            container.siblings().find('.FeatureButton.selected').removeClass('selected');

            if (self.hasClass('selected'))
            {
                $featuredImageContainer.css('background-image', 'url(' + container.data('image-path') + ')');
                $featuredImageField.val(container.data('attachment-id'));
            }
            else
            {
                $featuredImageContainer.css('background-image', 'url(' + $featuredImageContainer.data('default-image') + ')');
                $featuredImageField.val(0);
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

            $('#galleryImage_' + e.file.id).xfRemove('xfFadeOut', function()
            {
                if (!$container.find('.GalleryImage').length)
                {
                    $container.addClass('empty');
                }
            });
        };

        $galleryUploader.bind(
        {
            AttachmentQueueValidation: function(e)
            {
                swfUpload = e.swfUpload;
                if (!e.isImage)
                {
                    e.preventDefault();
                    e.swfUpload.cancelUpload(e.file.id, false);
                }
            },

            AttachmentQueued: function(e)
            {
                swfUpload = e.swfUpload;
                createImageHolder(e.file.id)
            },

            AttachmentQueueError: attachmentErrorHandler,
            AttachmentUploadError: attachmentErrorHandler,

            AttachmentUploadProgress: function(e)
            {
                swfUpload = e.swfUpload;
                console.log('Uploaded %d/%d bytes.', e.bytes, e.file.size);
                var percent = Math.min(100, Math.ceil(e.bytes * 100 / e.file.size));
                $('#galleryImage_' + e.file.id).find('.ProgressBar > .done').css('width', percent + '%');
            },

            AttachmentUploaded: function(e)
            {
                swfUpload = e.swfUpload;
                var container;

                if (typeof e.file != 'undefined')
                {
                    container = $('#galleryImage_' + e.file.id);
                }
                else
                {
                    container = createImageHolder(e.ajaxData.template_id);
                }

                var image = e.ajaxData.slideUrl || e.ajaxData.thumbnailUrl || e.ajaxData.viewUrl;
                container.css('background-image', 'url(' + image + ')');
                container.find('.DeleteButton').attr('data-href', e.ajaxData.deleteUrl);
                container.find('.ProgressBar').xfFadeOut();
                container.data('image-path', image);
                container.data('attachment-id', e.ajaxData.attachment_id);
                container.addClass('loaded');

                if ($featuredImageField.val() == '' || $featuredImageField.val() == 0)
                {
                    container.find('.FeatureButton').trigger('click');
                }
            }
        });

        if ($container.find('.GalleryImage.loaded').length)
        {
            $container.find('.GalleryImage.loaded .DeleteButton').click(deleteImage);
            $container.find('.GalleryImage.loaded .FeatureButton').click(featureImage);
        }
    };

    if (typeof XenForo.AttachmentUploader == 'function')
    {
        XenForo.register('.GalleryUploader', 'KomuKuYJB.GalleryUploader');
    }
}
(jQuery, this, document);