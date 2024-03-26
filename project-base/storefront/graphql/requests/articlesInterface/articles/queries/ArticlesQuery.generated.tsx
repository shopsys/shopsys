import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { SimpleNotBlogArticleFragment } from '../fragments/SimpleNotBlogArticleFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type ArticlesQueryVariables = Types.Exact<{
  placement: Types.InputMaybe<Array<Types.ArticlePlacementTypeEnum> | Types.ArticlePlacementTypeEnum>;
  first: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type ArticlesQuery = { __typename?: 'Query', articles: { __typename?: 'ArticleConnection', edges: Array<{ __typename: 'ArticleEdge', node: { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean } | { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean } | null } | null> | null } };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CartInterface": [
      "Cart"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "PriceInterface": [
      "Price",
      "ProductPrice"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    

export const ArticlesQueryDocument = gql`
    query ArticlesQuery($placement: [ArticlePlacementTypeEnum!], $first: Int) @redisCache(ttl: 3600) {
  articles(placement: $placement, first: $first) {
    edges {
      __typename
      node {
        ...SimpleNotBlogArticleFragment
      }
    }
  }
}
    ${SimpleNotBlogArticleFragment}`;

export function useArticlesQuery(options?: Omit<Urql.UseQueryArgs<ArticlesQueryVariables>, 'query'>) {
  return Urql.useQuery<ArticlesQuery, ArticlesQueryVariables>({ query: ArticlesQueryDocument, ...options });
};