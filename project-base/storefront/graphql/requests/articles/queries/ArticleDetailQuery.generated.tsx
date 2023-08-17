import * as Types from '../../types';

import gql from 'graphql-tag';
import { ArticleDetailFragmentApi } from '../../articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type ArticleDetailQueryVariablesApi = Types.Exact<{
    urlSlug: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;

export type ArticleDetailQueryApi = {
    __typename?: 'Query';
    article:
        | { __typename?: 'ArticleLink' }
        | {
              __typename: 'ArticleSite';
              uuid: string;
              slug: string;
              placement: string;
              text: string | null;
              seoTitle: string | null;
              seoMetaDescription: string | null;
              createdAt: any;
              articleName: string;
              breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
          }
        | null;
};

export const ArticleDetailQueryDocumentApi = gql`
    query ArticleDetailQuery($urlSlug: String) {
        article(urlSlug: $urlSlug) {
            ...ArticleDetailFragment
        }
    }
    ${ArticleDetailFragmentApi}
`;

export function useArticleDetailQueryApi(options?: Omit<Urql.UseQueryArgs<ArticleDetailQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<ArticleDetailQueryApi, ArticleDetailQueryVariablesApi>({
        query: ArticleDetailQueryDocumentApi,
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
