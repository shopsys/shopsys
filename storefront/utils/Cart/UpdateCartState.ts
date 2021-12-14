import { CartInput, CartType } from 'types/cart';
import { AppStore } from 'redux/main';
import { cartInputActions } from 'redux/slices/cartInput';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'connectors/transports/pickupPlace/types';
import { TransportType } from 'connectors/transports/types';
import { userActions } from 'redux/slices/user';

export const updateCartState = (
    dispatch: AppStore['dispatch'],
    resultData: {
        cart: CartType | null;
        transport: TransportType | null;
        pickupPlace: PickupPlaceType | null;
        payment: PaymentType | null;
    },
    updatedCartInputData: CartInput,
): void => {
    dispatch(userActions.setCart(resultData.cart));
    dispatch(userActions.setTransport(resultData.transport));
    dispatch(userActions.setPickupPlace(resultData.pickupPlace));
    dispatch(userActions.setPayment(resultData.payment));
    dispatch(cartInputActions.setCartInputData(updatedCartInputData));
};
