import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { GtmChangeCartItemEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';

export const getGtmChangeCartItemEvent = (
    event: GtmEventType.add_to_cart | GtmEventType.remove_from_cart,
    cartItem: CartItemFragment,
    listIndex: number | undefined,
    quantity: number,
    currencyCodeCode: string,
    eventValueWithoutVat: number,
    eventValueWithVat: number,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    gtmCartInfo?: GtmCartInfoType | null,
): GtmChangeCartItemEventType => ({
    event,
    ecommerce: {
        listName: gtmProductListName,
        currencyCode: currencyCodeCode,
        valueWithoutVat: eventValueWithoutVat,
        valueWithVat: eventValueWithVat,
        products: [mapGtmCartItemType(cartItem, domainUrl, listIndex, quantity)],
    },
    cart: gtmCartInfo,
    _clear: true,
});
