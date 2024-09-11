import { GoogleMapMarker } from './GoogleMapMarker';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import GoogleMapReact from 'google-map-react';
import getConfig from 'next/config';
import { useEffect, useMemo, useRef, useState } from 'react';
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
};

type MarkerProperties = {
    cluster: boolean;
    markerIdentifier: string;
};

declare const google: any;

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
}) => {
    const { publicRuntimeConfig } = getConfig();
    const { mapSetting } = useDomainConfig();
    const defaultLatitude = latitude ? parseFloat(latitude) : mapSetting.latitude;
    const defaultLongitude = longitude ? parseFloat(longitude) : mapSetting.longitude;
    const [activeMarkerIdentifier, setActiveMarkerIdentifier] = useState<string>('');
    const [zoom, setZoom] = useState<number>(defaultZoom ?? mapSetting.zoom);
    const [bounds, setBounds] = useState<GeoJSON.BBox>();
    const mapRef = useRef<any>(null);
    const [isGoogleApiLoaded, setIsGoogleApiLoaded] = useState(false);

    const markersClusterConfig: PointFeature<MarkerProperties>[] = useMemo(() => {
        const validMarkers = (markers?.filter((marker) => marker.latitude !== null && marker.longitude !== null) ??
            []) as MapMarker[];

        return validMarkers.map(markerMapper);
    }, [markers]);

    const { clusters, supercluster } = useSupercluster({
        points: markersClusterConfig,
        zoom,
        bounds,
        options: { radius: 75, minZoom: 0, maxZoom: 20 },
    });

    const selectMarkerHandler = (identifier: string) => {
        if (!isDetail) {
            const newActiveMarkerIdentifier = activeMarkerIdentifier === identifier ? '' : identifier;
            setActiveMarkerIdentifier(newActiveMarkerIdentifier);
            activeMarkerHandler?.(newActiveMarkerIdentifier);
        }
    };

    const handleClusterClick = (cluster: any) => {
        const { cluster_id, point_count } = cluster.properties;

        if (supercluster && cluster_id) {
            const expansionZoom = Math.min(
                supercluster.getClusterExpansionZoom(cluster_id),
                20, // Max zoom level
            );

            const markersInCluster = supercluster.getLeaves(cluster_id, point_count); // Get all markers in this cluster

            const latLngBounds = new google.maps.LatLngBounds();
            markersInCluster.forEach((marker) => {
                latLngBounds.extend({
                    lat: marker.geometry.coordinates[1],
                    lng: marker.geometry.coordinates[0],
                });
            });

            setZoom(expansionZoom); // Set the zoom level to zoom into the cluster
            mapRef.current?.fitBounds(latLngBounds); // Adjust map bounds to fit all markers
        }
    };

    useEffect(() => {
        if (!isGoogleApiLoaded) {
            return;
        }

        if (markersClusterConfig.length > 1 && mapRef.current !== null && google !== undefined) {
            const newBounds = new google.maps.LatLngBounds();

            markersClusterConfig.forEach((point) => {
                newBounds.extend({
                    lat: point.geometry.coordinates[1],
                    lng: point.geometry.coordinates[0],
                });
            });

            mapRef.current.fitBounds(newBounds);
        } else if (markersClusterConfig.length === 1) {
            if (mapRef.current !== null) {
                mapRef.current.setZoom(15);
                mapRef.current.panTo({
                    lat: markersClusterConfig[0].geometry.coordinates[1],
                    lng: markersClusterConfig[0].geometry.coordinates[0],
                });
            }
        }

        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [markersClusterConfig, isGoogleApiLoaded]);

    return (
        <div className="w-full">
            <GoogleMapReact
                yesIWantToUseGoogleMapApiInternals
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
                onGoogleApiLoaded={({ map }) => {
                    mapRef.current = map;
                    setIsGoogleApiLoaded(true);
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
                            onClusterClicked={handleClusterClick}
                            onMarkerClicked={selectMarkerHandler}
                            {...coordinates}
                        />
                    );
                })}
            </GoogleMapReact>
        </div>
    );
};
