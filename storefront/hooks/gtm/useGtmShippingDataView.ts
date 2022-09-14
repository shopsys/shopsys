import { getGtmShippingInfoEvent, getNewGtmEcommerceEvent } from 'helpers/gtm/eventFactories';
import { gtmSafePushEvent } from 'helpers/gtm/gtm';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'types/gtm';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

export const useGtmShippingDataView = (
    transport: TransportType | null,
    pickupPlace: PickupPlaceType | null,
    paymentName: string | undefined,
    gtmStaticPageViewEvent: GtmPageViewEventType,
): void => {
    const wasViewedRef = useRef(false);

    useEffect(() => {
        if (
            gtmStaticPageViewEvent._isLoaded &&
            gtmStaticPageViewEvent.cart &&
            transport !== null &&
            !wasViewedRef.current
        ) {
            wasViewedRef.current = true;
            const event = getNewGtmEcommerceEvent('ec.shipping_data', true);
            event.ecommerce = getGtmShippingInfoEvent(gtmStaticPageViewEvent.cart, transport, pickupPlace, paymentName);
            gtmSafePushEvent(event);
        }
    }, [gtmStaticPageViewEvent._isLoaded, gtmStaticPageViewEvent.cart, paymentName, pickupPlace, transport]);
};
