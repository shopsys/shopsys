import { useSeznamMapLoader } from 'hooks/seznamMap/useSeznamMapLoader';
import { useEffect, useRef, useState } from 'react';
import { LatLngLiteral } from 'types/map';
import { SeznamMapAPILoaderConfig, SeznamMapLoaderConfig, SeznamMapMapOptions } from 'types/seznamMap';

type SeznamMapMounterProps = {
    center: LatLngLiteral;
    zoom: number;
    mapId?: string;
    mapContainerClassName?: string;
    mapOptions?: SeznamMapMapOptions;
    loaderConfig?: SeznamMapLoaderConfig;
    loaderAPIConfig?: SeznamMapAPILoaderConfig;
    onLoad?: (sMap: SMap) => void;
    onError?: () => void;
};

export const SeznamMapMounter: FC<SeznamMapMounterProps> = ({
    children,
    center,
    zoom,
    mapId = 'seznam-map-container',
    mapContainerClassName,
    mapOptions,
    loaderConfig,
    loaderAPIConfig,
    onLoad,
    onError,
}) => {
    const [isMapCreated, setIsMapCreated] = useState(false);
    const isMapLoaded = useSeznamMapLoader(loaderConfig, loaderAPIConfig, onError);

    const mapContainerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (isMapCreated || !isMapLoaded || mapContainerRef.current === null) {
            return;
        }

        const sMap = new SMap(mapContainerRef.current, SMap.Coords.fromWGS84(center.lng, center.lat), zoom, mapOptions);
        setIsMapCreated(true);

        if (onLoad) {
            onLoad(sMap);
        }
    }, [center, isMapCreated, isMapLoaded, mapOptions, onLoad, zoom]);

    return (
        <div className={mapContainerClassName} id={mapId} ref={mapContainerRef}>
            {children}
        </div>
    );
};
