import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { mapPayment } from 'connectors/payments/Payment';
import { mapPriceData } from './Transports';
import { mapStoresPreviewApiData } from 'connectors/stores/Stores';
import { TransportType } from './types';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';

export const mapTransport = (
    apiData: TransportWithAvailablePaymentsAndStoresFragmentApi,
    currencyCode: string,
): TransportType => {
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
        hasPersonalPickup:
            apiData.stores !== undefined &&
            apiData.stores !== null &&
            Array.isArray(apiData.stores?.edges) &&
            apiData.stores.edges.length > 0,
        payments: apiData.payments.map((payment) => mapPayment(payment, currencyCode)),
        stores:
            apiData.stores !== undefined && apiData.stores !== null && Array.isArray(apiData.stores?.edges)
                ? mapStoresPreviewApiData(apiData.stores)
                : [],
    };
};
