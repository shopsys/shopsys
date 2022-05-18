import { ImageType } from './image';
import { PriceType } from './price';
import { PageInfoType } from 'types/pageInfo';

export type ListedOrderType = {
    uuid: string;
    number: string;
    creationDate: string;
    items: {
        quantity: number;
    };
    transport: {
        name: string;
        image: ImageType | null;
    };
    payment: string;
    totalPrice: PriceType;
};

export type ListedOrderConnectionType = {
    orders: ListedOrderType[];
    totalCount: number;
    pageInfo: PageInfoType;
};

export type OrderDetailItemType = {
    name: string;
    unitPrice: PriceType;
    totalPrice: PriceType;
    vatRate: string;
    quantity: number;
    unit: string;
};

export type OrderDetailType = {
    uuid: string;
    number: string;
    creationDate: string;
    status: string;
    firstName: string;
    lastName: string;
    email: string;
    telephone: string;
    companyName: string;
    companyNumber: string;
    companyTaxNumber: string;
    street: string;
    city: string;
    postcode: string;
    country: string;
    differentDeliveryAddress: boolean;
    deliveryFirstName: string;
    deliveryLastName: string;
    deliveryCompanyName: string;
    deliveryTelephone: string;
    deliveryStreet: string;
    deliveryCity: string;
    deliveryPostcode: string;
    deliveryCountry: string;
    note: string;
    urlHash: string;
    promoCode: string;
    trackingNumber: string | null;
    trackingUrl: string | null;
    items: OrderDetailItemType[];
    transport: { name: string };
    payment: { name: string };
};
