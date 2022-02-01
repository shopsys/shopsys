import { CartInput, CartResultValues } from 'types/cart';
import { mapPaymentToPaymentInput } from 'connectors/payments/Payment';
import { mapTransportToTransportInput } from 'connectors/cart/Cart';

export const getCartInputFromCartResult = (resultData: CartResultValues): CartInput => {
    return {
        cartUuid: resultData.cartUuid,
        promoCode: resultData.promoCode,
        transport:
            resultData.transport === null
                ? null
                : mapTransportToTransportInput(resultData.transport, resultData.pickupPlace),
        payment: resultData.payment === null ? null : mapPaymentToPaymentInput(resultData.payment),
    };
};
