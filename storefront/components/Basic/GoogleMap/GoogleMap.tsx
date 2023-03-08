import { Icon } from '../Icon/Icon';
import { GoogleMapMarker } from './GoogleMapMarker';
import GoogleMapReact from 'google-map-react';
import getConfig from 'next/config';
import { useEffect, useMemo, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { twJoin } from 'tailwind-merge';

type GoogleMapMarker = {
    locationLatitude: string | null;
    locationLongitude: string | null;
};

type GoogleMapProps = {
    lat?: string | null;
    lng?: string | null;
    zoom?: number | null;
    markers?: GoogleMapMarker[];
    activeMarkerHandler?: (index: number) => void;
    isDetail?: boolean;
    closeMarkers?: boolean;
};

const TEST_IDENTIFIER = 'basic-googlemap';

export const GoogleMap: FC<GoogleMapProps> = ({
    lat,
    lng,
    zoom,
    markers,
    activeMarkerHandler,
    isDetail,
    closeMarkers,
}) => {
    const { publicRuntimeConfig } = getConfig();
    const { mapSetting } = useShopsysSelector((state) => state.domain);
    const mapLat = lat === null || lat === undefined ? mapSetting.latitude : parseFloat(lat);
    const mapLng = lng === null || lng === undefined ? mapSetting.longitude : parseFloat(lng);
    const mapZoom = zoom === null || zoom === undefined ? mapSetting.zoom : zoom;
    const [activeMarker, setActiveMarker] = useState(-1);

    const mappedMarkers = useMemo(
        () =>
            markers?.map((marker) => ({
                locationLatitude: marker.locationLatitude !== null ? parseFloat(marker.locationLatitude) : null,
                locationLongitude: marker.locationLongitude !== null ? parseFloat(marker.locationLongitude) : null,
            })),
        [markers],
    );

    const markerClickHandler = (index: number) => {
        if (!isDetail) {
            setActiveMarker(activeMarker === index ? -1 : index);
        }
    };

    useEffect(() => {
        if (closeMarkers) {
            setActiveMarker(-1);
        }
    }, [closeMarkers]);

    useEffect(() => {
        if (activeMarkerHandler) {
            activeMarkerHandler(activeMarker);
        }
    }, [activeMarkerHandler, activeMarker]);

    return (
        <div className="h-full w-full" data-testid={TEST_IDENTIFIER}>
            <GoogleMapReact
                bootstrapURLKeys={{ key: publicRuntimeConfig.googleMapApiKey }}
                defaultCenter={{ lat: mapLat, lng: mapLng }}
                defaultZoom={mapZoom}
                options={{
                    disableDoubleClickZoom: true,
                    fullscreenControl: false,
                    zoomControlOptions: { position: 1 },
                }}
            >
                {mappedMarkers !== undefined &&
                    mappedMarkers.length !== 0 &&
                    mappedMarkers.map((mappedMarker, index) => (
                        <GoogleMapMarker
                            key={index}
                            lat={mappedMarker.locationLatitude}
                            lng={mappedMarker.locationLongitude}
                            isActive={index === activeMarker}
                            onClick={() => markerClickHandler(index)}
                            isDetail={isDetail}
                        >
                            <Icon
                                iconType="icon"
                                icon="MapMarker"
                                onClick={() => markerClickHandler(index)}
                                className={twJoin(
                                    'transf absolute',
                                    index === activeMarker ? 'h-12 w-10 text-orange' : 'h-9 w-8 text-greyDark ',
                                    isDetail && 'h-14 w-12 cursor-default',
                                )}
                            />
                        </GoogleMapMarker>
                    ))}
            </GoogleMapReact>
        </div>
    );
};
