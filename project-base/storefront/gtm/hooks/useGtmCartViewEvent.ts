import { getGtmCartViewEvent } from 'gtm/helpers/eventFactories';
import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { GtmPageViewEventType } from 'gtm/types/events';
import { useEffect, useRef } from 'react';

export const useGtmCartViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);
    const previousPromoCodes = useRef(JSON.stringify(gtmPageViewEvent.cart?.promoCodes));

    useEffect(() => {
        if (
            gtmPageViewEvent._isLoaded &&
            gtmPageViewEvent.cart !== undefined &&
            gtmPageViewEvent.cart !== null &&
            (!wasViewedRef.current || JSON.stringify(gtmPageViewEvent.cart.promoCodes) !== previousPromoCodes.current)
        ) {
            wasViewedRef.current = true;
            previousPromoCodes.current = JSON.stringify(gtmPageViewEvent.cart.promoCodes);
            gtmSafePushEvent(
                getGtmCartViewEvent(
                    gtmPageViewEvent.currencyCode,
                    gtmPageViewEvent.cart.valueWithoutVat,
                    gtmPageViewEvent.cart.valueWithVat,
                    gtmPageViewEvent.cart.products,
                ),
            );
        }
    }, [gtmPageViewEvent._isLoaded, gtmPageViewEvent.cart, gtmPageViewEvent.currencyCode]);
};
