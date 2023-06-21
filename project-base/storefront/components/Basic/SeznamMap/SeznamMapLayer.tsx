import { useEffect } from 'react';

type SeznamMapLayerProps = {
    map: SMap;
    activeMarkerHandler?: (markerId: string) => void;
};

export const SeznamMapLayer: FC<SeznamMapLayerProps> = ({ children, map, activeMarkerHandler }) => {
    useEffect(() => {
        const layer = map.addDefaultLayer(SMap.DEF_BASE);
        layer.enable();

        if (activeMarkerHandler) {
            const signals = map.getSignals();
            signals.addListener(window, 'marker-click', (e) => {
                const markerId = e?.target?.getId();
                if (markerId !== undefined) {
                    activeMarkerHandler(markerId);
                }
            });
        }

        return () => {
            map.removeLayer(layer);
        };
    }, [activeMarkerHandler, map]);

    return <>{children}</>;
};
