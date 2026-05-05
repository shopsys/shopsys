import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmContactInformationViewEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';

export const getGtmContactInformationViewEvent = (
    gtmCartInfo: GtmCartInfoType,
    arePricesHidden: boolean,
): GtmContactInformationViewEventType => ({
    event: GtmEventType.contact_information_view,
    ecommerce: {
        currencyCode: gtmCartInfo.currencyCode,
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        promoCodes: gtmCartInfo.promoCodes,
        products: gtmCartInfo.products,
        arePricesHidden,
    },
    _clear: true,
});
