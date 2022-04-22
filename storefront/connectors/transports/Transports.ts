import { TransportWithAvailablePaymentsAndStoresFragmentApi, useTransportsQueryApi } from 'graphql/generated';
import { getFirstImage } from 'connectors/image/Image';
import { mapPayment } from 'connectors/payments/Payment';
import { mapPickupPlacesApiData } from 'connectors/transports/pickupPlace/PickupPlace';
import { mapPriceData } from 'connectors/price/Prices';
import { TransportType } from 'types/transport';
import { useShopsysSelector } from 'redux/main';

export const useTransports = (cartUuid: string | null): TransportType[] => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [result] = useTransportsQueryApi({ variables: { cartUuid } });
    const transportsApiData = result.data?.transports;

    if (transportsApiData !== undefined) {
        return mapTransports(transportsApiData, currencyCode);
    }
    return [];
};

export const mapTransport = (
    apiData: TransportWithAvailablePaymentsAndStoresFragmentApi,
    currencyCode: string,
): TransportType => {
    return {
        ...apiData,
        description: apiData.description !== null ? apiData.description : '',
        instruction: apiData.instruction !== null ? apiData.instruction : '',
        image: getFirstImage(apiData.images),
        price: mapPriceData(apiData.price, currencyCode),
        isPersonalPickup:
            (apiData.stores?.edges !== undefined && apiData.stores.edges !== null && apiData.stores.edges.length > 0) ||
            apiData.transportType.code === 'packetery',
        payments: apiData.payments.map((payment) => mapPayment(payment, currencyCode, null)),
        stores: apiData.stores !== null ? mapPickupPlacesApiData(apiData.stores) : [],
    };
};

const mapTransports = (
    apiData: TransportWithAvailablePaymentsAndStoresFragmentApi[],
    currencyCode: string,
): TransportType[] => {
    const mappedTransports: TransportType[] = [];
    for (const transport of apiData) {
        const mappedTransport = mapTransport(transport, currencyCode);
        mappedTransports.push(mappedTransport);
    }

    return mappedTransports;
};
