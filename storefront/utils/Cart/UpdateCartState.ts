import { AppStore } from 'redux/main';
import { cartActions } from 'redux/slices/cart';
import { CartType } from 'types/cart';
import { getCartInputFromCartResult } from './GetCartInputFromCartResult';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

export const updateCartState = (
    dispatch: AppStore['dispatch'],
    resultData?: {
        cartUuid: string | null;
        cart: CartType | null;
        transport: TransportType | null;
        pickupPlace: PickupPlaceType | null;
        payment: PaymentType | null;
        promoCode: string | null;
    },
): void => {
    if (resultData === undefined) {
        dispatch(cartActions.setCart(null));
        dispatch(cartActions.setTransport(null));
        dispatch(cartActions.setPickupPlace(null));
        dispatch(cartActions.setPayment(null));
        dispatch(
            cartActions.setCartInputData({
                cartUuid: null,
                payment: null,
                transport: null,
                promoCode: null,
            }),
        );
        dispatch(cartActions.setIsCartEmpty(true));
        return;
    }
    dispatch(cartActions.setCart(resultData.cart));
    dispatch(cartActions.setTransport(resultData.transport));
    dispatch(cartActions.setPickupPlace(resultData.pickupPlace));
    dispatch(cartActions.setPayment(resultData.payment));
    dispatch(cartActions.setCartInputData(getCartInputFromCartResult(resultData)));
    dispatch(cartActions.setIsCartEmpty(resultData.cart?.items.length === 0));
};
