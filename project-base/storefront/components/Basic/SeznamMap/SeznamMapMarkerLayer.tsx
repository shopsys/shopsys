import { useCallback, useEffect, useRef } from 'react';
import { MapMarker } from 'types/map';

const CLUSTERER_MAX_DISTANCE = 75;

type SeznamMapMarkerLayerProps = {
    map: SMap;
    markers: Array<MapMarker>;
    activeMarkerId?: string;
    isMarkersClickable: boolean;
};

export const SeznamMapMarkerLayer: FC<SeznamMapMarkerLayerProps> = ({
    map,
    markers,
    activeMarkerId,
    isMarkersClickable,
}) => {
    const layerRef = useRef<SMap.LayerMarker>();

    const createMarkerIcon = useCallback(
        (isActive: boolean) => {
            const iconElement = document.createElement('div');

            iconElement.innerHTML = `
            <svg class="${[
                'w-8',
                'transition-transform',
                isActive ? 'origin-bottom scale-125 text-orange' : 'text-greyDark',
                isMarkersClickable ? 'cursor-pointer' : 'cursor-default',
            ].join(' ')}" viewBox="0 0 30 38" xmlns="http://www.w3.org/2000/svg">
                <path d="M30,15A15,15,0,1,0,10.089,29.161L15,38l4.911-8.839A14.994,14.994,0,0,0,30,15Z" fill="currentColor" />
            </svg>`;

            return iconElement;
        },
        [isMarkersClickable],
    );

    useEffect(() => {
        const layer = new SMap.Layer.Marker();
        if (markers.length > 1) {
            const clusterer = new SMap.Marker.Clusterer(map, CLUSTERER_MAX_DISTANCE);
            layer.setClusterer(clusterer);
        }

        layer.addMarker([]);
        map.addLayer(layer).enable();

        layerRef.current = layer;

        return () => {
            map.removeLayer(layer);
        };
    }, [map, markers]);

    useEffect(() => {
        if (!layerRef.current) {
            return;
        }

        const shouldFitMapToPoints = layerRef.current.getMarkers().length === 0;
        layerRef.current.removeAll();
        const allMarkersCoords: Array<SMap.Coords> = [];

        const seznamMarkers = markers.map(({ lng, lat, id }) => {
            const coords = SMap.Coords.fromWGS84(lng, lat);

            allMarkersCoords.push(coords);

            return new SMap.Marker(coords, id, {
                url: createMarkerIcon(id === activeMarkerId),
            });
        });

        layerRef.current.addMarker(seznamMarkers);

        if (shouldFitMapToPoints) {
            const [center, zoom] = map.computeCenterZoom(allMarkersCoords);

            map.setCenterZoom(center, zoom);
        }
    }, [map, activeMarkerId, createMarkerIcon, markers]);

    return null;
};
