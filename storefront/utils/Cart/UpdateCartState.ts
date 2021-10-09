import { CartInput, CartType } from 'connectors/cart/types';
import { StoreType, TransportType } from 'connectors/transports/types';
import { AppStore } from 'redux/main';
import { cookieActions } from 'redux/slices/cookie';
import { PaymentType } from 'connectors/payments/types';
import { userActions } from 'redux/slices/user';

export const updateCartState = (
    dispatch: AppStore['dispatch'],
    cart: CartType | null,
    transport: TransportType | null,
    personalPickupStore: StoreType | null,
    payment: PaymentType | null,
    updatedPromoCode: string | null,
    updatedUserCookieData: CartInput,
): void => {
    dispatch(userActions.setCart(cart));
    dispatch(userActions.setTransport(transport));
    dispatch(userActions.setPersonalPickupStore(personalPickupStore));
    dispatch(userActions.setPayment(payment));
    dispatch(cookieActions.setUserCookieData(updatedUserCookieData));
};
