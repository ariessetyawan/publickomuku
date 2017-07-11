/* 0bfe890ff68c17d3dfca46ada6f3f70a1ed3ee4e
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

    KomuKuYJB.LocationFetcher = function($container)
    {
        var mapOptions =
        {
            zoom: 12,
            mapTypeControl: false,
            disableDoubleClickZoom: true,
            zoomControlOptions: true,
            streetViewControl: false,
            center: new google.maps.LatLng($container.data('latitude'), $container.data('longitude'))
        },

        variables = ['route' ,'neighborhood' ,'sublocality_level_1', 'locality', 'administrative_area_level_2', 'administrative_area_level_1', 'country'],

        map = new google.maps.Map(document.getElementById('GoogleMap'), mapOptions),
        geocoder = new google.maps.Geocoder(),
        marker = new google.maps.Marker(
        {
            position: map.getCenter(),
            draggable: true,
            map: map
        }),

        inputWrapper = $('.LocationFinder'),
        searchForm = $container.siblings('.LocationSearch'),

        setPosition = function(position)
        {
            marker.setPosition(position);
            map.panTo(position);

            inputWrapper.find('.LocationInput').val('');
            inputWrapper.find('.LatitudeInput').val(position.lat());
            inputWrapper.find('.LongitudeInput').val(position.lng());

            var latlng = new google.maps.LatLng(position.lat(), position.lng());
            geocoder.geocode({latLng: latlng}, function(results, status)
            {
                if (status == google.maps.GeocoderStatus.OK && results[0])
                {
                    var result = results[0]['address_components'];

                    for (var i = 0; i < result.length; i++)
                    {
                        for (var j = 0; j < variables.length; j++)
                        {
                            for (var k = 0; k < result[i]['types'].length; k++)
                            {
                                if (variables[j] == result[i]['types'][k])
                                {
                                    inputWrapper.find('.LocationInput[data-name="' + variables[j] + '"]').val(
                                        variables[j] == 'country' ? result[i]['short_name'] : result[i]['long_name']
                                    );
                                }
                            }
                        }
                    }
                }
                else
                {
                    XenForo.alert(XenForo.phrases.unable_to_fetch_location_data_from_google_maps_api, null, 3000);
                }

                updateMessageText();
            });
        },
        updateMessageText = function()
        {
            var message = '', added = false;

            OuterLoop:
            for (var i = 0; i < variables.length; i++)
            {
                switch (variables[i])
                {
                    case 'locality':
                    case 'administrative_area_level_1':
                        continue OuterLoop;
                }

                var value = inputWrapper.find('.LocationInput[data-name="' + variables[i] + '"]').val();
                if (!value)
                {
                    continue;
                }

                if (added)
                {
                    message += ', ';
                }
                else
                {
                    added = true;
                }

                message += value;
            }

            $('.CurrentLocation').text(message);
        };

        google.maps.event.addListener(map, 'dblclick', function(e)
        {
            setPosition(e.latLng);
        });

        google.maps.event.addListener(marker, 'dragend', function(event) {
            setPosition(marker.position);
        });

        searchForm.bind('submit', function(e)
        {
            e.preventDefault();
            var value = searchForm.find('.textCtrl:text').val();

            if (!value)
            {
                return;
            }

            geocoder.geocode({address: value}, function(results, status)
            {
                if (status == google.maps.GeocoderStatus.OK && results)
                {
                    map.setZoom(16);
                    setPosition(results[0].geometry.location);
                }
                else
                {
                    XenForo.alert(XenForo.phrases.unable_to_fetch_location_data_from_google_maps_api, null, 3000);
                }
            });
        });
    };

    if (typeof google == 'object' && typeof google.maps == 'object')
    {
        XenForo.register('.LocationFetcher', 'KomuKuYJB.LocationFetcher');
    }
}
(jQuery, this, document);