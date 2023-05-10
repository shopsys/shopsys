import { getGtmCartViewEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'types/gtm/events';

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
