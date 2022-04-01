import { showErrorMessage } from 'components/Helpers/Toasts';
import { mapCartItem, useCurrentCart } from 'connectors/cart/Cart';
import { useAddToCartMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { GtmListNameType } from 'types/gtm';
import { onChangeCartItemGtmEvent } from 'utils/Gtm/EventHandlers';

export const useAddToCart = (): typeof addToCartAction => {
    const [, addToCart] = useAddToCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const t = useTypedTranslationFunction();
    const { cart } = useCurrentCart();

    const addToCartAction = async (
        productUuid: string,
        quantity: number,
        gtmListName: GtmListNameType,
        isAbsoluteQuantity = false,
    ) => {
        const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
        const initialQuantity = itemToBeAdded?.quantity ?? 0;
        const addToCartActionResult = await addToCart({
            input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
        });
        dispatch(userActions.setCartUuid(addToCartActionResult.data?.AddToCart.cart.uuid ?? null));

        // EXTEND ADDING TO CART HERE

        if (addToCartActionResult.error !== undefined) {
            showErrorMessage(t('Unable to add product to cart'));
            return null;
        }

        const addToCartResult = addToCartActionResult.data?.AddToCart;

        if (addToCartResult === undefined) {
            return null;
        }

        const cartItem = addToCartResult.addProductResult.cartItem;
        const notOnStockQuantity = addToCartResult.addProductResult.notOnStockQuantity;

        if (notOnStockQuantity > 0) {
            showErrorMessage(
                t(
                    'You have the maximum available amount in your cart, you cannot add more (total {{ quantity }} {{ unitName }})',
                    {
                        quantity: cartItem.quantity,
                        unitName: cartItem.product.unit.name,
                    },
                ),
            );
        }

        onChangeCartItemGtmEvent(
            mapCartItem(cartItem, currencyCode),
            addToCartResult.addProductResult.addedQuantity - initialQuantity,
            gtmListName,
        );

        return addToCartResult;
    };

    return addToCartAction;
};
