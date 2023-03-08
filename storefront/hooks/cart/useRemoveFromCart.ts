import { CartFragmentApi, CartItemFragmentApi, useRemoveFromCartMutationApi } from 'graphql/generated';
import { onRemoveCartItemGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { GtmListNameType } from 'types/gtm';

export type RemoveFromCartHandler = (
    cartItem: CartItemFragmentApi,
    listIndex: number,
    gtmListName: GtmListNameType,
) => Promise<CartFragmentApi | undefined | null>;

export const useRemoveFromCart = (): [RemoveFromCartHandler, boolean] => {
    const [{ fetching }, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode, url } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();

    const removeItemFromCartAction = async (
        cartItem: CartItemFragmentApi,
        listIndex: number,
        gtmListName: GtmListNameType,
    ) => {
        const removeItemFromCartActionResult = await removeItemFromCart({
            input: { cartUuid, cartItemUuid: cartItem.uuid },
        });

        if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
            dispatch(userActions.setCartUuid(removeItemFromCartActionResult.data.RemoveFromCart.uuid));

            const absoluteEventValue =
                mapPriceForCalculations(cartItem.product.price.priceWithoutVat) * cartItem.quantity;
            const absoluteEventValueWithTax =
                mapPriceForCalculations(cartItem.product.price.priceWithVat) * cartItem.quantity;

            onRemoveCartItemGtmEventHandler(
                cartItem,
                currencyCode,
                absoluteEventValue,
                absoluteEventValueWithTax,
                listIndex,
                gtmListName,
                url,
            );
        }

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return [removeItemFromCartAction, fetching];
};
