import { FC, useEffect, useState } from 'react';
import { GoogleMapMarkerStyled, GoogleMapWrapStyled } from './GoogleMap.style';
import getConfig from 'next/config';
import GoogleMapReact from 'google-map-react';
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
    activeMarker?: (index: number) => void;
    isDetail?: boolean;
    closeMarkers?: boolean;
};

const GoogleMap: FC<GoogleMapProps> = (props) => {
    const { publicRuntimeConfig } = getConfig();
    const { mapSetting } = useShopsysSelector((state) => state.domain);
    const lat = props.lat === null || props.lat === undefined ? mapSetting.latitude : props.lat;
    const lng = props.lng === null || props.lng === undefined ? mapSetting.longitude : props.lng;
    const zoom = props.zoom === null || props.zoom === undefined ? mapSetting.zoom : props.zoom;
    const [activeMarker, setActiveMarker] = useState(-1);

    const markerClickHandler = (index: number) => {
        if (props.isDetail !== true) {
            setActiveMarker(activeMarker === index ? -1 : index);
        }
    };

    useEffect(() => {
        if (props.closeMarkers === true) {
            setActiveMarker(-1);
        }
    }, [props.closeMarkers]);

    useEffect(() => {
        if (typeof props.activeMarker === 'function') {
            props.activeMarker(activeMarker);
        }
    }, [activeMarker]);

    return (
        <GoogleMapWrapStyled>
            <GoogleMapReact
                bootstrapURLKeys={{ key: publicRuntimeConfig.googleMapApiKey }}
                defaultCenter={{ lat: lat, lng: lng }}
                defaultZoom={zoom}
                options={{
                    disableDoubleClickZoom: true,
                    fullscreenControl: false,
                    zoomControlOptions: { position: 1 },
                }}
            >
                {props.markers !== undefined &&
                    Array.isArray(props.markers) &&
                    props.markers.length !== 0 &&
                    props.markers.map((marker, index) => (
                        <GoogleMapMarkerStyled
                            iconType="icon"
                            icon="MapMarker"
                            key={index}
                            lat={marker.locationLatitude}
                            lng={marker.locationLongitude}
                            isDetail={props.isDetail}
                            isActive={index === activeMarker}
                            onClick={() => markerClickHandler(index)}
                        ></GoogleMapMarkerStyled>
                    ))}
            </GoogleMapReact>
        </GoogleMapWrapStyled>
    );
};

export default GoogleMap;
