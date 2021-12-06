import { AppStore } from 'redux/main';
import { cartInputActions } from 'redux/slices/cartInput';
import { CartType } from 'types/cart';
import { getCartInputFromCartResult } from './GetCartInputFromCartResult';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';
import { userActions } from 'redux/slices/user';

export const updateCartState = (
    dispatch: AppStore['dispatch'],
    resultData?: {
        cart: CartType | null;
        transport: TransportType | null;
        pickupPlace: PickupPlaceType | null;
        payment: PaymentType | null;
        promoCode: string | null;
    },
): void => {
    if (resultData === undefined) {
        dispatch(userActions.setCart(null));
        dispatch(userActions.setTransport(null));
        dispatch(userActions.setPickupPlace(null));
        dispatch(userActions.setPayment(null));
        dispatch(
            cartInputActions.setCartInputData({
                cartUuid: null,
                isCartEmpty: true,
                payment: null,
                transport: null,
                promoCode: null,
            }),
        );
        return;
    }
    dispatch(userActions.setCart(resultData.cart));
    dispatch(userActions.setTransport(resultData.transport));
    dispatch(userActions.setPickupPlace(resultData.pickupPlace));
    dispatch(userActions.setPayment(resultData.payment));
    dispatch(cartInputActions.setCartInputData(getCartInputFromCartResult(resultData)));
};
