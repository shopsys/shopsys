import * as Types from '../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragmentApi } from './OrderDetailItemFragment.generated';
import { PriceFragmentApi } from '../../prices/fragments/PriceFragment.generated';
export type OrderDetailFragmentApi = {
    __typename: 'Order';
    uuid: string;
    number: string;
    creationDate: any;
    status: string;
    firstName: string | null;
    lastName: string | null;
    email: string;
    telephone: string;
    companyName: string | null;
    companyNumber: string | null;
    companyTaxNumber: string | null;
    street: string;
    city: string;
    postcode: string;
    differentDeliveryAddress: boolean;
    deliveryFirstName: string | null;
    deliveryLastName: string | null;
    deliveryCompanyName: string | null;
    deliveryTelephone: string | null;
    deliveryStreet: string | null;
    deliveryCity: string | null;
    deliveryPostcode: string | null;
    note: string | null;
    urlHash: string;
    promoCode: string | null;
    trackingNumber: string | null;
    trackingUrl: string | null;
    items: Array<{
        __typename: 'OrderItem';
        name: string;
        vatRate: string;
        quantity: number;
        unit: string | null;
        unitPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
        totalPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
    }>;
    transport: { __typename: 'Transport'; name: string };
    payment: { __typename: 'Payment'; name: string };
    country: { __typename: 'Country'; name: string };
    deliveryCountry: { __typename: 'Country'; name: string } | null;
    totalPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
};

export const OrderDetailFragmentApi = gql`
    fragment OrderDetailFragment on Order {
        __typename
        uuid
        number
        creationDate
        items {
            ...OrderDetailItemFragment
        }
        transport {
            __typename
            name
        }
        payment {
            __typename
            name
        }
        status
        firstName
        lastName
        email
        telephone
        companyName
        companyNumber
        companyTaxNumber
        street
        city
        postcode
        country {
            __typename
            name
        }
        differentDeliveryAddress
        deliveryFirstName
        deliveryLastName
        deliveryCompanyName
        deliveryTelephone
        deliveryStreet
        deliveryCity
        deliveryPostcode
        deliveryCountry {
            __typename
            name
        }
        note
        urlHash
        promoCode
        trackingNumber
        trackingUrl
        totalPrice {
            ...PriceFragment
        }
    }
    ${OrderDetailItemFragmentApi}
    ${PriceFragmentApi}
`;

export interface PossibleTypesResultData {
    possibleTypes: {
        [key: string]: string[];
    };
}
const result: PossibleTypesResultData = {
    possibleTypes: {
        Advert: ['AdvertCode', 'AdvertImage'],
        ArticleInterface: ['ArticleSite', 'BlogArticle'],
        Breadcrumb: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
        CartInterface: ['Cart'],
        CustomerUser: ['CompanyCustomerUser', 'RegularCustomerUser'],
        NotBlogArticleInterface: ['ArticleLink', 'ArticleSite'],
        ParameterFilterOptionInterface: [
            'ParameterCheckboxFilterOption',
            'ParameterColorFilterOption',
            'ParameterSliderFilterOption',
        ],
        PriceInterface: ['Price', 'ProductPrice'],
        Product: ['MainVariant', 'RegularProduct', 'Variant'],
        ProductListable: ['Brand', 'Category', 'Flag'],
        Slug: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
    },
};
export default result;
