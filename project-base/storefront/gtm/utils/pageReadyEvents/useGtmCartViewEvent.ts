import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmCartViewEvent } from 'gtm/factories/getGtmCartViewEvent';
import { GtmPageReadyEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmCartViewEvent = (gtmPageReadyEvent: GtmPageReadyEventType): void => {
    const wasViewedRef = useRef(false);
    const previousPromoCodes = useRef(JSON.stringify(gtmPageReadyEvent.cart?.promoCodes));
    const { didPageReadyRun, isScriptLoaded } = useGtmContext();
    const { canSeePrices } = useAuthorization();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageReadyRun &&
            gtmPageReadyEvent._isLoaded &&
            gtmPageReadyEvent.cart !== undefined &&
            gtmPageReadyEvent.cart !== null &&
            (!wasViewedRef.current || JSON.stringify(gtmPageReadyEvent.cart.promoCodes) !== previousPromoCodes.current)
        ) {
            wasViewedRef.current = true;
            previousPromoCodes.current = JSON.stringify(gtmPageReadyEvent.cart.promoCodes);
            gtmSafePushEvent(
                getGtmCartViewEvent(
                    gtmPageReadyEvent.currencyCode,
                    gtmPageReadyEvent.cart.valueWithoutVat,
                    gtmPageReadyEvent.cart.valueWithVat,
                    gtmPageReadyEvent.cart.products,
                    !canSeePrices,
                ),
            );
        }
    }, [
        gtmPageReadyEvent._isLoaded,
        gtmPageReadyEvent.cart,
        gtmPageReadyEvent.currencyCode,
        didPageReadyRun,
        isScriptLoaded,
        canSeePrices,
    ]);
};
