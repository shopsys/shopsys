import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmContactInformationPageViewEvent } from 'gtm/factories/getGtmContactInformationPageViewEvent';
import { GtmPageViewEventType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmContactInformationPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    const wasViewedRef = useRef(false);
    const { didPageViewRun, isScriptLoaded } = useGtmContext();
    const currentCustomerData = useCurrentCustomerData();

    useEffect(() => {
        if (
            isScriptLoaded &&
            didPageViewRun &&
            gtmPageViewEvent._isLoaded &&
            gtmPageViewEvent.cart &&
            !wasViewedRef.current
        ) {
            wasViewedRef.current = true;
            gtmSafePushEvent(
                getGtmContactInformationPageViewEvent(gtmPageViewEvent.cart, !!currentCustomerData?.arePricesHidden),
            );
        }
    }, [gtmPageViewEvent._isLoaded, gtmPageViewEvent.cart, didPageViewRun]);
};
