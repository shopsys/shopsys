import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPaymentAndTransportViewEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';

export const getGtmTransportAndPaymentViewEvent = (
    currencyCode: string,
    gtmCartInfo: GtmCartInfoType,
    arePricesHidden: boolean,
): GtmPaymentAndTransportViewEventType => ({
    event: GtmEventType.payment_and_transport_view,
    ecommerce: {
        currencyCode,
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        products: gtmCartInfo.products,
        arePricesHidden,
    },
    _clear: true,
});
