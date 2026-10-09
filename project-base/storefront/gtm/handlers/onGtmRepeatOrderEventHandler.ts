import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { mapGtmServiceCartItems } from 'gtm/mappers/mapGtmServiceCartItems';
import { GtmChangeCartItemEventType } from 'gtm/types/events';
import { GtmCartProductOrServiceType } from 'gtm/types/objects';
import { getGtmMappedCart } from 'gtm/utils/getGtmMappedCart';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const onGtmRepeatOrderEventHandler = (
    previousCartItems: TypeCartItemFragment[],
    updatedCart: TypeCartFragment,
    domainConfig: DomainConfigType,
    isUserLoggedIn: boolean,
    arePricesHidden: boolean,
): void => {
    const previousQuantities = new Map(previousCartItems.map((item) => [item.uuid, item.quantity]));
    const addedCartItems = updatedCart.items.flatMap((item) => {
        const addedQuantity = item.quantity - (previousQuantities.get(item.uuid) ?? 0);

        return addedQuantity > 0 ? [{ ...item, quantity: addedQuantity }] : [];
    });

    if (addedCartItems.length === 0) {
        return;
    }

    const products = [
        ...addedCartItems.map((item) => mapGtmCartItemType(item, domainConfig.url)),
        ...mapGtmServiceCartItems(addedCartItems),
    ];
    const event: GtmChangeCartItemEventType = {
        event: GtmEventType.add_to_cart,
        ecommerce: {
            listName: GtmProductListNameType.repeat_order,
            currencyCode: domainConfig.currencyCode,
            valueWithoutVat: getProductsValue(products, 'priceWithoutVat'),
            valueWithVat: getProductsValue(products, 'priceWithVat'),
            products,
            arePricesHidden,
        },
        cart: getGtmMappedCart(updatedCart, updatedCart.promoCodes, isUserLoggedIn, domainConfig, updatedCart.uuid),
        _clear: true,
    };

    gtmSafePushEvent(event);
};

const getProductsValue = (
    products: GtmCartProductOrServiceType[],
    priceKey: 'priceWithoutVat' | 'priceWithVat',
): number | null =>
    products.reduce<number | null>((total, product) => {
        const price = product[priceKey];

        return total === null || price === null ? null : total + price * product.quantity;
    }, 0);
