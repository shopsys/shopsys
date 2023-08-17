import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type CookiesArticleUrlQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type CookiesArticleUrlQueryApi = {
    __typename?: 'Query';
    cookiesArticle: { __typename?: 'ArticleSite'; slug: string } | null;
};

export const CookiesArticleUrlQueryDocumentApi = gql`
    query CookiesArticleUrlQuery {
        cookiesArticle {
            slug
        }
    }
`;

export function useCookiesArticleUrlQueryApi(
    options?: Omit<Urql.UseQueryArgs<CookiesArticleUrlQueryVariablesApi>, 'query'>,
) {
    return Urql.useQuery<CookiesArticleUrlQueryApi, CookiesArticleUrlQueryVariablesApi>({
        query: CookiesArticleUrlQueryDocumentApi,
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
