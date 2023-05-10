import { getGtmPaymentAndTransportPageViewEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'types/gtm/events';

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
