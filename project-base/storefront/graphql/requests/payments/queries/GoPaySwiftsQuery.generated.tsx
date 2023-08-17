import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type GoPaySwiftsQueryVariablesApi = Types.Exact<{
    currencyCode: Types.Scalars['String']['input'];
}>;

export type GoPaySwiftsQueryApi = {
    __typename?: 'Query';
    GoPaySwifts: Array<{ __typename?: 'GoPayBankSwift'; name: string; imageNormalUrl: string; swift: string }>;
};

export const GoPaySwiftsQueryDocumentApi = gql`
    query GoPaySwiftsQuery($currencyCode: String!) {
        GoPaySwifts(currencyCode: $currencyCode) {
            name
            imageNormalUrl
            swift
        }
    }
`;

export function useGoPaySwiftsQueryApi(options: Omit<Urql.UseQueryArgs<GoPaySwiftsQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<GoPaySwiftsQueryApi, GoPaySwiftsQueryVariablesApi>({
        query: GoPaySwiftsQueryDocumentApi,
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
