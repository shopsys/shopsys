import { TransportWithAvailablePaymentsAndStoresFragmentApi, useTransportsQueryApi } from 'graphql/generated';

export const useTransports = (
    cartUuid: string | null,
): TransportWithAvailablePaymentsAndStoresFragmentApi[] | undefined => {
    const [result] = useTransportsQueryApi({ variables: { cartUuid }, requestPolicy: 'cache-and-network' });

    return result.data?.transports;
};
