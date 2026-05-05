import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmTransportAndPaymentViewEvent } from 'gtm/factories/getGtmTransportAndPaymentViewEvent';
import { GtmPageReadyEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmPaymentAndTransportViewEvent = (gtmPageReadyEvent: GtmPageReadyEventType): void => {
    const wasViewedRef = useRef(false);
    const { didPageReadyRun, isScriptLoaded } = useGtmContext();
    const { canSeePrices } = useAuthorization();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageReadyRun &&
            gtmPageReadyEvent._isLoaded &&
            gtmPageReadyEvent.cart !== null &&
            gtmPageReadyEvent.cart !== undefined &&
            !wasViewedRef.current
        ) {
            wasViewedRef.current = true;
            gtmSafePushEvent(
                getGtmTransportAndPaymentViewEvent(
                    gtmPageReadyEvent.cart.currencyCode,
                    gtmPageReadyEvent.cart,
                    !canSeePrices,
                ),
            );
        }
    }, [gtmPageReadyEvent._isLoaded, gtmPageReadyEvent.cart, didPageReadyRun, isScriptLoaded, canSeePrices]);
};
