import { getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'types/gtm';

export const useGtmCartView = (gtmStaticPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (
            gtmStaticPageViewEvent._isLoaded &&
            gtmStaticPageViewEvent.cart !== undefined &&
            gtmStaticPageViewEvent.cart !== null &&
            !wasViewedRef.current
        ) {
            wasViewedRef.current = true;
            const event = getNewGtmEcommerceEvent('ec.cart', true);
            event.ecommerce = gtmStaticPageViewEvent.cart;
            gtmSafePushEvent(event);
        }
    }, [gtmStaticPageViewEvent._isLoaded, gtmStaticPageViewEvent.cart]);
};
