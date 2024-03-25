import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmContactInformationPageViewEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';

export const getGtmContactInformationPageViewEvent = (
    gtmCartInfo: GtmCartInfoType,
): GtmContactInformationPageViewEventType => ({
    event: GtmEventType.contact_information_page_view,
    ecommerce: {
        currencyCode: gtmCartInfo.currencyCode,
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        promoCodes: gtmCartInfo.promoCodes,
        products: gtmCartInfo.products,
    },
    _clear: true,
});
