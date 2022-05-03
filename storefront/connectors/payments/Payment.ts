import { mapPriceData, mapPriceInputData } from 'connectors/price/Prices';
import { PaymentInputType, PaymentType } from 'types/payment';
import { getFirstImage } from 'connectors/image/Image';
import { SimplePaymentFragmentApi } from 'graphql/generated';

export const mapPayment = (
    apiData: SimplePaymentFragmentApi,
    currencyCode: string,
    goPayBankSwift: string | null,
): PaymentType => {
    return {
        ...apiData,
        description: apiData.description !== null ? apiData.description : '',
        instruction: apiData.instruction !== null ? apiData.instruction : '',
        image: getFirstImage(apiData.images),
        price: mapPriceData(apiData.price, currencyCode),
        goPayPaymentMethod: apiData.goPayPaymentMethod !== null ? apiData.goPayPaymentMethod : undefined,
        goPayBankSwift,
    };
};

export const mapPaymentToPaymentInput = (payment: PaymentType, goPayBankSwift: string | null): PaymentInputType => {
    return {
        uuid: payment.uuid,
        price: mapPriceInputData(payment.price),
        goPayBankSwift,
    };
};
