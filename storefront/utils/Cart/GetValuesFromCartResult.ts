import { CartApiType, CartType } from 'connectors/cart/types';
import { StoreType, TransportType } from 'connectors/transports/types';
import { getSelectedPersonalPickupStore } from 'connectors/transports/PersonalPickupStore';
import { mapCart } from 'connectors/cart/Cart';
import { mapPayment } from 'connectors/payments/Payment';
import { mapTransport } from 'connectors/transports/Transport';
import { PaymentType } from 'connectors/payments/types';

export const getValuesFromCartResult = (
    resultData: CartApiType,
    pickupPlaceIdentifier: string | null,
    promoCode: string | null,
    currencyCode: string,
): {
    cart: CartType | null;
    transport: TransportType | null;
    personalPickupStore: StoreType | null;
    payment: PaymentType | null;
    promoCode: string | null;
} => {
    let cart: CartType | null = null;
    let transport: TransportType | null = null;
    let personalPickupStore: StoreType | null = null;
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
            },
            currencyCode,
        );
        transport = resultData.transport === null ? null : mapTransport(resultData.transport, currencyCode);
        personalPickupStore = getSelectedPersonalPickupStore(transport, pickupPlaceIdentifier);
        payment =
            resultData.payment === null || transport === null ? null : mapPayment(resultData.payment, currencyCode);
        updatedPromoCode = promoCode;
    }

    return {
        cart,
        transport,
        personalPickupStore,
        payment,
        promoCode: updatedPromoCode,
    };
};
