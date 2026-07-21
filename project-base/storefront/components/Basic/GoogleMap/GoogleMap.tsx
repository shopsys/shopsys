import { getPublicConfigProperty } from 'envConfig';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import GoogleMapReact from 'google-map-react';
import { TypeMapStoreFragment } from 'graphql/requests/stores/fragments/MapStoreFragment.generated';
import { useMapStoresQuery } from 'graphql/requests/stores/queries/MapStoresQuery.generated';
import { TypeCoordinates } from 'graphql/types';
import { useEffect, useMemo, useRef, useState } from 'react';
import { PointFeature } from 'supercluster';
import { MapMarker, MapMarkerNullable } from 'types/map';
import useSupercluster from 'use-supercluster';
import { mapConnectionEdges } from 'utils/mappers/connection';
import useTranslation from '../../../utils/i18n/useTranslationWrapper';
import { GoogleMapMarker, GoogleMapSearchMarker } from './GoogleMapMarker';

const CLUSTER_RADIUS = 75;
const CLUSTER_MAX_ZOOM = 20;
const CLUSTER_MIN_ZOOM = 0;
const ONE_POINT_ONLY_ZOOM = 15;
const USER_COORDINATES_ZOOM = 10;

type GoogleMapProps = {
    latitude?: string | null;
    longitude?: string | null;
    defaultZoom?: number | null;
    markers?: MapMarkerNullable[];
    activeMarkerHandler?: (marker: MapMarker | null) => void;
    isDetail?: boolean;
    userCoordinates?: TypeCoordinates | null;
    shouldCenterToUserCoordinates?: boolean;
    additionalMarker?: MapMarkerNullable | null;
    gestureHandling?: 'auto' | 'cooperative' | 'greedy' | 'none';
};

type MarkerProperties = {
    cluster: boolean;
    marker?: MapMarker;
    markerIdentifier: string;
};

declare const google: any;

const getMarkerIdentifier = (marker: MapMarker) => marker.identifier ?? `${marker.latitude}-${marker.longitude}`;
const parseCoordinate = (coordinate: string | number | null | undefined): number | null => {
    if (coordinate === null || coordinate === undefined) {
        return null;
    }

    const parsedCoordinate = typeof coordinate === 'number' ? coordinate : parseFloat(coordinate);

    return Number.isFinite(parsedCoordinate) ? parsedCoordinate : null;
};

const markerMapper = (marker: MapMarker): PointFeature<MarkerProperties> => ({
    type: 'Feature' as const,
    properties: {
        cluster: false,
        marker,
        markerIdentifier: getMarkerIdentifier(marker),
    },
    geometry: {
        type: 'Point' as const,
        coordinates: [parseCoordinate(marker.longitude) as number, parseCoordinate(marker.latitude) as number],
    },
});

