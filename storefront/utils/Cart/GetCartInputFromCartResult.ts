import { CartInput, CartType } from 'types/cart';
import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

export const getCartInputFromCartResult = (resultData: {
    cartUuid: string | null;
    cart: CartType | null;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    payment: PaymentType | null;
    promoCode: string | null;
}): CartInput => {
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
