import '../components/filterAllNodes';
import $ from 'jquery';
import MarkerClusterer from '@google/markerclustererplus';
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
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        var markers = getMarkers();
        var markerCluster = new MarkerClusterer(map, markers, {
            imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
        });

        markerCluster.setMap(map);

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
                position: markerData.locations
            });

            marker.addListener('click', function () {
                replaceMarkerContentBoxById(markerData.data.id);
            });
            bounds.extend(markerData.locations);

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

    }
}

$(document).ready(function () {
    new Register().registerCallback(Map.init);
});
