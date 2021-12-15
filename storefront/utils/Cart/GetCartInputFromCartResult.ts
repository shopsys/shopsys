import { CartInput, CartType } from 'types/cart';
import { mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

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
