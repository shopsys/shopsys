import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeAddToCartMutation } from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
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

    gtmSafePushEvent(event);
};
