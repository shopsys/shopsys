import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type OrderSentPageContentVariablesApi = Types.Exact<{
    orderUuid: Types.Scalars['Uuid']['input'];
}>;

export type OrderSentPageContentApi = { __typename?: 'Query'; orderSentPageContent: string };

export const OrderSentPageContentDocumentApi = gql`
    query OrderSentPageContent($orderUuid: Uuid!) {
        orderSentPageContent(orderUuid: $orderUuid)
    }
`;

export function useOrderSentPageContentApi(
    options: Omit<Urql.UseQueryArgs<OrderSentPageContentVariablesApi>, 'query'>,
) {
    return Urql.useQuery<OrderSentPageContentApi, OrderSentPageContentVariablesApi>({
        query: OrderSentPageContentDocumentApi,
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
