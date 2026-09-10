import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { mapGtmServiceCartItem } from 'gtm/mappers/mapGtmServiceCartItems';
import { GtmCartInfoType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmRemoveFromCartEventHandler = (
    removedCartItem: TypeCartItemFragment,
    currencyCode: string,
    listIndex: number | undefined,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    arePricesHidden: boolean,
    gtmCartInfo?: GtmCartInfoType | null,
): void => {
    const additionalServiceCartItems = removedCartItem.additionalServices.map((additionalService) =>
        mapGtmServiceCartItem(additionalService, [removedCartItem.product.id], removedCartItem.quantity),
    );
    const additionalServicesUnitPriceWithoutVat = additionalServiceCartItems.reduce(
        (unitPrice, additionalServiceCartItem) => unitPrice + (additionalServiceCartItem.priceWithoutVat ?? 0),
        0,
    );
    const additionalServicesUnitPriceWithVat = additionalServiceCartItems.reduce(
        (unitPrice, additionalServiceCartItem) => unitPrice + (additionalServiceCartItem.priceWithVat ?? 0),
        0,
    );

    const eventValueWithoutVat = getGtmPriceBasedOnVisibility(removedCartItem.product.price.priceWithoutVat);
    const eventValueWithVat = getGtmPriceBasedOnVisibility(removedCartItem.product.price.priceWithVat);
    const eventValueWithoutVatMultipliedByQuantity =
        eventValueWithoutVat === null
            ? eventValueWithoutVat
            : (eventValueWithoutVat + additionalServicesUnitPriceWithoutVat) * removedCartItem.quantity;
    const eventValueWithVatMultipliedByQuantity =
        eventValueWithVat === null
            ? eventValueWithVat
            : (eventValueWithVat + additionalServicesUnitPriceWithVat) * removedCartItem.quantity;

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
            additionalServiceCartItems,
            gtmCartInfo,
        ),
    );
};
