import * as Types from '../../types';

import gql from 'graphql-tag';
import { LastOrderFragmentApi } from '../fragments/LastOrderFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type LastOrderQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type LastOrderQueryApi = {
    __typename?: 'Query';
    lastOrder: {
        __typename: 'Order';
        pickupPlaceIdentifier: string | null;
        deliveryStreet: string | null;
        deliveryCity: string | null;
        deliveryPostcode: string | null;
        transport: { __typename: 'Transport'; uuid: string; name: string; description: string | null };
        payment: {
            __typename: 'Payment';
            uuid: string;
            name: string;
            description: string | null;
            instruction: string | null;
            type: string;
            price: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
            mainImage: {
                __typename: 'Image';
                name: string | null;
                sizes: Array<{
                    __typename: 'ImageSize';
                    size: string;
                    url: string;
                    width: number | null;
                    height: number | null;
                    additionalSizes: Array<{
                        __typename: 'AdditionalSize';
                        height: number | null;
                        media: string;
                        url: string;
                        width: number | null;
                    }>;
                }>;
            } | null;
            goPayPaymentMethod: {
                __typename: 'GoPayPaymentMethod';
                identifier: string;
                name: string;
                paymentGroup: string;
            } | null;
        };
        deliveryCountry: { __typename: 'Country'; name: string; code: string } | null;
    } | null;
};

export const LastOrderQueryDocumentApi = gql`
    query LastOrderQuery {
        lastOrder {
            ...LastOrderFragment
        }
    }
    ${LastOrderFragmentApi}
`;

export function useLastOrderQueryApi(options?: Omit<Urql.UseQueryArgs<LastOrderQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<LastOrderQueryApi, LastOrderQueryVariablesApi>({
        query: LastOrderQueryDocumentApi,
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
