import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { GtmChangeCartItemEventType } from 'gtm/types/events';
import { GtmCartInfoType, GtmServiceCartItemType } from 'gtm/types/objects';

export const getGtmChangeCartItemAdditionalServiceEvent = (
    event: GtmEventType.add_to_cart | GtmEventType.remove_from_cart,
    additionalServiceCartItem: GtmServiceCartItemType,
    gtmProductListName: GtmProductListNameType,
    currencyCode: string,
    eventValueWithoutVat: number | null,
    eventValueWithVat: number | null,
    arePricesHidden: boolean,
    gtmCartInfo: GtmCartInfoType,
): GtmChangeCartItemEventType => ({
    event,
    ecommerce: {
        listName: gtmProductListName,
        currencyCode,
        valueWithoutVat: eventValueWithoutVat,
        valueWithVat: eventValueWithVat,
        products: [additionalServiceCartItem],
        arePricesHidden,
    },
    cart: gtmCartInfo,
    _clear: true,
});
