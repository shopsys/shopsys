import { getFirstImageSize } from 'connectors/image/Image';
import { mapPriceData } from 'connectors/transports/Transports';
import { PaymentType } from 'types/payment';
import { SimplePaymentFragmentApi } from 'graphql/generated';

export const mapPayment = (apiData: SimplePaymentFragmentApi, currencyCode: string): PaymentType => {
    return {
        ...apiData,
        description: apiData.description !== null ? apiData.description : '',
        instruction: apiData.instruction !== null ? apiData.instruction : '',
        image: getFirstImageSize(apiData.images),
        price: mapPriceData(apiData.price, currencyCode),
    };
};
