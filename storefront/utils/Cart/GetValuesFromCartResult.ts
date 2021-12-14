import { CartFragmentApi } from 'graphql/generated';
import { CartType } from 'types/cart';
import { getSelectedPickupPlace } from 'connectors/transports/pickupPlace/PickupPlace';
import { mapCart } from 'connectors/cart/Cart';
import { mapPayment } from 'connectors/payments/Payment';
import { mapTransport } from 'connectors/transports/Transport';
import { PaymentType } from 'connectors/payments/types';
import { PickupPlaceType } from 'connectors/transports/pickupPlace/types';
import { TransportType } from 'connectors/transports/types';

export const getValuesFromCartResult = (
    resultData: CartFragmentApi,
    pickupPlaceIdentifier: string | null,
    promoCode: string | null,
    currencyCode: string,
): {
    cart: CartType | null;
    transport: TransportType | null;
    pickupPlace: PickupPlaceType | null;
    payment: PaymentType | null;
    promoCode: string | null;
} => {
    let cart: CartType | null = null;
    let transport: TransportType | null = null;
    let pickupPlace: PickupPlaceType | null = null;
    let payment: PaymentType | null = null;
    let updatedPromoCode: string | null = null;

    if (resultData !== null) {
        cart = mapCart(
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
        transport =
            resultData.transport === null || resultData.transport === undefined
                ? null
                : mapTransport(resultData.transport, currencyCode);
        pickupPlace = getSelectedPickupPlace(transport, pickupPlaceIdentifier);
        payment =
            resultData.payment === null || resultData.payment === undefined || transport === null
                ? null
                : mapPayment(resultData.payment, currencyCode);
        updatedPromoCode = promoCode;
    }

    return {
        cart,
        transport,
        pickupPlace,
        payment,
        promoCode: updatedPromoCode,
    };
};
