import * as Types from '../../types';

import gql from 'graphql-tag';
import { OrderDetailFragmentApi } from '../fragments/OrderDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type OrderDetailQueryVariablesApi = Types.Exact<{
    orderNumber: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;

export type OrderDetailQueryApi = {
    __typename?: 'Query';
    order: {
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
    } | null;
};

export const OrderDetailQueryDocumentApi = gql`
    query OrderDetailQuery($orderNumber: String) {
        order(orderNumber: $orderNumber) {
            ...OrderDetailFragment
        }
    }
    ${OrderDetailFragmentApi}
`;

export function useOrderDetailQueryApi(options?: Omit<Urql.UseQueryArgs<OrderDetailQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<OrderDetailQueryApi, OrderDetailQueryVariablesApi>({
        query: OrderDetailQueryDocumentApi,
        ...options,
    });
}

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
