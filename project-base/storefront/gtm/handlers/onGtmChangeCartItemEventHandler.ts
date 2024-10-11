import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeAddToCartMutation } from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { getGtmMappedCart } from 'gtm/utils/getGtmMappedCart';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const onGtmChangeCartItemEventHandler = (
    initialQuantity: number,
    isAbsoluteQuantity: boolean,
    addToCartResult: TypeAddToCartMutation['AddToCart'],
    addedCartItem: TypeCartItemFragment,
    domainConfig: DomainConfigType,
    listIndex: number | undefined,
    gtmProductListName: GtmProductListNameType,
    isUserLoggedIn: boolean,
    arePricesHidden: boolean,
): void => {
    const quantityDifference = isAbsoluteQuantity
        ? addToCartResult.addProductResult.addedQuantity - initialQuantity
        : addToCartResult.addProductResult.addedQuantity;

    const eventValueWithoutVat = getGtmPriceBasedOnVisibility(addedCartItem.product.price.priceWithoutVat);
    const eventValueWithVat = getGtmPriceBasedOnVisibility(addedCartItem.product.price.priceWithVat);
    const eventValueWithoutVatMultipliedByQuantity =
        eventValueWithoutVat === null ? eventValueWithoutVat : eventValueWithoutVat * Math.abs(quantityDifference);
    const eventValueWithVatMultipliedByQuantity =
        eventValueWithVat === null ? eventValueWithVat : eventValueWithVat * Math.abs(quantityDifference);

    const absoluteQuantity = Math.abs(quantityDifference);
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
        getGtmMappedCart(
            addToCartResult.cart,
            addToCartResult.cart.promoCodes,
            isUserLoggedIn,
            domainConfig,
            addToCartResult.cart.uuid,
        ),
    );

    if (quantityDifference < 0) {
        event.event = GtmEventType.remove_from_cart;
    }

    gtmSafePushEvent(event);
};
