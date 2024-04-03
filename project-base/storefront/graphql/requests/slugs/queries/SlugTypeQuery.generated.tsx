import * as Types from '../../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeSlugTypeQueryVariables = Types.Exact<{
  slug: Types.Scalars['String']['input'];
}>;


export type TypeSlugTypeQuery = { __typename?: 'Query', slug: { __typename: 'ArticleSite' } | { __typename: 'BlogArticle' } | { __typename: 'BlogCategory' } | { __typename: 'Brand' } | { __typename: 'Category' } | { __typename: 'Flag' } | { __typename: 'MainVariant' } | { __typename: 'RegularProduct' } | { __typename: 'Store' } | { __typename: 'Variant' } | null };


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
    

export const SlugTypeQueryDocument = gql`
    query SlugTypeQuery($slug: String!) {
  slug(slug: $slug) {
    __typename
  }
}
    `;

export function useSlugTypeQuery(options: Omit<Urql.UseQueryArgs<TypeSlugTypeQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeSlugTypeQuery, TypeSlugTypeQueryVariables>({ query: SlugTypeQueryDocument, ...options });
};