import { PriceApiType, PriceType, TransportApiType, TransportType } from './types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';
import { useShopsysSelector } from 'redux/store';

export const transportsQuery = `
        query transports {
            transports {
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
    return apiData.map((transport) => {
        return {
            ...transport,
            image: transport.images.length === 0 ? null : transport.images[0].sizes[0],
            price: mapPriceData(transport.price, currencyCode),
            personalPickup: Array.isArray(transport.stores?.edges) && transport.stores.edges.length > 0,
            payments: transport.payments.map((payment) => {
                return {
                    ...payment,
                    image: payment.images.length === 0 ? null : payment.images[0].sizes[0],
                    price: mapPriceData(payment.price, currencyCode),
                };
            }),
            stores: Array.isArray(transport.stores?.edges) ? transport.stores.edges.map((edge) => edge.node) : [],
        };
    });
};

export const getTransports = (): TransportType[] => {
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const result = useFetchQuery({ query: transportsQuery });
    const transportsApiData = result?.data?.transports;

    if (transportsApiData !== undefined) {
        return mapTransports(transportsApiData, currencyCode);
    }
    return [];
};
