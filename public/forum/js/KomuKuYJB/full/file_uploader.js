/* 5baa9b3125db3dd63322fdf86757230d9fc22380
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

    KomuKuYJB.FileUploader = function($container)
    {
        var uploader = XenForo.AttachmentUploader($container),
            uploaderDisabler = $('#ctrl_resource_file_type_file_Disabler');

        if (uploaderDisabler.length)
        {
            var uploaderObject = $container.find('object');
            uploaderDisabler.bind(
                {
                    DisablerDisabled: function()
                    {
                        uploaderObject.css('visibility', 'visible');
                    },

                    DisablerEnabled: function()
                    {
                        uploaderObject.css('visibility', 'hidden');
                    }
                });
        }

        var $result = $($container.data('result')),
            $progress = $result.find('.Progress'),
            $meter = $progress.find('.Meter'),
            $filename = $result.find('.Filename'),
            $delete = $result.find('.Delete'),
            queueLength = 0,
            swfUpload,
            fileId,

            showContainer = function()
            {
                $container.css(
                    {
                        overflow: '',
                        height: '',
                        width: '',
                        position: ''
                    });
            },
            hideContainer = function()
            {
                $container.css(
                    {
                        overflow: 'hidden',
                        height: '1px',
                        width: '1px',
                        position: 'relative'
                    });
            },

            attachmentErrorHandler = function(e)
            {
                swfUpload = e.swfUpload;
                fileId = null;

                setTimeout(function() {
                    if (!$filename.is(':visible'))
                    {
                        var error = '';
                        if (e.ajaxData)
                        {
                            $.each(e.ajaxData.error, function(i, errorText) { error += errorText + "\n"; });
                        }

                        uploader.swfAlert(e.file, e.errorCode, error);

                        $('#' + e.file.id).xfRemove();
                        $result.hide();
                        showContainer();
                    }
                }, 1000);

                if (e.type == 'AttachmentUploadError')
                {
                    queueLength--;
                }
            };

        $container.bind(
            {
                AttachmentQueueValidation: function(e)
                {
                    swfUpload = e.swfUpload;

                    if (queueLength > 1)
                    {
                        e.preventDefault();
                        e.swfUpload.cancelUpload(e.file.id, false);
                        return;
                    }

                    fileId = e.file.id;
                },

                AttachmentQueued: function(e)
                {
                    swfUpload = e.swfUpload

                    if (queueLength > 1)
                    {
                        e.swfUpload.cancelUpload(e.file.id, false);
                        return;
                    }

                    fileId = e.file.id;

                    queueLength++;

                    console.log('Queued: %s (%d bytes)', e.file.name, e.file.size);

                    hideContainer();
                    $filename.hide();
                    $meter.css('width', 0);
                    $progress.show();
                    $result.fadeIn(XenForo.speed.fast);
                },

                AttachmentUploadProgress: function(e)
                {
                    swfUpload = e.swfUpload;
                    fileId = e.file.id;

                    console.log('Uploaded %d/%d bytes.', e.bytes, e.file.size);

                    var percent = Math.min(100, Math.ceil(e.bytes * 100 / e.file.size));

                    $meter.css('width', percent + '%');
                },

                AttachmentQueueError: attachmentErrorHandler,
                AttachmentUploadError: attachmentErrorHandler,

                AttachmentUploaded: function(e)
                {
                    swfUpload = e.swfUpload;
                    fileId = null;

                    var filename = e.ajaxData.filename || e.file.name;
                    console.info('Upload of %s completed!', filename);

                    hideContainer();
                    $result.show();
                    $progress.hide();
                    $filename.text(filename);
                    $filename.show();
                    $delete.data('href', e.ajaxData.deleteUrl);

                    if (e.file)
                    {
                        // only do the queue for the swf upload
                        queueLength--;
                    }
                }
            });

        $delete.bind('click', function(e)
        {
            e.preventDefault();
            console.log(e);

            if ($delete.data('href'))
            {
                XenForo.ajax(
                    $delete.data('href'), {},
                    function(ajaxData, textStatus)
                    {
                        $delete.removeData('href');
                        $result.fadeOut(XenForo.speed.fast, function()
                        {
                            showContainer();
                        });
                    }
                );
            }
            else
            {
                if (swfUpload && fileId)
                {
                    swfUpload.cancelUpload(fileId);
                }
                $result.fadeOut(XenForo.speed.fast, function()
                {
                    showContainer();
                });
            }

        });
    };

    // *********************************************************************

    if (typeof XenForo.AttachmentUploader == 'function')
    {
        XenForo.register('.FileUploader', 'KomuKuYJB.FileUploader');
    }
}
(jQuery, this, document);