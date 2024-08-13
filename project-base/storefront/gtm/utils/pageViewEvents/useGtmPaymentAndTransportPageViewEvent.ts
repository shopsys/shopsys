import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmTransportAndPaymentPageViewEvent } from 'gtm/factories/getGtmTransportAndPaymentPageViewEvent';
import { GtmPageViewEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmPaymentAndTransportPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);
    const { didPageViewRun, isScriptLoaded } = useGtmContext();
    const currentCustomerData = useCurrentCustomerData();

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
                    !!currentCustomerData?.arePricesHidden,
                ),
            );
        }
    }, [gtmPageViewEvent._isLoaded, gtmPageViewEvent.cart, didPageViewRun]);
};
