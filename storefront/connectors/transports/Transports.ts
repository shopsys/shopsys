import {
    PriceFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useTransportsQueryApi,
} from 'graphql/generated';
import { PriceType, TransportType } from 'types/transport';
import { mapTransport } from './Transport';
import { useShopsysSelector } from 'redux/main';

export const mapPriceData = (price: PriceFragmentApi, currencyCode: string): PriceType => {
    return {
        priceWithVat: Number.parseFloat(price.priceWithVat),
        priceWithoutVat: Number.parseFloat(price.priceWithoutVat),
        vatAmount: Number.parseFloat(price.vatAmount),
        currencyCode,
    };
};

const mapTransports = (
    apiData: TransportWithAvailablePaymentsAndStoresFragmentApi[],
    currencyCode: string,
): TransportType[] => {
    const mappedTransports: TransportType[] = [];
    for (const transport of apiData) {
        const mappedTransport = mapTransport(transport, currencyCode);
        if (mappedTransport !== null) {
            mappedTransports.push(mappedTransport);
        }
    }
    return mappedTransports;
};

export const getTransports = (cartUuid?: string | null): TransportType[] => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [result] = useTransportsQueryApi({ variables: { cartUuid } });
    const transportsApiData = result?.data?.transports;

    if (transportsApiData !== undefined) {
        return mapTransports(transportsApiData, currencyCode);
    }
    return [];
};
