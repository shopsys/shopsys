import { mapCart, mapPaymentToCartInput, mapTransportToCartInput } from 'connectors/cart/Cart';
import { AppStore } from 'redux/main';
import { CartApiType } from 'connectors/cart/types';
import { cookieActions } from 'redux/slices/cookie';
import { getSelectedPersonalPickupStore } from 'connectors/transports/PersonalPickupStore';
import { mapPayment } from 'connectors/payments/Payment';
import { mapTransport } from 'connectors/transports/Transport';
import { PaymentApiType } from 'connectors/payments/types';
import { TransportApiType } from 'connectors/transports/types';
import { updateUserDataCookie } from 'helpers/Cookies';
import { userActions } from 'redux/slices/user';

export const updateStateAndCookie = (
    resultData: CartApiType & {
        transport: TransportApiType | null;
        payment: PaymentApiType | null;
    },
    dispatch: AppStore['dispatch'],
    personalPickupStoreUuid: string | null,
    promoCode: string | null,
    currencyCode: string,
): void => {
    const cart = mapCart(
        {
            uuid: resultData.uuid,
            items: resultData.items,
            modifications: resultData.modifications,
            totalPrice: resultData.totalPrice,
            totalDiscountPrice: resultData.totalDiscountPrice,
        },
        currencyCode,
    );
    const transport = mapTransport(resultData.transport, currencyCode);
    const personalPickupStore = getSelectedPersonalPickupStore(transport, personalPickupStoreUuid);
    const payment = mapPayment(resultData.payment, currencyCode);

    dispatch(userActions.setCart(cart));
    dispatch(userActions.setTransport(transport));
    dispatch(userActions.setPersonalPickupStore(personalPickupStore));
    dispatch(userActions.setPayment(payment));

    const updatedUserCookieData = {
        cartUuid: cart?.uuid === undefined ? null : cart.uuid,
        transport: mapTransportToCartInput(transport, personalPickupStore),
        payment: mapPaymentToCartInput(payment),
        promoCode: promoCode,
    };
    dispatch(cookieActions.setUserCookieData(updatedUserCookieData));
    updateUserDataCookie(updatedUserCookieData);
};
