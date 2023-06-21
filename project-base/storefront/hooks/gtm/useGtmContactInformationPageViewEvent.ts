import { getGtmContactInformationPageViewEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'types/gtm/events';

export const useGtmContactInformationPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (gtmPageViewEvent._isLoaded && gtmPageViewEvent.cart && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(getGtmContactInformationPageViewEvent(gtmPageViewEvent.cart));
        }
    }, [gtmPageViewEvent._isLoaded, gtmPageViewEvent.cart]);
};
