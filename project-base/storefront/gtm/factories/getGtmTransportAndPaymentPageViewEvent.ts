import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPaymentAndTransportPageViewEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';

export const getGtmTransportAndPaymentPageViewEvent = (
    currencyCode: string,
    gtmCartInfo: GtmCartInfoType,
): GtmPaymentAndTransportPageViewEventType => ({
    event: GtmEventType.payment_and_transport_page_view,
    ecommerce: {
        currencyCode,
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        products: gtmCartInfo.products,
    },
    _clear: true,
});
