import { TransportApiType, TransportType } from './types';
import { mapPriceData } from './Transports';

export const transportBody = `
        transport {
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
    ` as const;

export const mapTransport = (apiData: TransportApiType | null, currencyCode: string): TransportType | null => {
    if (apiData === null) {
        return null;
    }

    return {
        ...apiData,
        image: apiData.images.length === 0 ? null : apiData.images[0].sizes[0],
        price: mapPriceData(apiData.price, currencyCode),
        personalPickup: Array.isArray(apiData.stores?.edges) && apiData.stores.edges.length > 0,
        payments: apiData.payments.map((payment) => {
            return {
                ...payment,
                image: payment.images.length === 0 ? null : payment.images[0].sizes[0],
                price: mapPriceData(payment.price, currencyCode),
            };
        }),
        stores: Array.isArray(apiData.stores?.edges) ? apiData.stores.edges.map((edge) => edge.node) : [],
    };
};
