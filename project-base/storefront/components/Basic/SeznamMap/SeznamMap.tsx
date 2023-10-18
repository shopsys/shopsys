import { SeznamMapControls } from './SeznamMapControls';
import { SeznamMapLayer } from './SeznamMapLayer';
import { SeznamMapMarkerLayer } from './SeznamMapMarkerLayer';
import { SeznamMapMounter } from './SeznamMapMounter';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { showErrorMessage } from 'helpers/toasts';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { LatLngLiteral, MapMarker } from 'types/map';
import { SeznamMapLoaderLang } from 'types/seznamMap';

type SeznamMapProps = {
    markers: Array<MapMarker>;
    center?: LatLngLiteral | null;
    zoom?: number;
    activeMarkerHandler?: (markerId: string) => void;
    activeMarkerId?: string;
};

export const SeznamMap: FC<SeznamMapProps> = ({ markers, center, zoom, activeMarkerHandler, activeMarkerId }) => {
    const { t } = useTranslation();
    const [map, setMap] = useState<SMap | null>(null);
    const {
        defaultLocale,
        mapSetting: { zoom: defaultZoom, latitude, longitude },
    } = useDomainConfig();

    const onLoad = (createdMap: SMap) => {
        setMap(createdMap);
    };

    const onError = () => {
        showErrorMessage(t('Error occured while loading seznam maps'));
    };

    return (
        <div className="relative h-full w-full">
            <SeznamMapMounter
                loaderConfig={{ lang: defaultLocale as SeznamMapLoaderLang }}
                mapContainerClassName="h-full w-full"
                zoom={zoom ?? defaultZoom}
                center={
                    center ?? {
                        lat: latitude,
                        lng: longitude,
                    }
                }
                loaderAPIConfig={{
                    jak: true,
                    poi: false,
                    pano: false,
                    suggest: false,
                }}
                onError={onError}
                onLoad={onLoad}
            >
                {map ? (
                    <SeznamMapLayer activeMarkerHandler={activeMarkerHandler} map={map}>
                        <SeznamMapControls map={map} />
                        <SeznamMapMarkerLayer
                            activeMarkerId={activeMarkerId}
                            isMarkersClickable={!!activeMarkerHandler}
                            map={map}
                            markers={markers}
                        />
                    </SeznamMapLayer>
                ) : (
                    <LoaderWithOverlay className="w-12" />
                )}
            </SeznamMapMounter>
        </div>
    );
};
