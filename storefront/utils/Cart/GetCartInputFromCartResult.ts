import { CartInput, CartType } from 'types/cart';
import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { PaymentType } from 'connectors/payments/types';
import { PickupPlaceType } from 'connectors/transports/pickupPlace/types';
import { TransportType } from 'connectors/transports/types';

export const getCartInputFromCartResult = (resultData: {
    cart: CartType | null;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    payment: PaymentType | null;
    promoCode: string | null;
}): CartInput => {
    return {
        cartUuid: resultData.cart?.uuid === undefined ? null : resultData.cart.uuid,
        isCartEmpty: resultData.cart?.items.length === 0,
        transport:
            resultData.transport === null
                ? null
                : mapTransportToTransportInput(resultData.transport, resultData.pickupPlace),
        payment: resultData.payment === null ? null : mapPaymentToPaymentInput(resultData.payment),
        promoCode: resultData.promoCode,
    };
};
