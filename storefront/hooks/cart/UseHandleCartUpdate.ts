import { AddToCartResultType, CartApiType } from 'connectors/cart/types';
import { Dispatch } from 'redux';
import { mapCart } from 'connectors/cart/Cart';
import { updateUserDataCookie } from 'helpers/Cookies';
import { userActions } from 'redux/store/UserStore';

export const useHandleCartUpdate = (
    cartApiData: AddToCartResultType | CartApiType,
    currencyCode: string,
    dispatch: Dispatch,
): void => {
    const newCart = mapCart(cartApiData, currencyCode);
    dispatch(userActions.setCart(newCart));
    updateUserDataCookie({ cartUuid: newCart.uuid });
};
