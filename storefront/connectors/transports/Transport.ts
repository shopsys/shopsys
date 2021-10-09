import { TransportApiType, TransportType } from './types';
import { mapPayment } from 'connectors/payments/Payment';
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

export const mapTransport = (apiData: TransportApiType, currencyCode: string): TransportType => {
    return {
        ...apiData,
        image: apiData.images.length === 0 ? null : apiData.images[0].sizes[0],
        price: mapPriceData(apiData.price, currencyCode),
        hasPersonalPickup: Array.isArray(apiData.stores?.edges) && apiData.stores.edges.length > 0,
        payments: apiData.payments.map((payment) => mapPayment(payment, currencyCode)),
        stores: Array.isArray(apiData.stores?.edges) ? apiData.stores.edges.map((edge) => edge.node) : [],
    };
};
