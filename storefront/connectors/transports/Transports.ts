import { TransportWithAvailablePaymentsAndStoresFragmentApi, useTransportsQueryApi } from 'graphql/generated';
import { mapTransport } from './Transport';
import { TransportType } from 'types/transport';
import { useShopsysSelector } from 'redux/main';

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

export const getTransports = (cartUuid: string | null): TransportType[] => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [result] = useTransportsQueryApi({ variables: { cartUuid } });
    const transportsApiData = result.data?.transports;

    if (transportsApiData !== undefined) {
        return mapTransports(transportsApiData, currencyCode);
    }
    return [];
};
