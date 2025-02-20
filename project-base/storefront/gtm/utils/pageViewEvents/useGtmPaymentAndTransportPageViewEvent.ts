import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmTransportAndPaymentPageViewEvent } from 'gtm/factories/getGtmTransportAndPaymentPageViewEvent';
import { GtmPageViewEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmPaymentAndTransportPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);
    const { didPageViewRun, isScriptLoaded } = useGtmContext();
    const { canSeePrices } = useAuthorization();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageViewRun &&
            gtmPageViewEvent._isLoaded &&
            gtmPageViewEvent.cart !== null &&
            gtmPageViewEvent.cart !== undefined &&
            !wasViewedRef.current
        ) {
            wasViewedRef.current = true;
            gtmSafePushEvent(
                getGtmTransportAndPaymentPageViewEvent(
                    gtmPageViewEvent.cart.currencyCode,
                    gtmPageViewEvent.cart,
                    !canSeePrices,
                ),
            );
        }
    }, [gtmPageViewEvent._isLoaded, gtmPageViewEvent.cart, didPageViewRun]);
};
