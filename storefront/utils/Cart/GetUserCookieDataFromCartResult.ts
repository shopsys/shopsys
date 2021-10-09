import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { StoreType, TransportType } from 'connectors/transports/types';
import { CartType } from 'connectors/cart/types';
import { PaymentType } from 'connectors/payments/types';
import { UserDataCookieType } from 'helpers/Cookies';

export const getUserCookieDataFromCartResult = (resultData: {
    cart: CartType | null;
    transport: TransportType | null;
    personalPickupStore: StoreType | null;
    payment: PaymentType | null;
    promoCode: string | null;
}): UserDataCookieType => {
    return {
        cartUuid: resultData.cart?.uuid === undefined ? null : resultData.cart.uuid,
        transport:
            resultData.transport === null
                ? null
                : mapTransportToTransportInput(resultData.transport, resultData.personalPickupStore),
        payment: resultData.payment === null ? null : mapPaymentToPaymentInput(resultData.payment),
        promoCode: resultData.promoCode,
    };
};