export const GoogleMap: FC<GoogleMapProps> = ({
    latitude,
    longitude,
    defaultZoom,
    markers,
    activeMarkerHandler,
    isDetail,
    userCoordinates = null,
    shouldCenterToUserCoordinates = true,
    additionalMarker = null,
    gestureHandling,
}) => {
    const googleMapApiKey = getPublicConfigProperty('googleMapApiKey');
    const { mapSetting } = useDomainConfig();
    const defaultLatitude = parseCoordinate(latitude) ?? parseCoordinate(mapSetting.latitude) ?? 0;
    const defaultLongitude = parseCoordinate(longitude) ?? parseCoordinate(mapSetting.longitude) ?? 0;
    const [activeMarkerIdentifier, setActiveMarkerIdentifier] = useState<string>('');
    const [zoom, setZoom] = useState<number>(defaultZoom ?? mapSetting.zoom);
    const [bounds, setBounds] = useState<GeoJSON.BBox>();
    const mapRef = useRef<any>(null);
    const [isGoogleApiLoaded, setIsGoogleApiLoaded] = useState(false);
    const { t } = useTranslation();

    const [{ data: mapStoresData }] = useMapStoresQuery({ pause: markers !== undefined });
    const effectiveMarkers = useMemo(
        () => markers ?? mapConnectionEdges<TypeMapStoreFragment>(mapStoresData?.stores.edges),
        [mapStoresData?.stores.edges, markers],
    );
    const validMarkers = useMemo(
        () =>
            (effectiveMarkers?.filter(
                (marker) => parseCoordinate(marker.latitude) !== null && parseCoordinate(marker.longitude) !== null,
            ) ?? []) as MapMarker[],
        [effectiveMarkers],
    );
    const markersClusterConfig: PointFeature<MarkerProperties>[] = useMemo(
        () => validMarkers.map(markerMapper),
        [validMarkers],
    );

    const { clusters, supercluster } = useSupercluster({
        points: markersClusterConfig,
        zoom,
        bounds,
        options: { radius: CLUSTER_RADIUS, minZoom: CLUSTER_MIN_ZOOM, maxZoom: CLUSTER_MAX_ZOOM },
    });
    const additionalMarkerLatitude = parseCoordinate(additionalMarker?.latitude);
    const additionalMarkerLongitude = parseCoordinate(additionalMarker?.longitude);
    const additionalMarkerCoordinates =
        additionalMarkerLatitude !== null && additionalMarkerLongitude !== null
            ? { lat: additionalMarkerLatitude, lng: additionalMarkerLongitude }
            : null;
    const selectMarkerHandler = (marker: MapMarker) => {
        if (!isDetail) {
            const identifier = getMarkerIdentifier(marker);
            const newActiveMarkerIdentifier = activeMarkerIdentifier === identifier ? '' : identifier;
            setActiveMarkerIdentifier(newActiveMarkerIdentifier);
            activeMarkerHandler?.(newActiveMarkerIdentifier === '' ? null : marker);
        }
    };

    const handleClusterClick = (cluster: any) => {
        const { cluster_id, point_count } = cluster.properties;

        if (supercluster && cluster_id) {
            const expansionZoom = Math.min(supercluster.getClusterExpansionZoom(cluster_id), CLUSTER_MAX_ZOOM);

            const markersInCluster = supercluster.getLeaves(cluster_id, point_count); // Get all markers in this cluster

            const latLngBounds = new google.maps.LatLngBounds();
            markersInCluster.forEach((marker) => {
                const markerLatitude = parseCoordinate(marker.geometry.coordinates[1]);
                const markerLongitude = parseCoordinate(marker.geometry.coordinates[0]);

                if (markerLatitude === null || markerLongitude === null) {
                    return;
                }

                latLngBounds.extend({
                    lat: markerLatitude,
                    lng: markerLongitude,
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

        if (latitude !== undefined && latitude !== null && longitude !== undefined && longitude !== null) {
            mapRef.current?.setZoom(defaultZoom ?? mapSetting.zoom);
            mapRef.current?.panTo({
                lat: defaultLatitude,
                lng: defaultLongitude,
            });

            return;
        }

        if (markersClusterConfig.length > 1 && mapRef.current !== null && google !== undefined) {
            const newBounds = new google.maps.LatLngBounds();

            const userLatitude = parseCoordinate(userCoordinates?.latitude);
            const userLongitude = parseCoordinate(userCoordinates?.longitude);

            if (shouldCenterToUserCoordinates && userLatitude !== null && userLongitude !== null) {
                mapRef.current.setZoom(USER_COORDINATES_ZOOM);
                mapRef.current.panTo({
                    lat: userLatitude,
                    lng: userLongitude,
                });

                return;
            }

            markersClusterConfig.forEach((point) => {
                newBounds.extend({
                    lat: point.geometry.coordinates[1],
                    lng: point.geometry.coordinates[0],
                });
            });

            mapRef.current.fitBounds(newBounds);
        } else if (markersClusterConfig.length === 1) {
            if (mapRef.current !== null) {
                mapRef.current.setZoom(ONE_POINT_ONLY_ZOOM);
                mapRef.current.panTo({
                    lat: markersClusterConfig[0].geometry.coordinates[1],
                    lng: markersClusterConfig[0].geometry.coordinates[0],
                });
            }
        }
    }, [
        markersClusterConfig,
        isGoogleApiLoaded,
        userCoordinates,
        shouldCenterToUserCoordinates,
        latitude,
        longitude,
        defaultLatitude,
        defaultLongitude,
        defaultZoom,
        mapSetting.zoom,
    ]);

    return (
        <div className="w-full">
            <GoogleMapReact
                yesIWantToUseGoogleMapApiInternals
                bootstrapURLKeys={{ key: googleMapApiKey || '' }}
                defaultCenter={{ lat: defaultLatitude, lng: defaultLongitude }}
                defaultZoom={defaultZoom ?? mapSetting.zoom}
                options={{
                    disableDoubleClickZoom: true,
                    fullscreenControl: false,
                    gestureHandling,
                    zoomControlOptions: { position: 1 },
                }}
                onChange={({ zoom, bounds }) => {
                    if (bounds?.nw === undefined || bounds.se === undefined) {
                        return;
                    }

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
                    const clusterLatitude = parseCoordinate(latitude);
                    const clusterLongitude = parseCoordinate(longitude);

                    if (clusterLatitude === null || clusterLongitude === null) {
                        return null;
                    }

                    // Coordinates consumed by GoogleMapReact
                    const coordinates = {
                        lat: clusterLatitude,
                        lng: clusterLongitude,
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

                {additionalMarkerCoordinates !== null && (
                    <GoogleMapSearchMarker
                        lat={additionalMarkerCoordinates.lat}
                        lng={additionalMarkerCoordinates.lng}
                        title={additionalMarker?.name || t('Search location marker')}
                    />
                )}
            </GoogleMapReact>
        </div>
    );
};
