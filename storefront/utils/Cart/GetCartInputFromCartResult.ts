import { CartInput, CartType } from 'connectors/cart/types';
import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { StoreType, TransportType } from 'connectors/transports/types';
import { PaymentType } from 'connectors/payments/types';

export const getCartInputFromCartResult = (resultData: {
    cart: CartType | null;
    transport: TransportType | null;
    personalPickupStore: StoreType | null;
    payment: PaymentType | null;
    promoCode: string | null;
}): CartInput => {
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
