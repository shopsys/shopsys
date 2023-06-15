import { showErrorMessage } from 'components/Helpers/toasts';
import { useCurrentCart } from 'connectors/cart/Cart';
import { AddToCartMutationApi, useAddToCartMutationApi } from 'graphql/generated';
import { onGtmChangeCartItemEventHandler } from 'helpers/gtm/eventHandlers';
import { getGtmMappedCart } from 'helpers/gtm/gtm';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useCallback } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';

export type AddToCartAction = (
    productUuid: string,
    quantity: number,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
) => Promise<AddToCartMutationApi['AddToCart'] | null>;

export const useAddToCart = (
    gtmMessageOrigin: GtmMessageOriginType,
    gtmProductListName: GtmProductListNameType,
): [AddToCartAction, boolean] => {
    const [{ fetching }, addToCart] = useAddToCartMutationApi();
    const t = useTypedTranslationFunction();
    const { isUserLoggedIn } = useCurrentUserData();
    const { cart } = useCurrentCart();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((s) => s.cartUuid);
    const updateUserState = usePersistStore((s) => s.updateUserState);

    const addToCartAction = useCallback<AddToCartAction>(
        async (productUuid, quantity, listIndex, isAbsoluteQuantity = false) => {
            const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
            const initialQuantity = itemToBeAdded?.quantity ?? 0;
            const addToCartActionResult = await addToCart({
                input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
            });
            if (cartUuid === null) {
                updateUserState({ cartUuid: addToCartActionResult.data?.AddToCart.cart.uuid ?? null });
            }

            // EXTEND ADDING TO CART HERE

            if (addToCartActionResult.error !== undefined) {
                showErrorMessage(t('Unable to add product to cart'), gtmMessageOrigin);
                return null;
            }

            const addToCartResult = addToCartActionResult.data?.AddToCart;

            if (addToCartResult === undefined) {
                return null;
            }

            const addedCartItem = addToCartResult.addProductResult.cartItem;
            const notOnStockQuantity = addToCartResult.addProductResult.notOnStockQuantity;

            if (notOnStockQuantity > 0) {
                showErrorMessage(
                    t(
                        'You have the maximum available amount in your cart, you cannot add more (total {{ quantity }} {{ unitName }})',
                        {
                            quantity: addedCartItem.quantity,
                            unitName: addedCartItem.product.unit.name,
                        },
                    ),
                    gtmMessageOrigin,
                );
            }

            const quantityDifference = isAbsoluteQuantity
                ? addToCartResult.addProductResult.addedQuantity - initialQuantity
                : addToCartResult.addProductResult.addedQuantity;
            const absoluteEventValueWithoutVat =
                mapPriceForCalculations(addedCartItem.product.price.priceWithoutVat) * Math.abs(quantityDifference);
            const absoluteEventValueWithVat =
                mapPriceForCalculations(addedCartItem.product.price.priceWithVat) * Math.abs(quantityDifference);

            onGtmChangeCartItemEventHandler(
                addedCartItem,
                domainConfig.currencyCode,
                absoluteEventValueWithoutVat,
                absoluteEventValueWithVat,
                listIndex,
                quantityDifference,
                gtmProductListName,
                domainConfig.url,
                getGtmMappedCart(
                    addToCartResult.cart,
                    addToCartResult.cart.promoCode,
                    isUserLoggedIn,
                    domainConfig,
                    cartUuid,
                ),
            );

            return addToCartResult;
        },
        [
            cart?.items,
            addToCart,
            cartUuid,
            domainConfig,
            gtmProductListName,
            isUserLoggedIn,
            updateUserState,
            t,
            gtmMessageOrigin,
        ],
    );

    return [addToCartAction, fetching];
};
