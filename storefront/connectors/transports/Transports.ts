import { mapPickupPlacesApiData } from 'connectors/transports/pickupPlace/PickupPlace';
import { TransportWithAvailablePaymentsAndStoresFragmentApi, useTransportsQueryApi } from 'graphql/generated';
import { useMemo } from 'react';
import { TransportType } from 'types/transport';

export const useTransports = (cartUuid: string | null): TransportType[] => {
    const [result] = useTransportsQueryApi({ variables: { cartUuid }, requestPolicy: 'cache-and-network' });
    const transportsApiData = result.data?.transports;

    return useMemo(() => {
        if (transportsApiData !== undefined) {
            return mapTransports(transportsApiData);
        }
        return [];
    }, [transportsApiData]);
};

export const mapTransport = (apiData: TransportWithAvailablePaymentsAndStoresFragmentApi): TransportType => {
    return {
        ...apiData,
        description: apiData.description !== null ? apiData.description : '',
        instruction: apiData.instruction !== null ? apiData.instruction : '',
        stores: apiData.stores !== null ? mapPickupPlacesApiData(apiData.stores) : [],
    };
};

const mapTransports = (apiData: TransportWithAvailablePaymentsAndStoresFragmentApi[]): TransportType[] => {
    const mappedTransports: TransportType[] = [];
    for (const transport of apiData) {
        const mappedTransport = mapTransport(transport);
        mappedTransports.push(mappedTransport);
    }

    return mappedTransports;
};
