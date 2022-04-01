import { useRemoveFromCartMutationApi } from 'graphql/generated';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { CartItemType } from 'types/cart';
import { onRemoveCartItemGtmEvent } from 'utils/Gtm/EventHandlers';
import { userActions } from 'redux/slices/user';

export const useRemoveFromCart = (): typeof removeItemFromCartAction => {
    const [, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const dispatch = useShopsysDispatch();

    const removeItemFromCartAction = async (cartItemUuid: string, gtmListName: string) => {
        const removeItemFromCartActionResult = await removeItemFromCart({ input: { cartUuid, cartItemUuid } });

        if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
            dispatch(userActions.setCartUuid(removeItemFromCartActionResult.data.RemoveFromCart.uuid));
        }

        onRemoveCartItemGtmEvent({} as unknown as CartItemType, gtmListName);

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return removeItemFromCartAction;
};
