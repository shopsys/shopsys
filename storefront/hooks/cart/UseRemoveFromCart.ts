import { useRemoveFromCartMutationApi } from 'graphql/generated';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { CartItemType } from 'types/cart';
import { GtmListNameType } from 'types/gtm';
import { onRemoveCartItemGtmEventHandler } from 'utils/Gtm/EventHandlers';

export const useRemoveFromCart = (): typeof removeItemFromCartAction => {
    const [, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const dispatch = useShopsysDispatch();

    const removeItemFromCartAction = async (
        cartItem: CartItemType,
        listIndex: number,
        gtmListName: GtmListNameType,
    ) => {
        const removeItemFromCartActionResult = await removeItemFromCart({
            input: { cartUuid, cartItemUuid: cartItem.uuid },
        });

        if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
            dispatch(userActions.setCartUuid(removeItemFromCartActionResult.data.RemoveFromCart.uuid));
        }

        onRemoveCartItemGtmEventHandler(cartItem, listIndex, gtmListName);

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return removeItemFromCartAction;
};
