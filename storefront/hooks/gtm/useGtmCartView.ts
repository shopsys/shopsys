import { useEffect, useRef } from 'react';
import { getNewGtmEcommerceEvent } from 'utils/Gtm/EventFactories';
import { GtmPageViewEventType } from 'types/gtm';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

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
