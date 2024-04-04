import * as Types from '../../../types';

import gql from 'graphql-tag';
import { AdvertsFragment } from '../fragments/AdvertsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAdvertsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeAdvertsQuery = { __typename?: 'Query', adverts: Array<{ __typename: 'AdvertCode', code: string, uuid: string, name: string, positionName: string, type: string, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> } | { __typename: 'AdvertImage', link: string | null, uuid: string, name: string, positionName: string, type: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, mainImageMobile: { __typename: 'Image', name: string | null, url: string } | null, categories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string }> }> };


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
    

export const AdvertsQueryDocument = gql`
    query AdvertsQuery {
  adverts {
    ...AdvertsFragment
  }
}
    ${AdvertsFragment}`;

export function useAdvertsQuery(options?: Omit<Urql.UseQueryArgs<TypeAdvertsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeAdvertsQuery, TypeAdvertsQueryVariables>({ query: AdvertsQueryDocument, ...options });
};