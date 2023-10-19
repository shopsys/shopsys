import { getGtmContactInformationPageViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { GtmPageViewEventType } from 'gtm/types/events';
import { useEffect, useRef } from 'react';

export const useGtmContactInformationPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (gtmPageViewEvent._isLoaded && gtmPageViewEvent.cart && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(getGtmContactInformationPageViewEvent(gtmPageViewEvent.cart));
        }
    }, [gtmPageViewEvent._isLoaded, gtmPageViewEvent.cart]);
};
