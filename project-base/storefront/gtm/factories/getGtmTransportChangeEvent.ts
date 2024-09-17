import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmShippingInfo } from 'gtm/mappers/mapGtmShippingInfo';
import { GtmTransportChangeEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const getGtmTransportChangeEvent = (
    gtmCartInfo: GtmCartInfoType,
    updatedTransport: TypeTransportWithAvailablePaymentsAndStoresFragment,
    updatedPickupPlace: StoreOrPacketeryPoint | null,
    paymentName: string | undefined,
    arePricesHidden: boolean,
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
            transportPriceWithoutVat: getGtmPriceBasedOnVisibility(updatedTransport.price.priceWithoutVat),
            transportPriceWithVat: getGtmPriceBasedOnVisibility(updatedTransport.price.priceWithVat),
            arePricesHidden,
        },
        _clear: true,
    };
};
