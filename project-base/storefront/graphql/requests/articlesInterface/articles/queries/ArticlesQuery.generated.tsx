import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleNotBlogArticleFragmentApi } from '../fragments/SimpleNotBlogArticleFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type ArticlesQueryVariablesApi = Types.Exact<{
    placement: Types.InputMaybe<Array<Types.ArticlePlacementTypeEnumApi> | Types.ArticlePlacementTypeEnumApi>;
    first: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;

export type ArticlesQueryApi = {
    __typename?: 'Query';
    articles: {
        __typename?: 'ArticleConnection';
        edges: Array<{
            __typename: 'ArticleEdge';
            node:
                | {
                      __typename: 'ArticleLink';
                      uuid: string;
                      name: string;
                      url: string;
                      placement: string;
                      external: boolean;
                  }
                | {
                      __typename: 'ArticleSite';
                      uuid: string;
                      name: string;
                      slug: string;
                      placement: string;
                      external: boolean;
                  }
                | null;
        } | null> | null;
    };
};

export const ArticlesQueryDocumentApi = gql`
    query ArticlesQuery($placement: [ArticlePlacementTypeEnum!], $first: Int) @_redisCache(ttl: 3600) {
        articles(placement: $placement, first: $first) {
            edges {
                __typename
                node {
                    ...SimpleNotBlogArticleFragment
                }
            }
        }
    }
    ${SimpleNotBlogArticleFragmentApi}
`;

export function useArticlesQueryApi(options?: Omit<Urql.UseQueryArgs<ArticlesQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<ArticlesQueryApi, ArticlesQueryVariablesApi>({ query: ArticlesQueryDocumentApi, ...options });
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
