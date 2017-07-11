/* 25e7cec0af7cdbf719c18a4d56826d939458a0b5
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright � 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */

/** @param {jQuery} $ jQuery Object */
!function ($, window, document, _undefined)
{
    if (typeof KomuKuYJB == 'undefined')
    {
        KomuKuYJB = {};
    }

    KomuKuYJB.Gallery = function($container) { this.Gallery($container) };
    KomuKuYJB.Gallery.prototype =
    {
        Gallery: function($container)
        {
            var slider = $container.find('.MiniSlider'),
            slides = slider.find('.Slide'),
            slideCount = slides.length,
            containerWidth, slideWidth,
            navigation = $container.find('.SliderNavigation'),
            leftCount = 0,
            padding, containerOffsetRight,
            activateTimeout = 0,
            loadVariables = function()
            {
                slideWidth = slides.eq(0).outerWidth(true);
                containerWidth = $container.width();
                containerOffsetRight = slider.offset().left + containerWidth;

                for (var i = 0; i < slideCount; i++)
                {
                    var slide = slides.eq(i);
                    if (slide.offset().top > slider.offset().top + 30)
                    {
                        padding = Math.ceil(i / 2); break;
                    }
                }

                if (padding)
                {
                    slider.removeClass('centered').css('width', parseInt(slideCount * slideWidth * 1.5));
                    var lastSlideOffsetRight = slides.last().offset().left + slideWidth;

                    navigation.xfFadeIn('slow').bind('click', function()
                    {
                        if ($(this).hasClass('right'))
                        {
                            leftCount += Math.min(padding, slideCount - (leftCount + padding));
                        }
                        else
                        {
                            leftCount -= leftCount > padding ? padding : leftCount;
                        }

                        var move = parseFloat(leftCount * slideWidth);
                        if (move >= lastSlideOffsetRight - containerOffsetRight)
                        {
                            leftCount -= leftCount > padding ? padding : leftCount;
                            move = lastSlideOffsetRight - containerOffsetRight;
                        }

                        slider.css('transform', 'translate3d(-' + move + 'px,0,0)')
                    });
                }
                else
                {
                    slider.addClass('centered').css('width', 'auto');
                    navigation.xfFadeOut('slow')
                }
            };

            loadVariables();

            if ($container.siblings('.GalleryImageContainer').length)
            {
                var imageContainer = $container.siblings('.GalleryImageContainer').first(),
                image = imageContainer.find('> img'),
                selectedImageIndex = 0,
                loadedImages = [],
                triggerContainer = $('.SliderOverlayTriggerContainer'),
                loadImage = function(index)
                {
                    var slide = slides.eq(index);
                    slide.addClass('selected').siblings('.selected').removeClass('selected');

                    if (loadedImages[index])
                    {
                        image.attr('src', loadedImages[index].src);
                        selectedImageIndex = index;
                    }
                    else
                    {
                        loadedImages[index] = new Image();
                        slide.addClass('loading');
                        image.addClass('loading');
                        imageContainer.addClass('loading');

                        loadedImages[index].onload = function()
                        {
                            selectedImageIndex = index;
                            image.attr('src', this.src);
                            image.removeClass('loading');
                            slide.removeClass('loading');
                            imageContainer.removeClass('loading');
                        };

                        loadedImages[index].src = slide.find('a').first().data('href') || slide.find('a').first().attr('href');
                    }
                };

                slides.find('a').bind('click', function(e)
                {
                    e.preventDefault();
                    loadImage($(this).parent().index());
                });

                $(document).ready(function() { loadImage(0); });

                $(window).resize(function()
                {
                    if (activateTimeout)
                    {
                        return;
                    }

                    activateTimeout = setTimeout(function()
                    {
                        loadVariables();
                        activateTimeout = 0;
                    }, 300);
                });

                if (triggerContainer.length)
                {
                    image.css('cursor', 'pointer').bind('click', function(e)
                    {
                        e.preventDefault();
                        triggerContainer.find('.Slide').eq(selectedImageIndex).find('.LbTrigger').trigger('click');
                    });

                    var miniSlider = $('.MiniGalleryContainer');
                    if (miniSlider.length)
                    {
                        miniSlider.find('.MiniSlider .Slide a').bind('click', function(e)
                        {
                            e.preventDefault();
                            triggerContainer.find('.Slide').eq($(this).parent().index()).find('.LbTrigger').trigger('click');
                        });
                    }
                }
            }
        }
    };

    KomuKuYJB.LocationMap = function($container)
    {
        var mapOptions =
        {
            zoom: 16,
            zoomControlOptions: true,
            streetViewControl: true,
            scrollwheel: false,
            center: new google.maps.LatLng($container.data('latitude'), $container.data('longitude'))
        },

        map = new google.maps.Map(document.getElementById('GoogleMap'), mapOptions),
        marker = new google.maps.Marker(
        {
            position: map.getCenter(),
            draggable: false,
            map: map
        });
    };

    KomuKuYJB.SocialShare = function($container)
    {
        $.fn.socialPopup = function (options)
        {
            var href = $(this).data('href') || $(this).attr('href') || false;
            if (!href)
            {
                return;
            }

            if (href.substring(0, 6) == 'mailto')
            {
                return;
            }

            if (typeof options != 'object')
            {
                options = {};
            }

            var defaultOptions =
            {
                height: options.height || 500,
                width: options.width || 650,
                scrollTo: options.scrollTo || 0,
                resizable: options.resizeable || 0,
                scrollbars: options.scrollbars || 0,
                location: options.location || 0
            };

            defaultOptions.top = options.top || (screen.height / 2) - (defaultOptions.height / 2);
            defaultOptions.left = options.left || (screen.width / 2) - (defaultOptions.width / 2);

            var serialized = '';
            for (var key in defaultOptions)
            {
                serialized += key + '=' + defaultOptions[key] + ',';
            }

            serialized = serialized.slice(0, -1);
            window.open(href, 'Popup', serialized);
        };

        $container.find('.SocialItem.SocialPopup a').bind('click', function(e)
        {
            e.preventDefault();
            $(this).socialPopup();
        });
    };

    KomuKuYJB.CommentButton = function ($button)
    {
        var target = $('.CommentAnchor');

        if (target.length)
        {
            $button.bind('click', function(e)
            {
                e.preventDefault();

                var top = target.offset().top,
                scroller = XenForo.getPageScrollTagName();
                $(scroller).animate({ scrollTop: top }, XenForo.speed.normal, 'easeOutBack');
            });
        }
    };

    XenForo.register('.GalleryThumbContainer', 'KomuKuYJB.Gallery');
    XenForo.register('.MiniGalleryContainer', 'KomuKuYJB.Gallery');
    XenForo.register('.SocialShareLinks', 'KomuKuYJB.SocialShare');
    XenForo.register('.CommentButton', 'KomuKuYJB.CommentButton');

    if (typeof google == 'object' && typeof google.maps == 'object')
    {
        XenForo.register('.LocationMap', 'KomuKuYJB.LocationMap');
    }
}
(jQuery, this, document);