import { CartFragmentApi } from 'graphql/generated';
import { CartResultValues } from 'types/cart';
import { getSelectedPickupPlace } from 'connectors/transports/pickupPlace/PickupPlace';
import { mapCart } from 'connectors/cart/Cart';
import { mapPayment } from 'connectors/payments/Payment';
import { mapTransport } from 'connectors/transports/Transports';
import { PriceType } from 'types/price';

export const getValuesFromCartResult = (
    resultData: CartFragmentApi,
    currencyCode: string,
    isUserLoggedIn: boolean,
    goPayBankSwift: string | null,
): CartResultValues => {
    const emptyPriceArray: PriceType = {
        priceWithVat: 0,
        priceWithoutVat: 0,
        vatAmount: 0,
        currencyCode: currencyCode,
    };
    const cartUuid = isUserLoggedIn ? null : resultData.uuid;
    const transport = resultData.transport === null ? null : mapTransport(resultData.transport, currencyCode);
    const pickupPlace = getSelectedPickupPlace(transport, resultData.selectedPickupPlaceIdentifier);
    const payment =
        resultData.payment === null || transport === null
            ? null
            : mapPayment(resultData.payment, currencyCode, goPayBankSwift);
    const cart = mapCart(
        {
            ...resultData,
        },
        transport !== null ? transport.price : { ...emptyPriceArray },
        payment !== null ? payment.price : { ...emptyPriceArray },
        currencyCode,
    );
    const updatedPromoCode = resultData.promoCode;

    return {
        cartUuid,
        cart,
        transport,
        pickupPlace,
        goPayBankSwift,
        payment,
        promoCode: updatedPromoCode,
    };
};
