import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeAddToCartMutation } from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { mapGtmServiceCartItem } from 'gtm/mappers/mapGtmServiceCartItems';
import { getGtmMappedCart } from 'gtm/utils/getGtmMappedCart';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const onGtmChangeCartItemEventHandler = (
    initialQuantity: number,
    isAbsoluteQuantity: boolean,
    addToCartResult: TypeAddToCartMutation['AddToCart'],
    addedCartItem: TypeCartItemFragment,
    updatedCart: TypeCartFragment,
    domainConfig: DomainConfigType,
    listIndex: number | undefined,
    gtmProductListName: GtmProductListNameType,
    isUserLoggedIn: boolean,
    arePricesHidden: boolean,
    previousCartItems: TypeCartItemFragment[],
): void => {
    const quantityDifference = isAbsoluteQuantity
        ? addToCartResult.addProductResult.addedQuantity - initialQuantity
        : addToCartResult.addProductResult.addedQuantity;
    const absoluteQuantity = Math.abs(quantityDifference);

    const additionalServiceCartItems = addedCartItem.additionalServices.map((additionalService) =>
        mapGtmServiceCartItem(additionalService, [addedCartItem.product.id], absoluteQuantity),
    );
    const additionalServicesUnitPriceWithoutVat = additionalServiceCartItems.reduce(
        (unitPrice, additionalServiceCartItem) => unitPrice + (additionalServiceCartItem.priceWithoutVat ?? 0),
        0,
    );
    const additionalServicesUnitPriceWithVat = additionalServiceCartItems.reduce(
        (unitPrice, additionalServiceCartItem) => unitPrice + (additionalServiceCartItem.priceWithVat ?? 0),
        0,
    );

    const eventValueWithoutVat = getGtmPriceBasedOnVisibility(addedCartItem.product.price.priceWithoutVat);
    const eventValueWithVat = getGtmPriceBasedOnVisibility(addedCartItem.product.price.priceWithVat);
    const eventValueWithoutVatMultipliedByQuantity =
        eventValueWithoutVat === null
            ? eventValueWithoutVat
            : (eventValueWithoutVat + additionalServicesUnitPriceWithoutVat) * absoluteQuantity;
    const eventValueWithVatMultipliedByQuantity =
        eventValueWithVat === null
            ? eventValueWithVat
            : (eventValueWithVat + additionalServicesUnitPriceWithVat) * absoluteQuantity;

    const event = getGtmChangeCartItemEvent(
        GtmEventType.add_to_cart,
        addedCartItem,
        listIndex,
        absoluteQuantity,
        domainConfig.currencyCode,
        eventValueWithoutVatMultipliedByQuantity,
        eventValueWithVatMultipliedByQuantity,
        gtmProductListName,
        domainConfig.url,
        arePricesHidden,
        additionalServiceCartItems,
        getGtmMappedCart(updatedCart, updatedCart.promoCodes, isUserLoggedIn, domainConfig, updatedCart.uuid),
    );

    if (quantityDifference < 0) {
        event.event = GtmEventType.remove_from_cart;
    }

    const giftSourceItems = quantityDifference < 0 ? previousCartItems : updatedCart.items;
    const giftComparisonItems = quantityDifference < 0 ? updatedCart.items : previousCartItems;

    for (const giftCartItem of giftSourceItems) {
        if (giftCartItem.type !== TypeCartItemTypeEnum.ProductGift) {
            continue;
        }

        const previousGiftQuantity = giftComparisonItems.find((item) => item.uuid === giftCartItem.uuid)?.quantity ?? 0;
        const changedGiftQuantity = giftCartItem.quantity - previousGiftQuantity;

        if (changedGiftQuantity <= 0) {
            continue;
        }

        const mappedGift = mapGtmCartItemType(giftCartItem, domainConfig.url, listIndex, changedGiftQuantity);
        event.ecommerce.products = [...(event.ecommerce.products ?? []), mappedGift];
        event.ecommerce.valueWithoutVat =
            event.ecommerce.valueWithoutVat === null || mappedGift.priceWithoutVat === null
                ? null
                : event.ecommerce.valueWithoutVat + mappedGift.priceWithoutVat * changedGiftQuantity;
        event.ecommerce.valueWithVat =
            event.ecommerce.valueWithVat === null || mappedGift.priceWithVat === null
                ? null
                : event.ecommerce.valueWithVat + mappedGift.priceWithVat * changedGiftQuantity;
    }

    gtmSafePushEvent(event);
};
