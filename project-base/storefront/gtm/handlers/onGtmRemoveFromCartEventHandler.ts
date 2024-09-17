import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { GtmCartInfoType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmRemoveFromCartEventHandler = (
    removedCartItem: TypeCartItemFragment,
    currencyCode: string,
    listIndex: number,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    arePricesHidden: boolean,
    gtmCartInfo?: GtmCartInfoType | null,
): void => {
    const eventValueWithoutVat = getGtmPriceBasedOnVisibility(removedCartItem.product.price.priceWithoutVat);
    const eventValueWithVat = getGtmPriceBasedOnVisibility(removedCartItem.product.price.priceWithVat);
    const eventValueWithoutVatMultipliedByQuantity =
        eventValueWithoutVat === null ? eventValueWithoutVat : eventValueWithoutVat * removedCartItem.quantity;
    const eventValueWithVatMultipliedByQuantity =
        eventValueWithVat === null ? eventValueWithVat : eventValueWithVat * removedCartItem.quantity;

    gtmSafePushEvent(
        getGtmChangeCartItemEvent(
            GtmEventType.remove_from_cart,
            removedCartItem,
            listIndex,
            removedCartItem.quantity,
            currencyCode,
            eventValueWithoutVatMultipliedByQuantity,
            eventValueWithVatMultipliedByQuantity,
            gtmProductListName,
            domainUrl,
            arePricesHidden,
            gtmCartInfo,
        ),
    );
};
