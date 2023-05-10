import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffect } from 'react';

type SeznamMapControlsProps = {
    map: SMap;
};

export const SeznamMapControls: FC<SeznamMapControlsProps> = ({ map }) => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        const control = new SMap.Control.Mouse(SMap.MOUSE_PAN | SMap.MOUSE_ZOOM | SMap.MOUSE_WHEEL);
        map.addControl(control);

        return () => {
            map.removeControl(control);
        };
    }, [map]);

    useEffect(() => {
        const control = new SMap.Control.Zoom(
            {},
            {
                showZoomMenu: false,
                titles: [t('Zoom in'), t('Zoom out')],
            },
        );

        map.addControl(control);

        return () => {
            map.removeControl(control);
        };
    }, [map, t]);

    return null;
};
