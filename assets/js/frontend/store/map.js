import '../components/filterAllNodes';
import $ from 'jquery';
import Register from 'framework/common/utils/Register';

export default class Map {
    static init ($container) {
        var googleMapsDivElement = document.getElementById('js-google-map-box');

        if (googleMapsDivElement == null) {
            return;
        }

        this.$container = $container;
        var map;
        var bounds = new google.maps.LatLngBounds();

        map = new google.maps.Map(googleMapsDivElement, {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            fullscreenControl: false,
            mapTypeControl: false,
            rotateControl: false,
            streetViewControl: false,
            zoomControlOptions: {
                position: google.maps.ControlPosition.LEFT_TOP
            },
            styles: [
                {
                    'featureType': 'administrative.country',
                    'elementType': 'labels.text',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'administrative.land_parcel',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'administrative.locality',
                    'elementType': 'labels.text',
                    'stylers': [
                        {
                            'color': '#898a85'
                        },
                        {
                            'visibility': 'simplified'
                        }
                    ]
                },
                {
                    'featureType': 'administrative.neighborhood',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'poi',
                    'elementType': 'labels.text',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'road',
                    'elementType': 'labels',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'water',
                    'elementType': 'labels.text',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                }
            ]
        });

        var markers = getMarkers();

        map.fitBounds(bounds);

        var listener = google.maps.event.addListener(map, 'idle', function () {
            if (markers.length > 1) {
                map.setZoom(7);
            } else {
                map.setZoom(15);
            }

            google.maps.event.removeListener(listener);
        });

        function getMarkers () {
            var markers = [];

            for (var i = 0, len = markersData.length; i < len; i++) {
                var marker = createMarker(markersData[i]);
                markers.push(marker);
            }

            return markers;
        }

        function createMarker (markerData) {
            var marker = new google.maps.Marker({
                position: markerData.locations,
                icon: '/public/frontend/images/marker.svg'
            });

            marker.addListener('click', function () {
                replaceMarkerContentBoxById(markerData.data.id);
                for (var j = 0; j < markers.length; j++) {
                    markers[j].setIcon('/public/frontend/images/marker.svg');
                }
                marker.setIcon('/public/frontend/images/marker-selected.svg');
            });
            bounds.extend(markerData.locations);

            marker.setMap(map);

            return marker;
        }

        function replaceMarkerContentBoxById (storeId) {
            if (storeId !== '0') {
                $container.filterAllNodes('.js-store-info').each((key, group) => {
                    $(group).addClass('display-none');
                });

                var $markerContent = $container.filterAllNodes('.js-store-info[data-store-id=' + storeId + ']');
                $markerContent.removeClass('display-none');
            }
        }

        $('.js-google-map-close-store').on('click', hideAllContentBoxes);

        function hideAllContentBoxes () {
            $container.filterAllNodes('.js-store-info').addClass('display-none');
            for (var j = 0; j < markers.length; j++) {
                markers[j].setIcon('/public/frontend/images/marker.svg');
            }
        }
    }
}

$(document).ready(function () {
    new Register().registerCallback(Map.init);
});
