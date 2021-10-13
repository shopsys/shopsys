import { PaymentApiType, PaymentType } from './types';
import { mapPriceData } from 'connectors/transports/Transports';

export const paymentBody = `
        payment {
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
    ` as const;

export const mapPayment = (apiData: PaymentApiType, currencyCode: string): PaymentType => {
    return {
        ...apiData,
        image: apiData.images.length === 0 ? null : apiData.images[0].sizes[0],
        price: mapPriceData(apiData.price, currencyCode),
    };
};
