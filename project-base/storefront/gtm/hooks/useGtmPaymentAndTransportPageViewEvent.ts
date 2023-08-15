import { getGtmPaymentAndTransportPageViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'gtm/types/events';

export const useGtmPaymentAndTransportPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (
            gtmPageViewEvent._isLoaded &&
            gtmPageViewEvent.cart !== null &&
            gtmPageViewEvent.cart !== undefined &&
            !wasViewedRef.current
        ) {
            wasViewedRef.current = true;
            gtmSafePushEvent(
                getGtmPaymentAndTransportPageViewEvent(gtmPageViewEvent.cart.currencyCode, gtmPageViewEvent.cart),
            );
        }
    }, [gtmPageViewEvent._isLoaded, gtmPageViewEvent.cart]);
};
