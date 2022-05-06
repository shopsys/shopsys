import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { userActions } from 'redux/slices/user';
import { useRemoveFromCartMutationApi } from 'graphql/generated';

export const useRemoveFromCart = (): typeof removeItemFromCartAction => {
    const [, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const dispatch = useShopsysDispatch();

    const removeItemFromCartAction = async (cartItemUuid: string) => {
        const removeItemFromCartActionResult = await removeItemFromCart({ input: { cartUuid, cartItemUuid } });

        if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
            dispatch(userActions.setCartUuid(removeItemFromCartActionResult.data.RemoveFromCart.uuid));
        }

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return removeItemFromCartAction;
};
