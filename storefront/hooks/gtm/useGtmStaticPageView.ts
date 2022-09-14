import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'types/gtm';

export const useGtmStaticPageView = (event: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (event._isLoaded && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(event);
        }
    }, [event]);
};
