import { CartFragmentApi } from 'graphql/generated';
import { CartType } from 'types/cart';
import { getSelectedPickupPlace } from 'connectors/transports/pickupPlace/PickupPlace';
import { mapCart } from 'connectors/cart/Cart';
import { mapPayment } from 'connectors/payments/Payment';
import { mapTransport } from 'connectors/transports/Transport';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

export const getValuesFromCartResult = (
    resultData: CartFragmentApi,
    pickupPlaceIdentifier: string | null,
    currencyCode: string,
): {
    cart: CartType;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    payment: PaymentType | null;
    promoCode: string | null;
} => {
    const cart = mapCart(
        {
            uuid: resultData.uuid,
            items: resultData.items,
            modifications: resultData.modifications,
            totalPrice: resultData.totalPrice,
            totalDiscountPrice: resultData.totalDiscountPrice,
            remainingAmountWithVatForFreeTransport: resultData.remainingAmountWithVatForFreeTransport,
        },
        currencyCode,
    );
    const transport =
        resultData.transport === null || resultData.transport === undefined
            ? null
            : mapTransport(resultData.transport, currencyCode);
    const pickupPlace = getSelectedPickupPlace(transport, pickupPlaceIdentifier);
    const payment =
        resultData.payment === null || resultData.payment === undefined || transport === null
            ? null
            : mapPayment(resultData.payment, currencyCode);
    const updatedPromoCode = resultData.promoCode === undefined ? null : resultData.promoCode;

    return {
        cart,
        transport,
        pickupPlace,
        payment,
        promoCode: updatedPromoCode,
    };
};
