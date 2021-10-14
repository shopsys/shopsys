import { CartInput, CartType } from 'connectors/cart/types';
import { StoreType, TransportType } from 'connectors/transports/types';
import { AppStore } from 'redux/main';
import { cartInputActions } from 'redux/slices/cartInput';
import { PaymentType } from 'connectors/payments/types';
import { userActions } from 'redux/slices/user';

export const updateCartState = (
    dispatch: AppStore['dispatch'],
    resultData: {
        cart: CartType | null;
        transport: TransportType | null;
        personalPickupStore: StoreType | null;
        payment: PaymentType | null;
    },
    updatedCartInputData: CartInput,
): void => {
    dispatch(userActions.setCart(resultData.cart));
    dispatch(userActions.setTransport(resultData.transport));
    dispatch(userActions.setPersonalPickupStore(resultData.personalPickupStore));
    dispatch(userActions.setPayment(resultData.payment));
    dispatch(cartInputActions.setCartInputData(updatedCartInputData));
};
