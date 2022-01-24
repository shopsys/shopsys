import { getFirstImageSize } from 'connectors/image/Image';
import { mapPayment } from 'connectors/payments/Payment';
import { mapPickupPlacesApiData } from './pickupPlace/PickupPlace';
import { mapPriceData } from 'connectors/price/Prices';
import { TransportType } from 'types/transport';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';

export const mapTransport = (
    apiData: TransportWithAvailablePaymentsAndStoresFragmentApi,
    currencyCode: string,
): TransportType => {
    return {
        ...apiData,
        description: apiData.description !== null ? apiData.description : '',
        instruction: apiData.instruction !== null ? apiData.instruction : '',
        image: getFirstImageSize(apiData.images),
        price: mapPriceData(apiData.price, currencyCode),
        isPersonalPickup:
            (apiData.stores?.edges !== undefined && apiData.stores.edges !== null && apiData.stores.edges.length > 0) ||
            apiData.transportType.code === 'packetery',
        payments: apiData.payments.map((payment) => mapPayment(payment, currencyCode)),
        stores:
            apiData.stores !== null && Array.isArray(apiData.stores.edges)
                ? mapPickupPlacesApiData(apiData.stores)
                : [],
    };
};
