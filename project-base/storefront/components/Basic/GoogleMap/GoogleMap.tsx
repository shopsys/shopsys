import { GoogleMapMarker } from './GoogleMapMarker';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import GoogleMapReact from 'google-map-react';
import getConfig from 'next/config';
import { useEffect, useMemo, useState } from 'react';
import { PointFeature } from 'supercluster';
import { MapMarker, MapMarkerNullable } from 'types/map';
import useSupercluster from 'use-supercluster';

type GoogleMapProps = {
    latitude?: string | null;
    longitude?: string | null;
    defaultZoom?: number | null;
    markers?: MapMarkerNullable[];
    activeMarkerHandler?: (id: string) => void;
    isDetail?: boolean;
    closeMarkers?: boolean;
};

type MarkerProperties = {
    cluster: boolean;
    markerIdentifier: string;
};

const getMarkerIdentifier = (marker: MapMarker) => marker.identifier ?? `${marker.latitude}-${marker.longitude}`;

const markerMapper = (marker: MapMarker): PointFeature<MarkerProperties> => ({
    type: 'Feature' as const,
    properties: {
        cluster: false,
        markerIdentifier: getMarkerIdentifier(marker),
    },
    geometry: {
        type: 'Point' as const,
        coordinates: [parseFloat(marker.longitude), parseFloat(marker.latitude)],
    },
});

export const GoogleMap: FC<GoogleMapProps> = ({
    latitude,
    longitude,
    defaultZoom,
    markers,
    activeMarkerHandler,
    isDetail,
    closeMarkers,
}) => {
    const { publicRuntimeConfig } = getConfig();
    const { mapSetting } = useDomainConfig();
    const defaultLatitude = latitude ? parseFloat(latitude) : mapSetting.latitude;
    const defaultLongitude = longitude ? parseFloat(longitude) : mapSetting.longitude;
    const [activeMarkerIdentifier, setActiveMarkerIdentifier] = useState<string>('');
    const [zoom, setZoom] = useState<number>(defaultZoom ?? mapSetting.zoom);
    const [bounds, setBounds] = useState<GeoJSON.BBox>();

    const markersClusterConfig: PointFeature<MarkerProperties>[] = useMemo(() => {
        const validMarkers = (markers?.filter((marker) => marker.latitude !== null && marker.longitude !== null) ??
            []) as MapMarker[];

        return validMarkers.map(markerMapper);
    }, [markers]);

    const { clusters } = useSupercluster({
        points: markersClusterConfig,
        zoom,
        bounds,
        options: { radius: 75, maxZoom: 20 },
    });

    const selectMarkerHandler = (identifier: string) => {
        if (!isDetail) {
            const newActiveMarkerIdentifier = activeMarkerIdentifier === identifier ? '' : identifier;
            setActiveMarkerIdentifier(newActiveMarkerIdentifier);
            activeMarkerHandler?.(newActiveMarkerIdentifier);
        }
    };

    useEffect(() => {
        if (closeMarkers) {
            setActiveMarkerIdentifier('');
        }
    }, [closeMarkers]);

    return (
        <div className="h-full w-full">
            <GoogleMapReact
                bootstrapURLKeys={{ key: publicRuntimeConfig.googleMapApiKey }}
                defaultCenter={{ lat: defaultLatitude, lng: defaultLongitude }}
                defaultZoom={defaultZoom ?? mapSetting.zoom}
                options={{
                    disableDoubleClickZoom: true,
                    fullscreenControl: false,
                    zoomControlOptions: { position: 1 },
                }}
                onChange={({ zoom, bounds }) => {
                    setZoom(zoom);
                    setBounds([bounds.nw.lng, bounds.se.lat, bounds.se.lng, bounds.nw.lat]);
                }}
            >
                {clusters.map((cluster) => {
                    const [longitude, latitude] = cluster.geometry.coordinates;
                    // Coordinates consumed by GoogleMapReact
                    const coordinates = {
                        lat: latitude,
                        lng: longitude,
                    };

                    return (
                        <GoogleMapMarker
                            key={cluster.properties.markerIdentifier ?? cluster.id}
                            activeMarkerIdentifier={activeMarkerIdentifier}
                            cluster={cluster}
                            isDetail={isDetail}
                            onMarkerClicked={selectMarkerHandler}
                            {...coordinates}
                        />
                    );
                })}
            </GoogleMapReact>
        </div>
    );
};
