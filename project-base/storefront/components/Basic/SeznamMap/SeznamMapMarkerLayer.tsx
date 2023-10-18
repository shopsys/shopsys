import { SeznamMapMarkerIcon } from './SeznamMapMarkerIcon';
import { useCallback, useEffect, useRef } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
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

    const creteMarkerIcon = useCallback(
        (isActive: boolean) => {
            const iconWrapper = document.createElement('div');
            iconWrapper.innerHTML = renderToStaticMarkup(
                <SeznamMapMarkerIcon isActive={isActive} isClickable={isMarkersClickable} />,
            );

            return iconWrapper;
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
                url: creteMarkerIcon(id === activeMarkerId),
            });
        });

        layerRef.current.addMarker(seznamMarkers);

        if (shouldFitMapToPoints) {
            const [center, zoom] = map.computeCenterZoom(allMarkersCoords);

            map.setCenterZoom(center, zoom);
        }
    }, [map, activeMarkerId, creteMarkerIcon, markers]);

    return null;
};
