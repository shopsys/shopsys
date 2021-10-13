import { PriceApiType, PriceType, TransportApiType, TransportType } from './types';
import { mapTransport } from './Transport';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';
import { useShopsysSelector } from 'redux/main';

export const transportsQuery = `
        query transports ($cartUuid: Uuid) {
            transports (cartUuid: $cartUuid) {
                uuid
                name
                description
                instruction
                price {
                    priceWithVat
                    priceWithoutVat
                    vatAmount
                }
                images {
                    sizes {
                        url
                        height
                        width
                    }
                }
                payments {
                    uuid
                    name
                    description
                    instruction
                    price {
                        priceWithVat
                        priceWithoutVat
                        vatAmount
                    }
                    images {
                        sizes {
                            url
                            height
                            width
                        }
                    }
                }
                daysUntilDelivery
                stores {
                    edges {
                        node {
                            uuid
                            name
                            description
                            openingHours
                            street
                            postcode
                            city
                        }
                    }
                }
            }
        }
    ` as const;

export const mapPriceData = (price: PriceApiType, currencyCode: string): PriceType => {
    return {
        priceWithVat: Number.parseFloat(price.priceWithVat),
        priceWithoutVat: Number.parseFloat(price.priceWithoutVat),
        vatAmount: Number.parseFloat(price.vatAmount),
        currencyCode,
    };
};

const mapTransports = (apiData: TransportApiType[], currencyCode: string): TransportType[] => {
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
    const result = useFetchQuery({ query: transportsQuery, variables: { cartUuid } });
    const transportsApiData = result?.data?.transports;

    if (transportsApiData !== undefined) {
        return mapTransports(transportsApiData, currencyCode);
    }
    return [];
};
