import { GoogleMapMarkerStyled, GoogleMapWrapStyled } from './GoogleMap.style';
import GoogleMapReact from 'google-map-react';
import getConfig from 'next/config';
import { FC, useEffect, useState } from 'react';
import { useShopsysSelector } from 'redux/main';

type GoogleMapMarker = {
    locationLatitude: number | null;
    locationLongitude: number | null;
};

type GoogleMapProps = {
    lat?: number | null;
    lng?: number | null;
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
    const mapLat = lat === null || lat === undefined ? mapSetting.latitude : lat;
    const mapLng = lng === null || lng === undefined ? mapSetting.longitude : lng;
    const mapZoom = zoom === null || zoom === undefined ? mapSetting.zoom : zoom;
    const [activeMarker, setActiveMarker] = useState(-1);

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
        <GoogleMapWrapStyled data-testid={TEST_IDENTIFIER}>
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
                {markers !== undefined &&
                    Array.isArray(markers) &&
                    markers.length !== 0 &&
                    markers.map((marker, index) => (
                        <GoogleMapMarkerStyled
                            iconType="icon"
                            icon="MapMarker"
                            key={index}
                            lat={marker.locationLatitude}
                            lng={marker.locationLongitude}
                            isDetail={isDetail}
                            isActive={index === activeMarker}
                            onClick={() => markerClickHandler(index)}
                        ></GoogleMapMarkerStyled>
                    ))}
            </GoogleMapReact>
        </GoogleMapWrapStyled>
    );
};
