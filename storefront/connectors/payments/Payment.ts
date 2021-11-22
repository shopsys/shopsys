import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { mapPriceData } from 'connectors/transports/Transports';
import { PaymentType } from './types';
import { SimplePaymentFragmentApi } from 'graphql/generated';

export const mapPayment = (apiData: SimplePaymentFragmentApi, currencyCode: string): PaymentType => {
    return {
        ...apiData,
        description: apiData.description !== undefined && apiData.description !== null ? apiData.description : '',
        instruction: apiData.instruction !== undefined && apiData.instruction !== null ? apiData.instruction : '',
        image:
            apiData.images.length > 0 &&
            0 in apiData.images &&
            apiData.images[0]?.sizes !== undefined &&
            0 in apiData.images[0].sizes
                ? mapImageSizeApiData(apiData.images[0].sizes[0])
                : null,
        price: mapPriceData(apiData.price, currencyCode),
    };
};
