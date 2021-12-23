import { AppStore } from 'redux/main';
import { cartActions } from 'redux/slices/cart';
import { CartResultValues } from 'types/cart';

export const updateCartState = (dispatch: AppStore['dispatch'], resultData?: CartResultValues): void => {
    if (resultData === undefined) {
        dispatch(cartActions.resetCart());
        return;
    }
    dispatch(cartActions.updateCart(resultData));
};
