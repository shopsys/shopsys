import { ListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmShippingInfo } from 'gtm/mappers/mapGtmShippingInfo';
import { GtmTransportChangeEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';

export const getGtmTransportChangeEvent = (
    gtmCartInfo: GtmCartInfoType,
    updatedTransport: TransportWithAvailablePaymentsAndStoresFragment,
    updatedPickupPlace: ListedStoreFragment | null,
    paymentName: string | undefined,
): GtmTransportChangeEventType => {
    const { transportDetail, transportExtra } = mapGtmShippingInfo(updatedPickupPlace);

    return {
        event: GtmEventType.transport_change,
        ecommerce: {
            valueWithoutVat: gtmCartInfo.valueWithoutVat,
            valueWithVat: gtmCartInfo.valueWithVat,
            products: gtmCartInfo.products ?? [],
            currencyCode: gtmCartInfo.currencyCode,
            promoCodes: gtmCartInfo.promoCodes,
            paymentType: paymentName,
            transportType: updatedTransport.name,
            transportDetail,
            transportExtra,
            transportPriceWithoutVat: parseFloat(updatedTransport.price.priceWithoutVat),
            transportPriceWithVat: parseFloat(updatedTransport.price.priceWithVat),
        },
        _clear: true,
    };
};
