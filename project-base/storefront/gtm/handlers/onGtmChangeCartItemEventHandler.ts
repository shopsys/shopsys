import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';
import { GtmCartInfoType } from 'gtm/types/objects';

export const onGtmChangeCartItemEventHandler = (
    addedCartItem: CartItemFragment,
    currencyCode: string,
    eventValueWithoutVat: number,
    eventValueWithVat: number,
    listIndex: number | undefined,
    quantityDifference: number,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    gtmCartInfo?: GtmCartInfoType | null,
): void => {
    const absoluteQuantity = Math.abs(quantityDifference);
    const event = getGtmChangeCartItemEvent(
        GtmEventType.add_to_cart,
        addedCartItem,
        listIndex,
        absoluteQuantity,
        currencyCode,
        eventValueWithoutVat,
        eventValueWithVat,
        gtmProductListName,
        domainUrl,
        gtmCartInfo,
    );

    if (quantityDifference < 0) {
        event.event = GtmEventType.remove_from_cart;
    }

    gtmSafePushEvent(event);
};
