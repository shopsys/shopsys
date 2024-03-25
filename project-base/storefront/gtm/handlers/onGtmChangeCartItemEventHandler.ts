import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { AddToCartMutation } from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeCartItemEvent } from 'gtm/factories/getGtmChangeCartItemEvent';
import { getGtmMappedCart } from 'gtm/helpers/getGtmMappedCart';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';
import { DomainConfigType } from 'helpers/domain/domainConfig';
import { mapPriceForCalculations } from 'helpers/mappers/price';

export const onGtmChangeCartItemEventHandler = (
    initialQuantity: number,
    isAbsoluteQuantity: boolean,
    addToCartResult: AddToCartMutation['AddToCart'],
    addedCartItem: CartItemFragment,
    domainConfig: DomainConfigType,
    listIndex: number | undefined,
    gtmProductListName: GtmProductListNameType,
    isUserLoggedIn: boolean,
): void => {
    const quantityDifference = isAbsoluteQuantity
        ? addToCartResult.addProductResult.addedQuantity - initialQuantity
        : addToCartResult.addProductResult.addedQuantity;
    const eventValueWithoutVat =
        mapPriceForCalculations(addedCartItem.product.price.priceWithoutVat) * Math.abs(quantityDifference);
    const eventValueWithVat =
        mapPriceForCalculations(addedCartItem.product.price.priceWithVat) * Math.abs(quantityDifference);

    const absoluteQuantity = Math.abs(quantityDifference);
    const event = getGtmChangeCartItemEvent(
        GtmEventType.add_to_cart,
        addedCartItem,
        listIndex,
        absoluteQuantity,
        domainConfig.currencyCode,
        eventValueWithoutVat,
        eventValueWithVat,
        gtmProductListName,
        domainConfig.url,
        getGtmMappedCart(
            addToCartResult.cart,
            addToCartResult.cart.promoCode,
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
