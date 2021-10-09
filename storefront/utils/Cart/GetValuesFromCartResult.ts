import { CartApiType, CartType } from 'connectors/cart/types';
import { PaymentApiType, PaymentType } from 'connectors/payments/types';
import { StoreType, TransportApiType, TransportType } from 'connectors/transports/types';
import { getSelectedPersonalPickupStore } from 'connectors/transports/PersonalPickupStore';
import { mapCart } from 'connectors/cart/Cart';
import { mapPayment } from 'connectors/payments/Payment';
import { mapTransport } from 'connectors/transports/Transport';

export const getValuesFromCartResult = (
    resultData: CartApiType & {
        transport: TransportApiType | null;
        payment: PaymentApiType | null;
    },
    personalPickupStoreUuid: string | null,
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
        personalPickupStore = getSelectedPersonalPickupStore(transport, personalPickupStoreUuid);
        payment = resultData.payment === null ? null : mapPayment(resultData.payment, currencyCode);
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
