import { GoogleMapMarkerStyled, GoogleMapWrapStyled } from './GoogleMap.style';
import { FC } from 'react';
import getConfig from 'next/config';
import GoogleMapReact from 'google-map-react';

type GoogleMapProps = {
    lat: number;
    lng: number;
    zoom: number;
};

const GoogleMap: FC<GoogleMapProps> = (props) => {
    const { publicRuntimeConfig } = getConfig();
    const center = { lat: props.lat, lng: props.lng };

    return (
        <GoogleMapWrapStyled>
            <GoogleMapReact
                bootstrapURLKeys={{ key: publicRuntimeConfig.googleMapApiKey }}
                defaultCenter={center}
                defaultZoom={props.zoom}
            >
                <GoogleMapMarkerStyled icon="MapMarker" {...center}></GoogleMapMarkerStyled>
            </GoogleMapReact>
        </GoogleMapWrapStyled>
    );
};

export default GoogleMap;
