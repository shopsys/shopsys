import { PaymentInputType } from 'connectors/payments/types';
import { TransportInputType } from 'connectors/transports/types';

export type OrderInputType = {
    firstName: string;
    lastName: string;
    email: string;
    telephone: string;
    onCompanyBehalf: boolean;
    companyName?: string | null;
    companyNumber?: string | null;
    companyTaxNumber?: string | null;
    street: string;
    city: string;
    postcode: string;
    country: string;
    differentDeliveryAddress: boolean;
    deliveryFirstName?: string | null;
    deliveryLastName?: string | null;
    deliveryCompanyName?: string | null;
    deliveryTelephone?: string | null;
    deliveryStreet?: string | null;
    deliveryCity?: string | null;
    deliveryPostcode?: string | null;
    deliveryCountry?: string | null;
    note?: string | null;
    payment: PaymentInputType;
    transport: TransportInputType;
    cartUuid?: string | null;
    promoCode?: string | null;
};

export type OrderApiType = {
    email: string;
};
