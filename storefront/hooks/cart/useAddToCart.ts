import { showErrorMessage } from 'components/Helpers/Toasts';
import { mapCartItem, useCurrentCart } from 'connectors/cart/Cart';
import { AddToCartMutationApi, useAddToCartMutationApi } from 'graphql/generated';
import { onChangeCartItemGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCallback } from 'react';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { GtmListNameType, GtmMessageOriginType } from 'types/gtm';

export type AddToCartAction = (
    productUuid: string,
    quantity: number,
    gtmListName: GtmListNameType,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
) => Promise<AddToCartMutationApi['AddToCart'] | null>;

export const useAddToCart = (origin: GtmMessageOriginType): [AddToCartAction, boolean] => {
    const [{ fetching }, addToCart] = useAddToCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode, url } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const { cart } = useCurrentCart();

    const addToCartAction = useCallback<AddToCartAction>(
        async (productUuid, quantity, gtmListName, listIndex, isAbsoluteQuantity = false) => {
            const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
            const initialQuantity = itemToBeAdded?.quantity ?? 0;
            const addToCartActionResult = await addToCart({
                input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
            });
            if (cartUuid === null) {
                dispatch(userActions.setCartUuid(addToCartActionResult.data?.AddToCart.cart.uuid ?? null));
            }

            // EXTEND ADDING TO CART HERE

            if (addToCartActionResult.error !== undefined) {
                showErrorMessage(t('Unable to add product to cart'), origin);
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
                    origin,
                );
            }

            const quantityDifference = isAbsoluteQuantity
                ? addToCartResult.addProductResult.addedQuantity - initialQuantity
                : addToCartResult.addProductResult.addedQuantity;
            const absoluteEventValue =
                Number.parseFloat(addedCartItem.product.price.priceWithoutVat) * Math.abs(quantityDifference);
            const absoluteEventValueWithTax =
                Number.parseFloat(addedCartItem.product.price.priceWithVat) * Math.abs(quantityDifference);

            onChangeCartItemGtmEventHandler(
                mapCartItem(addedCartItem, currencyCode),
                currencyCode,
                absoluteEventValue,
                absoluteEventValueWithTax,
                quantityDifference,
                gtmListName,
                url,
                listIndex,
            );

            return addToCartResult;
        },
        [addToCart, cart?.items, cartUuid, currencyCode, dispatch, origin, t, url],
    );

    return [addToCartAction, fetching];
};
